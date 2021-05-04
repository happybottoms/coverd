<?php

namespace App\Workflow;

use App\Entity\Orders\PartnerOrder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class PartnerOrderSubscriber implements EventSubscriberInterface
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

    public function onTransitionAccept(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionComplete(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionBackToCreating(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionSubmit(GuardEvent $event): void
    {
        if ($this->canEditAll() || $this->checker->isGranted(PartnerOrder::ROLE_EDIT)) {
            return;
        }
        $event->setBlocked(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.partner_order.guard' => 'onTransition',
            'workflow.partner_order.guard.ACCEPT' => 'onTransitionAccept',
            'workflow.partner_order.guard.COMPLETE' => 'onTransitionComplete',
            'workflow.partner_order.guard.REVERT_TO_CREATING' => 'onTransitionBackToCreating',
            'workflow.partner_order.guard.SUBMIT' => 'onTransitionSubmit',
        ];
    }

    protected function canEditAll(): bool
    {
        return $this->checker->isGranted('ROLE_ADMIN')
            || $this->checker->isGranted(PartnerOrder::ROLE_EDIT_ALL)
        ;
    }
}
