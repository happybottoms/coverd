<?php

namespace App\Entity\EAV;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait AttributedEntityTrait
{
    /**
     * @var Collection
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\EAV\Attribute",
     *     fetch="EAGER",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    public function addAttribute(Attribute $attribute, bool $overwrite = true): void
    {
        foreach ($this->attributes as $item) {
            if ($item->getDefinition()->getId() === $attribute->getDefinition()->getId()) {
                if ($overwrite) {
                    $this->attributes->removeElement($item);
                }
            }
        }

        $this->attributes->add($attribute);
    }

    public function addAttributes(iterable $attributes, bool $overwrite = true): void
    {
        foreach ($attributes as $attribute) {
            $this->addAttribute($attribute, $overwrite);
        }
    }

    public function setAttribute(int $attributeId, $value): void
    {
        /** @var Attribute|null $attribute */
        $attribute = $this->attributes->filter(function (Attribute $item) use ($attributeId) {
            return $item->getDefinition()->getId() == $attributeId;
        })->first();

        if ($attribute) {
            $attribute->setValue($value);
        } else {
            // TODO
        }
    }

    public function removeAttribute(Attribute $attribute): void
    {
        $this->attributes->removeElement($attribute);
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function processAttributeChanges(array $changes): void
    {
        if (isset($changes['attributes'])) {
            foreach ($changes['attributes'] as $attributeChange) {
                if ($attributeChange['id'] > 0) {
                    $attribute = $this->getAttributeById($attributeChange['id']);
                    $attribute->setValue($attributeChange['value']);
                } else {
                    $definition = $this->getDefinitionById($attributeChange['definition_id']);
                    $attribute = $definition->createAttribute();
                    $attribute->setValue($attributeChange['value']);
                    $this->addAttribute($attribute);
                }
                if ($attribute->isEmpty()) {
                    $this->removeAttribute($attribute);
                }
            }
        }

        unset($changes['attributes']);
    }

    private function getAttributeById(int $id): ?Attribute
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getId() === $id) {
                return $attribute;
            }
        }

        return null;
    }

    private function getDefinitionById(int $id): ?Definition
    {
        /** @var Definition[] $definitions */
        $definitions = $this->getAttributes()->map(function (Attribute $attribute) {
            return $attribute->getDefinition();
        });

        foreach ($definitions as $definition) {
            if ($definition->getId() === $id) {
                return $definition;
            }
        }

        return null;
    }
}
