<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @package   Elasticsuite
 * @author    ElasticSuite Team <elasticsuite@smile.fr>
 * @copyright 2022 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

declare(strict_types=1);

namespace Elasticsuite\Product\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\Pagination;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Elasticsuite\Catalog\Repository\LocalizedCatalogRepository;
use Elasticsuite\Metadata\Repository\MetadataRepository;
use Elasticsuite\Product\Model\Product;
use Elasticsuite\ResourceMetadata\Service\ResourceMetadataManager;
use Elasticsuite\Search\DataProvider\Paginator;
use Elasticsuite\Search\Elasticsearch\Adapter;
use Elasticsuite\Search\Elasticsearch\Builder\Request\SimpleRequestBuilder as RequestBuilder;
use Elasticsuite\Search\Elasticsearch\Request\SortOrderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private Pagination $pagination,
        private ResourceMetadataFactoryInterface $resourceMetadataFactory,
        private ResourceMetadataManager $resourceMetadataManager,
        private MetadataRepository $metadataRepository,
        private LocalizedCatalogRepository $catalogRepository,
        private RequestBuilder $requestBuilder,
        private Adapter $adapter,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Product::class === $resourceClass;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ResourceClassNotFoundException
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        $entityType = $this->resourceMetadataManager->getMetadataEntity($resourceMetadata);
        if (null === $entityType) {
            throw new ResourceClassNotFoundException(sprintf('Resource "%s" has no declared metadata entity.', $resourceClass));
        }

        // TODO Supposed to be pulled from header.
        $catalogId = $context['filters']['catalogId'];
        $metadata = $this->metadataRepository->findOneBy(['entity' => $entityType]);
        if (!$metadata) {
            throw new InvalidArgumentException(sprintf('Entity type [%s] does not exist', $entityType));
        }
        if (null === $metadata->getEntity()) {
            throw new InvalidArgumentException(sprintf('Entity type [%s] is not defined', $entityType));
        }
        if (is_numeric($catalogId)) {
            $catalog = $this->catalogRepository->find($catalogId);
        } else {
            $catalog = $this->catalogRepository->findOneBy(['code' => $catalogId]);
        }
        if (null === $catalog) {
            throw new InvalidArgumentException(sprintf('Missing catalog [%s]', $catalogId));
        }

        $sortOrders = [];
        if (\array_key_exists('sort', $context['filters'])) {
            $field = $context['filters']['sort']['field'];
            $direction = $context['filters']['sort']['direction'] ?? SortOrderInterface::DEFAULT_SORT_DIRECTION;
            $sortOrders = [$field => ['direction' => $direction]];
        }

        $limit = $this->pagination->getLimit($resourceClass, $operationName, $context);
        $offset = $this->pagination->getOffset($resourceClass, $operationName, $context);

        $request = $this->requestBuilder->create(
            $metadata,
            $catalog,
            $offset,
            $limit,
            null,
            $sortOrders
        );
        $response = $this->adapter->search($request);

        return new Paginator(
            $this->denormalizer,
            $response,
            $resourceClass,
            $limit,
            $offset,
            $context
        );
    }
}
