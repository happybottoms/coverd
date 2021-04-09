<?php

namespace App\Workflow;

use App\Entity\Partner;

class PartnerWorkflow
{
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
            Partner::TRANSITION_SUBMIT => [
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
            Partner::TRANSITION_SUBMIT_PRIORITY => [
                'metadata' => [
                    'title' => 'Submit (Priority)'
                ],
                'from' => [
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_INACTIVE,
                ],
                'to' => Partner::STATUS_APPLICATION_PENDING_PRIORITY,
            ],
            Partner::TRANSITION_ACTIVATE => [
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
            Partner::TRANSITION_REVIEWED => [
                'metadata' => [
                    'title' => 'Reviewed'
                ],
                'from' => [
                    Partner::STATUS_NEEDS_PROFILE_REVIEW,
                    Partner::STATUS_REVIEW_PAST_DUE,
                ],
                'to' => Partner::STATUS_ACTIVE,
            ],
            Partner::TRANSITION_FLAG_FOR_REVIEW => [
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
            Partner::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE => [
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
            Partner::TRANSITION_DEACTIVATE => [
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
