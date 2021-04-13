<?php

namespace App\Listener;

use App\Entity\Partner;
use App\Workflow\PartnerWorkflow;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Workflow\Registry;

class PartnerListener implements EventSubscriber
{
    /** @var Registry */
    protected $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $partner = $event->getEntity();
        if (!$partner instanceof Partner) {
            return;
        }

        if ($partner->isReviewing() && !$event->hasChangedField('status')) {
            $this->workflowRegistry
                ->get($partner)
                ->apply($partner, PartnerWorkflow::TRANSITION_REVIEWED);
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }
}
