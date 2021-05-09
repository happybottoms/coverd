<?php

namespace App\Listener;

use App\Entity\Client;
use App\Entity\EAV\Attribute;
use App\Entity\EAV\ClientAttributeDefinition;
use App\IdGenerator\RandomIdGenerator;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class ClientListener implements EventSubscriber
{
    /**
     * @var RandomIdGenerator
     */
    protected $gen;

    public function __construct(RandomIdGenerator $gen)
    {
        $this->gen = $gen;
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $client = $event->getEntity();
        if (!$client instanceof Client) {
            return;
        }

        $em = $event->getEntityManager();
        $definitions = $em->getRepository(ClientAttributeDefinition::class)->findAll();

        $emptyAttributes = [];

        foreach ($definitions as $definition) {
            if (!$client->hasAttributeForDefinition($definition)) {
                $emptyAttributes[] = new Attribute($definition);
            }
        }

        $client->addAttributes($emptyAttributes, false);
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $client = $event->getEntity();
        if (!$client instanceof Client) {
            return;
        }

        if (!$client->getPublicId()) {
            $client->setPublicId($this->gen->generate());
        }

        $client->calculateAgeExpiration();
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $client = $event->getEntity();
        if (!$client instanceof Client) {
            return;
        }

        $client->calculateAgeExpiration();
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
            Events::prePersist,
            Events::preUpdate,
        ];
    }
}
