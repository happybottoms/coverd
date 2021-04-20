<?php

namespace App\Listener;

use App\Entity\EAV\PartnerProfileDefinition;
use App\Entity\PartnerProfile;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PartnerProfileListener
{
    public function postLoad(PartnerProfile $profile, LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $definitions = $em->getRepository(PartnerProfileDefinition::class)->findAll();

        $emptyAttributes = [];

        foreach ($definitions as $definition) {
            if (!$profile->hasAttributeForDefinition($definition)) {
                $emptyAttributes[] = $definition->createAttribute();
            }
        }

        $profile->addAttributes($emptyAttributes, false);
    }
}
