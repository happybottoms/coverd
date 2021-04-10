<?php

namespace App\Workflow;

use App\Entity\Partner;

class PartnerWorkflow extends BaseWorkflow
{
    public const TRANSITION_ACTIVATE = 'ACTIVATE';
    public const TRANSITION_DEACTIVATE = 'DEACTIVATE';
    public const TRANSITION_FLAG_FOR_REVIEW = 'FLAG_FOR_REVIEW';
    public const TRANSITION_FLAG_FOR_REVIEW_PAST_DUE = 'FLAG_FOR_REVIEW_PAST_DUE';
    public const TRANSITION_REVIEWED = 'REVIEWED';
    public const TRANSITION_SUBMIT = 'SUBMIT';
    public const TRANSITION_SUBMIT_PRIORITY = 'SUBMIT_PRIORITY';

    public const WORKFLOW = [
        'type' => 'state_machine',
        'audit_trail' => [
            'enabled' => true,
        ],
        'marking_store' => [
            'type' => 'method',
            'property' => 'status',
        ],
        'supports' => [
            Partner::class,
        ],
        'initial_marking' => Partner::STATUS_START,
        'places' => Partner::STATUSES,
        'transitions' => [
            self::TRANSITION_SUBMIT => [
                'metadata' => [
                    'title' => 'Submit'
                ],
                'from' => [
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_INACTIVE,
                    Partner::STATUS_START,
                ],
                'to' => Partner::STATUS_APPLICATION_PENDING,
            ],
            self::TRANSITION_SUBMIT_PRIORITY => [
                'metadata' => [
                    'title' => 'Submit (Priority)'
                ],
                'from' => [
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_INACTIVE,
                ],
                'to' => Partner::STATUS_APPLICATION_PENDING_PRIORITY,
            ],
            self::TRANSITION_ACTIVATE => [
                'metadata' => [
                    'title' => 'Activate'
                ],
                'from' => [
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_INACTIVE,
                    Partner::STATUS_NEEDS_PROFILE_REVIEW,
                    Partner::STATUS_REVIEW_PAST_DUE,
                ],
                'to' => Partner::STATUS_ACTIVE,
            ],
            self::TRANSITION_REVIEWED => [
                'metadata' => [
                    'title' => 'Reviewed'
                ],
                'from' => [
                    Partner::STATUS_NEEDS_PROFILE_REVIEW,
                    Partner::STATUS_REVIEW_PAST_DUE,
                ],
                'to' => Partner::STATUS_ACTIVE,
            ],
            self::TRANSITION_FLAG_FOR_REVIEW => [
                'metadata' => [
                    'title' => 'Flag for Review'
                ],
                'from' => [
                    Partner::STATUS_ACTIVE,
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_INACTIVE,
                    Partner::STATUS_REVIEW_PAST_DUE,
                ],
                'to' => Partner::STATUS_NEEDS_PROFILE_REVIEW,
            ],
            self::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE => [
                'metadata' => [
                    'title' => 'Flag for Review Past Due'
                ],
                'from' => [
                    Partner::STATUS_ACTIVE,
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_INACTIVE,
                    Partner::STATUS_NEEDS_PROFILE_REVIEW,
                ],
                'to' => Partner::STATUS_REVIEW_PAST_DUE,
            ],
            self::TRANSITION_DEACTIVATE => [
                'metadata' => [
                    'title' => 'Deactivate'
                ],
                'from' => [
                    Partner::STATUS_ACTIVE,
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_NEEDS_PROFILE_REVIEW,
                    Partner::STATUS_REVIEW_PAST_DUE,
                ],
                'to' => Partner::STATUS_INACTIVE,
            ],
        ],
    ];
}
