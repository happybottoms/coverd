<?php

namespace App\Service;

use App\Entity\AttributedEntityInterface;
use App\Entity\EAV\Attribute;
use App\Entity\EAV\Definition;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;

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

        if (isset($changes['attributes'])) {
            foreach ($changes['attributes'] as $attributeChange) {
                if ($attributeChange['id'] > 0) {
                    $attribute = $this->getAttributeById($attributeChange['id']);
                    if (!$attribute) {
                        continue;
                    }
                } else {
                    $definition = $this->getDefinitionById($attributeChange['definition_id']);
                    $attribute = $definition->createAttribute();
                    $entity->addAttribute($attribute);
                }

                if ($this->isPropertyParentRelationship($attribute, 'value')) {
                    if (is_array($attributeChange['value']) && key_exists('id', $attributeChange['value'])) {
                        $valueRef = $this->em->getReference(
                            $attribute->getValueType(),
                            $attributeChange['value']['id']
                        );
                    } else {
                        $valueRef = $this->em->getReference($attribute->getValueType(), $attributeChange['value']);
                    }
                    $attribute->setValue($valueRef);
                } else {
                    $attribute->setValue($attributeChange['value']);
                }

                if ($attribute->isEmpty()) {
                    $entity->removeAttribute($attribute);
                }
            }
        }

        unset($changes['attributes']);
    }

    private function getAttributeById(int $id): ?Attribute
    {
        return $this->em->getRepository(Attribute::class)->find($id);
    }

    private function getDefinitionById(int $id): ?Definition
    {
        return $this->em->getRepository(Definition::class)->find($id);
    }


    private function getPropertyAnnotations(Attribute $attribute, string $property)
    {
        $reader = new AnnotationReader();
        $reflClass = new \ReflectionClass(get_class($attribute));

        if (!$reflClass->hasProperty($property)) {
            return false;
        }

        return $reader->getPropertyAnnotations($reflClass->getProperty($property));
    }

    private function isPropertyParentRelationship(Attribute $attribute, string $property)
    {
        $annotations = $this->getPropertyAnnotations($attribute, $property);
        if (!$annotations) {
            return false;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ManyToMany || $annotation instanceof ManyToOne) {
                return true;
            }
        }

        return false;
    }
}
