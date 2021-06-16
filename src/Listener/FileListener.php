<?php

namespace App\Listener;

use App\Entity\File;
use App\IdGenerator\RandomIdGenerator;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class FileListener implements EventSubscriber
{
    /**
     * @var RandomIdGenerator
     */
    protected $gen;

    public function __construct(RandomIdGenerator $gen)
    {
        $this->gen = $gen;
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $file = $event->getEntity();
        if (!$file instanceof File) {
            return;
        }

        if (!$file->getPublicId()) {
            $file->setPublicId($this->gen->generate());
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }
}
