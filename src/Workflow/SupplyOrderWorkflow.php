<?php

namespace App\Workflow;

use App\Entity\Orders\SupplyOrder;

class SupplyOrderWorkflow extends BaseWorkflow
{
    public const TRANSITION_ORDER = 'ORDER';
    public const TRANSITION_COMPLETE = 'RECEIVE';

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
            self::TRANSITION_ORDER => [
                'metadata' => [
                    'title' => 'Order',
                ],
                'from' => [
                    SupplyOrder::STATUS_CREATING,
                ],
                'to' => SupplyOrder::STATUS_ORDERED,
            ],
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Receive',
                ],
                'from' => [
                    SupplyOrder::STATUS_ORDERED,
                ],
                'to' => SupplyOrder::STATUS_COMPLETED,
            ],
        ],
    ];
}
