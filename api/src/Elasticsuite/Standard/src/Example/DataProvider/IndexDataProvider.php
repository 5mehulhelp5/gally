<?php

namespace Elasticsuite\Example\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Elasticsuite\Example\Model\ExampleIndex;
use Elasticsuite\Example\Repository\Index\IndexRepositoryInterface;

class IndexDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface , ItemDataProviderInterface
{

    public function __construct(
        private IndexRepositoryInterface $indexRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === ExampleIndex::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        return $this->indexRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?object
    {
        return $this->indexRepository->findByName($id);
    }


}
