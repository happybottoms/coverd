<?php

namespace App\Workflow;

use App\Entity\Orders\SupplyOrder;

class SupplyOrderWorkflow extends BaseWorkflow
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
            SupplyOrder::class,
        ],
        'initial_marking' => SupplyOrder::STATUS_CREATING,
        'places' => SupplyOrder::STATUSES,
        'transitions' => [
            SupplyOrder::TRANSITION_ORDER => [
                'metadata' => [
                    'title' => 'Order',
                ],
                'from' => [
                    SupplyOrder::STATUS_CREATING,
                ],
                'to' => SupplyOrder::STATUS_ORDERED,
            ],
            SupplyOrder::TRANSITION_RECEIVE => [
                'metadata' => [
                    'title' => 'Receive',
                ],
                'from' => [
                    SupplyOrder::STATUS_ORDERED,
                ],
                'to' => SupplyOrder::STATUS_RECEIVED,
            ],
            SupplyOrder::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Complete'
                ],
                'from' => [
                    SupplyOrder::STATUS_RECEIVED,
                ],
                'to' => SupplyOrder::STATUS_COMPLETED,
            ],
        ],
    ];
}
