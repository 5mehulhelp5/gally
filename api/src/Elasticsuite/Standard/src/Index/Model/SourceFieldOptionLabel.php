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

namespace Elasticsuite\Index\Model;

use ApiPlatform\Core\Annotation\ApiResource;
use Elasticsuite\Catalog\Model\LocalizedCatalog;

#[ApiResource]
class SourceFieldOptionLabel
{
    private int $id;
    private SourceFieldOption $sourceFieldOption;
    private LocalizedCatalog $catalog;
    private string $label;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSourceFieldOption(): ?SourceFieldOption
    {
        return $this->sourceFieldOption;
    }

    public function setSourceFieldOption(?SourceFieldOption $sourceFieldOption): self
    {
        $this->sourceFieldOption = $sourceFieldOption;

        return $this;
    }

    public function getCatalog(): ?LocalizedCatalog
    {
        return $this->catalog;
    }

    public function setCatalog(?LocalizedCatalog $catalog): self
    {
        $this->catalog = $catalog;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
