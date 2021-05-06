<?php

namespace App\Service;

use App\Entity\AttributedEntityInterface;
use App\Entity\CoreEntity;
use App\Entity\EAV\Attribute;
use App\Entity\EAV\AttributeDefinition;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class EavAttributeProcessor
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function processAttributeChanges(AttributedEntityInterface $entity, &$changes)
    {
        if (!method_exists($entity, 'addAttribute') || !method_exists($entity, 'removeAttribute')) {
            throw new \Exception('Trying to process attribute changes on an entity that does not support attributes');
        }

        if (!isset($changes['attributes'])) {
            unset($changes['attributes']);
            return;
        }

        // Loop through the submitted attributes
        foreach ($changes['attributes'] as $attributeChange) {
            if (isset($attributeChange['id']) && $attributeChange['id'] > 0) {
                $attribute = $this->getAttributeById($attributeChange['id']);
                $definition = $attribute->getDefinition();
            } else {
                $definition = $this->getDefinitionById($attributeChange['definition_id']);
                $attribute = new Attribute($definition);
                $entity->addAttribute($attribute);
            }

            // If this is a not a multivalued attribute, wrap it in an array for processing
            $rawValues = ($definition->isMultivalued()) ? $attributeChange['value'] : [$attributeChange['value']];

            $values = new ArrayCollection();

            // Loop through the values of the changed attribute and update the entities
            foreach ($rawValues as $rawValue) {
                if ($attribute->hasRelationshipValue() && $attribute->hasOptions()) {
                    $relationId = null;

                    if (is_numeric($rawValue)) {
                        $relationId = $rawValue;
                    } elseif (isset($rawValue['id'])) {
                        $relationId = $rawValue['id'];
                    } else {
                        continue;
                    }

                    /** @var CoreEntity|null $relatedEntity */
                    $relatedEntity = $this->em->getReference(
                        $attribute->getValueType(),
                        $relationId
                    );

                    if (!$relatedEntity) {
                        continue;
                    }

                    $values->add($relatedEntity);
                } else {
                    $values->add($rawValue);
                }
            }

            $attribute->setValues($values);

            // After processing the changed values, if the attribute is empty, remove it from the entity
            if ($attribute->isEmpty()) {
                $entity->removeAttribute($attribute);
            }
        }

        unset($changes['attributes']);
    }

    private function getAttributeById(int $id): Attribute
    {
        $attribute = $this->em->getRepository(Attribute::class)->find($id);
        if (!$attribute) {
            throw new \Exception(sprintf("Unknown attribute id: %d", $id));
        }
        return $attribute;
    }

    private function getDefinitionById(int $id): AttributeDefinition
    {
        $definition = $this->em->getRepository(AttributeDefinition::class)->find($id);
        if (!$definition) {
            throw new \Exception(sprintf("Unknown defintion id: %d", $id));
        }
        return $definition;
    }
}
