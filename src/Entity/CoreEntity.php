<?php

namespace App\Entity;

use App\Entity\Traits\PubliclyExposedEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class CoreEntity
 *
 * Holds standard code for all models in the app.
 *
 * @package App
 */
abstract class CoreEntity
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use PubliclyExposedEntity;

    /**
     * Every entity will have an id
     *
     * @return int
     */
    abstract public function getId();

    /**
     * Take an associative array of values and overlay them on an existing entity.
     */
    public function applyChangesFromArray(array $changes): void
    {
        // remove id, just in case
        unset($changes['id']);
        unset($changes['createdAt']);
        unset($changes['updatedAt']);

        try {
            foreach ($changes as $property => $value) {
                if (is_array($value) && $this->isPropertyOneToManyRelationship($property)) {
                    $this->processChildren($property, $value);
                } elseif ($this->isPropertyOneToOneRelationship($property)) {
                    $this->processOneToOne($property, $value);
                } elseif ($this->isPropertyParentRelationship($property)) {
                    // TODO: Make this check the ID, load the referenced entity and set it. But this would break MVC.
                    continue;
                } else {
                    $setter = 'set' . ucfirst($property);
                    if (method_exists($this, $setter)) {
                        if ($this->getSetterParamType($setter) == 'DateTime') {
                            $value = $value ? new \DateTime($value) : null;
                        }
                        $this->$setter($value);
                    }
                }
            }
        } catch (\Exception $e) {
            print $e->getMessage();
            exit();
        }
    }

    /**
     * @param string $property
     * @param array $children
     */
    private function processChildren($property, $children)
    {
        $getter = 'get' . ucfirst($property);
        if (!method_exists($this, $getter)) {
            return;
        }

        /** @var ArrayCollection $collection */
        $collection = $this->$getter();
        foreach ($children as $childEl) {
            // Search the collection for the right child
            $childEntity = $collection->filter(function (CoreEntity $entity) use ($childEl) {
                return $entity->getId() == $childEl['id'];
            })->first();

            $childEntity->applyChangesFromArray($childEl);

            // TODO: handle creating and deleting child entities
        }
    }

    /**
     * @param string $property
     * @param array $children
     */
    private function processOneToOne($property, $changes)
    {
        $getter = 'get' . ucfirst($property);
        if (!method_exists($this, $getter)) {
            return;
        }

        /** @var CoreEntity $entity */
        $entity = $this->$getter();

        $entity->applyChangesFromArray($changes);
    }

    private function getClassAnnotations()
    {
        $reader = new AnnotationReader();
        $reflClass = new \ReflectionClass(get_class($this));
        return $reader->getClassAnnotations($reflClass);
    }

    private function getPropertyAnnotations($property)
    {
        $reader = new AnnotationReader();
        $reflClass = new \ReflectionClass(get_class($this));

        if (!$reflClass->hasProperty($property)) {
            return false;
        }

        return $reader->getPropertyAnnotations($reflClass->getProperty($property));
    }

    private function getSetterParamType($setter)
    {
        $reflClass = new \ReflectionClass(get_class($this));
        $reflMethod = $reflClass->getMethod($setter);
        $reflParameters = $reflMethod->getParameters();
        return $reflParameters[0]->getClass() ? $reflParameters[0]->getClass()->getName() : null;
    }

    private function isPropertyOneToManyRelationship($property)
    {
        $annotations = $this->getPropertyAnnotations($property);
        if (!$annotations) {
            return false;
        }

        return array_reduce($annotations, function ($carry, $annotation) {
            if ($annotation instanceof OneToMany) {
                return true;
            }

            return $carry;
        }, false);
    }

    private function isPropertyOneToOneRelationship($property)
    {
        $annotations = $this->getPropertyAnnotations($property);
        if (!$annotations) {
            return false;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof OneToOne) {
                return true;
            }
        }

        return false;
    }

    private function isPropertyParentRelationship($property)
    {
        $annotations = $this->getPropertyAnnotations($property);
        if (!$annotations) {
            return false;
        }

        return array_reduce($annotations, function ($carry, $annotation) {
            if ($annotation instanceof ManyToMany || $annotation instanceof ManyToOne) {
                return true;
            }

            return $carry;
        }, false);
    }
}
