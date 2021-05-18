<?php

namespace App\Listener;

use App\Entity\EAV\AttributeValue;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class AttributeValueListener
{
    public function preUpdate(AttributeValue $attribute, PreUpdateEventArgs $event): void
    {
        if ($attribute->isEmpty()) {
            $event->getEntityManager()->getUnitOfWork()->remove($attribute);
        }
    }

    public function prePersist(AttributeValue $attribute, LifecycleEventArgs $event): void
    {
        if ($attribute->isEmpty()) {
            $event->getEntityManager()->getUnitOfWork()->remove($attribute);
        }
    }
}
