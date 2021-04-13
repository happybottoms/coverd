<?php

namespace App\Workflow;

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
        if ($this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionSubmit(GuardEvent $event): void
    {
        $status = $event->getSubject()->getStatus();

        if (
            $this->canEditAll() && in_array(
                $status,
                [
                    Partner::STATUS_ACTIVE,
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_START,
                ],
                true
            )
        ) {
             return;
        }

        if ($status === Partner::STATUS_START && $this->checker->isGranted(Partner::ROLE_MANAGE_OWN)) {
            return;
        }

        $event->setBlocked(true);
    }

    public function onTransitionSubmitPriority(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionFlagForReview(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionFlagForReviewPastDue(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionActivate(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionReviewed(GuardEvent $event): void
    {
        if ($this->canEditAll() || $this->checker->isGranted(Partner::ROLE_MANAGE_OWN)) {
            return;
        }
        $event->setBlocked(true);
    }

    public function onTransitionDeactivate(GuardEvent $event): void
    {
        if ($this->canEditAll()) {
            return;
        }
        $event->setBlocked(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.partner_management.guard' => 'onTransition',
            'workflow.partner_management.guard.ACTIVATE' => 'onTransitionActivate',
            'workflow.partner_management.guard.DEACTIVATE' => 'onTransitionDeactivate',
            'workflow.partner_management.guard.FLAG_FOR_REVIEW' => 'onTransitionFlagForReview',
            'workflow.partner_management.guard.FLAG_FOR_REVIEW_PAST_DUE' => 'onTransitionFlagForReviewPastDue',
            'workflow.partner_management.guard.REVIEWED' => 'onTransitionReviewed',
            'workflow.partner_management.guard.SUBMIT' => 'onTransitionSubmit',
            'workflow.partner_management.guard.SUBMIT_PRIORITY' => 'onTransitionSubmitPriority',
        ];
    }

    protected function canEditAll(): bool
    {
        return $this->checker->isGranted('ROLE_ADMIN')
            || $this->checker->isGranted(Partner::ROLE_EDIT_ALL)
        ;
    }
}
