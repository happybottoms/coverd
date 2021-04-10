<?php

namespace App\Workflow;

use App\Entity\Orders\MerchandiseOrder;

class MerchandiseOrderWorkflow extends BaseWorkflow
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
            MerchandiseOrder::class,
        ],
        'initial_marking' => MerchandiseOrder::STATUS_CREATING,
        'places' => MerchandiseOrder::STATUSES,
        'transitions' => [
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Complete'
                ],
                'from' => [
                    MerchandiseOrder::STATUS_CREATING
                ],
                'to' => MerchandiseOrder::STATUS_COMPLETED,
            ],
        ],
    ];
}
