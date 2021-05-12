<?php

namespace App\Listener;

use App\Entity\EAV\Attribute;
use App\Entity\EAV\PartnerProfileAttributeDefinition;
use App\Entity\PartnerProfile;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PartnerProfileListener
{
    public function postLoad(PartnerProfile $profile, LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $definitions = $em->getRepository(PartnerProfileAttributeDefinition::class)->findAll();

        $emptyAttributes = [];

        foreach ($definitions as $definition) {
            if (!$profile->hasAttributeForDefinition($definition)) {
                $emptyAttributes[] = new Attribute($definition);
            }
        }

        $profile->addAttributes($emptyAttributes, false);
    }
}
