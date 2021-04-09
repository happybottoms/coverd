<?php

namespace App\Workflow;

use App\Entity\Client;

class ClientWorkflow
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
            Client::class,
        ],
        'initial_marking' => Client::STATUS_CREATION,
        'places' => Client::STATUSES,
        'transitions' => [
            Client::TRANSITION_ACTIVATE => [
                'metadata' => [
                    'title' => 'Activate'
                ],
                'from' => [
                    Client::STATUS_CREATION,
                    Client::STATUS_INACTIVE,
                    Client::STATUS_INACTIVE_BLOCKED,
                    Client::STATUS_INACTIVE_DUPLICATE,
                    Client::STATUS_INACTIVE_EXPIRED,
                    Client::STATUS_NEEDS_REVIEW,
                    Client::STATUS_REVIEW_PAST_DUE,
                ],
                'to' => Client::STATUS_ACTIVE,
            ],
            Client::TRANSITION_DEACTIVATE => [
                'metadata' => [
                    'title' => 'Deactivate'
                ],
                'from' => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_CREATION,
                    Client::STATUS_INACTIVE_BLOCKED,
                    Client::STATUS_INACTIVE_DUPLICATE,
                    Client::STATUS_INACTIVE_EXPIRED,
                ],
                'to' => Client::STATUS_INACTIVE,
            ],
            Client::TRANSITION_DEACTIVATE_EXPIRE => [
                'metadata' => [
                    'title' => 'Deactivate (Expire Client)'
                ],
                'from' => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_CREATION,
                    Client::STATUS_INACTIVE,
                    Client::STATUS_INACTIVE_DUPLICATE,
                ],
                'to' => Client::STATUS_INACTIVE_EXPIRED,
            ],
            Client::TRANSITION_DEACTIVATE_DUPLICATE => [
                'metadata' => [
                    'title' => 'Deactivate (Duplicate)'
                ],
                'from' => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_CREATION,
                    Client::STATUS_INACTIVE,
                    Client::STATUS_INACTIVE_EXPIRED,
                ],
                'to' => Client::STATUS_INACTIVE_DUPLICATE,
            ],
            Client::TRANSITION_FLAG_FOR_REVIEW => [
                'metadata' => [
                    'title' => 'Needs Review'
                ],
                'from' => [
                    Client::STATUS_ACTIVE,
                ],
                'to' => Client::STATUS_NEEDS_REVIEW,
            ],
            Client::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE => [
                'metadata' => [
                    'title' => 'Review Past Due'
                ],
                'from' => [
                    Client::STATUS_NEEDS_REVIEW,
                ],
                'to' => Client::STATUS_REVIEW_PAST_DUE,
            ],
            Client::TRANSITION_DEACTIVATE_BLOCKED => [
                'metadata' => [
                    'title' => 'Deactivate (Blocked)'
                ],
                'from' => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_CREATION,
                    Client::STATUS_INACTIVE,
                    Client::STATUS_INACTIVE_DUPLICATE,
                    Client::STATUS_INACTIVE_EXPIRED,
                    Client::STATUS_NEEDS_REVIEW,
                    Client::STATUS_REVIEW_PAST_DUE
                ],
                'to' => Client::STATUS_INACTIVE_BLOCKED,
            ],
        ],
    ];
}
