<?php

namespace App\Entity;

use App\Entity\EAV\Attribute;
use Doctrine\Common\Collections\Collection;

interface AttributedEntityInterface
{
    public function addAttribute(Attribute $attribute, bool $overwrite = true): void;

    public function addAttributes(iterable $attributes, bool $overwrite = true): void;

    /**
     * @param int $attributeId
     * @param mixed $value
     */
    public function setAttribute(int $attributeId, $value): void;

    public function removeAttribute(Attribute $attribute): void;

    public function getAttributes(): Collection;

    public function processAttributeChanges(array $changes): void;
}
