<?php

use App\Entity\Client;
use App\Entity\Partner;

$container->loadFromExtension('framework', [
    'workflows' => [
        'client_management' => [
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
            'places' => [
                Client::STATUS_ACTIVE,
                Client::STATUS_CREATION,
                Client::STATUS_INACTIVE,
                Client::STATUS_INACTIVE_BLOCKED,
                Client::STATUS_INACTIVE_DUPLICATE,
                Client::STATUS_INACTIVE_EXPIRED,
                Client::STATUS_NEEDS_REVIEW,
                Client::STATUS_REVIEW_PAST_DUE,
            ],
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
        ],
        'partner_management' => [
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
            'places' => [
                Partner::STATUS_ACTIVE,
                Partner::STATUS_APPLICATION_PENDING,
                Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                Partner::STATUS_INACTIVE,
                Partner::STATUS_NEEDS_PROFILE_REVIEW,
                Partner::STATUS_REVIEW_PAST_DUE,
                Partner::STATUS_START,
            ],
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
        ],
    ],
]);
