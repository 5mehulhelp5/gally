<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Elasticsuite
 * @package   Elasticsuite\Example
 * @author    Botis <botis@smile.fr>
 * @copyright 2022 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

declare(strict_types=1);

namespace Elasticsuite\Example\Serializer;

use Elasticsuite\Example\Model\ExampleProduct;
use Elasticsuite\Example\Model\TextAttribute;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class AttributesDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'AttributesDenormalizerCalled';

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        $alreadyCalled = $context[self::ALREADY_CALLED_DENORMALIZER] ?? false;

        return ExampleProduct::class === $type && !$alreadyCalled;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;

        /** @var ExampleProduct $esProduct */
        $esProduct = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (isset($data['_source'])) {
            foreach ($data['_source'] as $attributeCode => $value) {
                if (!\in_array($attributeCode, ExampleProduct::DEFAULT_ATTRIBUTE, true) && !\is_array($value)) {
                    $esProduct->addAttribute(
                        new TextAttribute($attributeCode, $value)
                    );
                }
            }
        }

        return $esProduct;
    }
}
