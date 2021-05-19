<?php

namespace App\Entity\EAV;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait AttributedEntityTrait
{
    /**
     * @var Attribute[]|Collection
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\EAV\Attribute",
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

    public function setAttribute(int $definitionId, $value): void
    {
        /** @var Attribute|null $attribute */
        $attribute = $this->attributes->filter(function (Attribute $item) use ($definitionId) {
            return $item->getDefinition()->getId() == $definitionId;
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
                    $attribute = new Attribute($definition);
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

    /**
     * @throws \Exception
     */
    private function getDefinitionById(int $id): AttributeDefinition
    {
        /** @var AttributeDefinition[] $definitions */
        $definitions = $this->getAttributes()->map(function (AttributeValue $attribute) {
            return $attribute->getDefinition();
        });

        foreach ($definitions as $definition) {
            if ($definition->getId() === $id) {
                return $definition;
            }
        }

        throw new \Exception(sprintf('Unknown definition id: %d', $id));
    }

    public function hasAttributeForDefinition(AttributeDefinition $definition): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getDefinition()->getId() === $definition->getId()) {
                return true;
            }
        }

        return false;
    }
}
