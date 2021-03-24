<?php

namespace App\EventSubscriber\Workflow;

use App\Entity\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class ClientSubscriber implements EventSubscriberInterface
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

    public function onTransitionActivate(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $status = $event->getSubject()->getStatus();

        if (
            $this->checker->isGranted(Client::ROLE_MANAGE_OWN) && in_array($status, [
                    Client::STATUS_CREATION,
                    Client::STATUS_INACTIVE,
                ], true)
        ) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionDeactivate(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $status = $event->getSubject()->getStatus();

        if (
            $this->checker->isGranted(Client::ROLE_MANAGE_OWN) && in_array($status, [
                Client::STATUS_CREATION,
                Client::STATUS_INACTIVE,
            ], true)
        ) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionDeactivateExpire(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionDeactivateDuplicate(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionDeactivateBlocked(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.client_management.guard' => 'onTransition',
            'workflow.client_management.guard.ACTIVATE' => 'onTransitionActivate',
            'workflow.client_management.guard.BLOCK' => 'onTransitionDeactivateBlocked',
            'workflow.client_management.guard.DEACTIVATE' => 'onTransitionDeactivate',
            'workflow.client_management.guard.EXPIRE' => 'onTransitionDeactivateExpire',
            'workflow.client_management.guard.DUPLICATE' => 'onTransitionDeactivateDuplicate',
        ];
    }

    protected function canEditAll(): bool
    {
        return $this->checker->isGranted('ROLE_ADMIN')
            || $this->checker->isGranted(Client::ROLE_EDIT_ALL)
        ;
    }
}
