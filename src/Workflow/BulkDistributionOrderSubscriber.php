<?php

namespace App\Workflow;

use App\Entity\Orders\BulkDistribution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class BulkDistributionOrderSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $checker;

    public function __construct(AuthorizationCheckerInterface $checker)
    {
        $this->checker = $checker;
    }

    public function onTransition(GuardEvent $event): void
    {
        if ($this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionComplete(GuardEvent $event): void
    {
        if ($this->canEditAll() || $this->canEditOwn()) {
            return;
        }
        $event->setBlocked(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.partner_order.guard' => 'onTransition',
            'workflow.partner_order.guard.COMPLETE' => 'onTransitionComplete',
        ];
    }

    protected function canEditAll(): bool
    {
        return $this->checker->isGranted('ROLE_ADMIN')
            || $this->checker->isGranted(BulkDistribution::ROLE_EDIT_ALL)
        ;
    }

    protected function canEditOwn(): bool
    {
        return $this->checker->isGranted(BulkDistribution::ROLE_MANAGE_OWN);
    }
}
