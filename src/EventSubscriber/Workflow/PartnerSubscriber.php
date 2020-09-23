<?php

namespace App\EventSubscriber\Workflow;

use App\Entity\Partner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class PartnerSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $checker;

    public function __construct(AuthorizationCheckerInterface $checker)
    {
        $this->checker = $checker;
    }

    public function onTransition(GuardEvent $event): void
    {
        if (!$this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $event->setBlocked(true);
        }
    }

    public function onTransitionSubmit(GuardEvent $event): void
    {
        ['status' => $status] = $event->getSubject();
        if (
            !$this->checker->isGranted(Partner::ROLE_EDIT_ALL)
            || !($status === Partner::STATUS_START && $this->checker->isGranted(Partner::ROLE_MANAGE_OWN))
        ) {
            $event->setBlocked(true);
        }
    }

    public function onTransitionSubmitPriority(GuardEvent $event): void
    {
        if (!$this->checker->isGranted(Partner::ROLE_EDIT_ALL)) {
            $event->setBlocked(true);
        }
    }

    public function onTransitionFlagForReview(GuardEvent $event): void
    {
        if (!$this->checker->isGranted(Partner::ROLE_EDIT_ALL)) {
            $event->setBlocked(true);
        }
    }

    public function onTransitionFlagForReviewPastDue(GuardEvent $event): void
    {
        if (!$this->checker->isGranted(Partner::ROLE_EDIT_ALL)) {
            $event->setBlocked(true);
        }
    }

    public function onTransitionActivate(GuardEvent $event): void
    {
        if (!$this->checker->isGranted(Partner::ROLE_EDIT_ALL)) {
            $event->setBlocked(true);
        }
    }

    public function onTransitionDeactivate(GuardEvent $event): void
    {
        if (!$this->checker->isGranted(Partner::ROLE_EDIT_ALL)) {
            $event->setBlocked(true);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.partner_management.guard' => 'onTransition',
            'workflow.partner_management.guard.SUBMIT' => 'onTransitionSubmit',
            'workflow.partner_management.guard.SUBMIT_PRIORITY' => 'onTransitionSubmitPriority',
            'workflow.partner_management.guard.FLAG_FOR_REVIEW' => 'onTransitionFlagForReview',
            'workflow.partner_management.guard.FLAG_FOR_REVIEW_PAST_DUE' => 'onTransitionFlagForReviewPastDue',
            'workflow.partner_management.guard.ACTIVATE' => 'onTransitionActivate',
            'workflow.partner_management.guard.DEACTIVATE' => 'onTransitionDeactivate',
        ];
    }
}
