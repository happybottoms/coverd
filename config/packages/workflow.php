<?php

use App\Entity\Client;
use App\Entity\Partner;

$container->loadFromExtension('framework', [
    'workflows' => [
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
                Partner::STATUS_START,
                Partner::STATUS_APPLICATION_PENDING,
                Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                Partner::STATUS_ACTIVE,
                Partner::STATUS_NEEDS_PROFILE_REVIEW,
                Partner::STATUS_REVIEW_PAST_DUE,
                Partner::STATUS_INACTIVE,
            ],
            'transitions' => [
                Partner::TRANSITION_SUBMIT => [
                    'metadata' => [
                        'title' => 'Submit'
                    ],
                    'from' => [
                        Partner::STATUS_START,
                        Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                        Partner::STATUS_INACTIVE,
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
                        Partner::STATUS_NEEDS_PROFILE_REVIEW,
                        Partner::STATUS_REVIEW_PAST_DUE,
                        Partner::STATUS_INACTIVE,
                    ],
                    'to' => Partner::STATUS_ACTIVE,
                ],
                Partner::TRANSITION_FLAG_FOR_REVIEW => [
                    'metadata' => [
                        'title' => 'Flag for Review'
                    ],
                    'from' => [
                        Partner::STATUS_APPLICATION_PENDING,
                        Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                        Partner::STATUS_ACTIVE,
                        Partner::STATUS_REVIEW_PAST_DUE,
                        Partner::STATUS_INACTIVE,
                    ],
                    'to' => Partner::STATUS_NEEDS_PROFILE_REVIEW,
                ],
                Partner::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE => [
                    'metadata' => [
                        'title' => 'Flag for Review Past Due'
                    ],
                    'from' => [
                        Partner::STATUS_APPLICATION_PENDING,
                        Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                        Partner::STATUS_ACTIVE,
                        Partner::STATUS_NEEDS_PROFILE_REVIEW,
                        Partner::STATUS_INACTIVE,
                    ],
                    'to' => Partner::STATUS_REVIEW_PAST_DUE,
                ],
                Partner::TRANSITION_DEACTIVATE => [
                    'metadata' => [
                        'title' => 'Deactivate'
                    ],
                    'from' => [
                        Partner::STATUS_APPLICATION_PENDING,
                        Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                        Partner::STATUS_ACTIVE,
                        Partner::STATUS_NEEDS_PROFILE_REVIEW,
                        Partner::STATUS_REVIEW_PAST_DUE,
                    ],
                    'to' => Partner::STATUS_INACTIVE,
                ],
            ],
        ],
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
                Client::STATUS_CREATION,
                Client::STATUS_ACTIVE,
                Client::STATUS_INACTIVE,
                Client::STATUS_LIMIT_REACHED,
                Client::STATUS_DUPLICATE_INACTIVE,
            ],
            'transitions' => [
                Client::TRANSITION_ACTIVATE => [
                    'metadata' => [
                        'title' => 'Activate'
                    ],
                    'from' => [
                        Client::STATUS_CREATION,
                        Client::STATUS_INACTIVE,
                        Client::STATUS_LIMIT_REACHED,
                        Client::STATUS_DUPLICATE_INACTIVE,
                    ],
                    'to' => Client::STATUS_ACTIVE,
                ],
                Client::TRANSITION_DEACTIVATE => [
                    'metadata' => [
                        'title' => 'Deactivate'
                    ],
                    'from' => [
                        Client::STATUS_CREATION,
                        Client::STATUS_ACTIVE,
                        Client::STATUS_LIMIT_REACHED,
                        Client::STATUS_DUPLICATE_INACTIVE,
                    ],
                    'to' => Client::STATUS_INACTIVE,
                ],
                Client::TRANSITION_EXPIRE => [
                    'metadata' => [
                        'title' => 'Expire Client'
                    ],
                    'from' => [
                        Client::STATUS_CREATION,
                        Client::STATUS_ACTIVE,
                        Client::STATUS_INACTIVE,
                        Client::STATUS_DUPLICATE_INACTIVE,
                    ],
                    'to' => Client::STATUS_LIMIT_REACHED,
                ],
                Client::TRANSITION_DUPLICATE_INACTIVE => [
                    'metadata' => [
                        'title' => 'Duplicate (inactivate)'
                    ],
                    'from' => [
                        Client::STATUS_CREATION,
                        Client::STATUS_ACTIVE,
                        Client::STATUS_INACTIVE,
                        Client::STATUS_LIMIT_REACHED,
                    ],
                    'to' => Client::STATUS_DUPLICATE_INACTIVE,
                ],
            ],
        ],
    ],
]);
