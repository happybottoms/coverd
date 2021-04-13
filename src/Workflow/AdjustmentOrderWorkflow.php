<?php

namespace App\Workflow;

use App\Entity\Orders\AdjustmentOrder;

class AdjustmentOrderWorkflow extends BaseWorkflow
{
    public const TRANSITION_COMPLETE = 'COMPLETE';

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
            AdjustmentOrder::class,
        ],
        'initial_marking' => AdjustmentOrder::STATUS_CREATING,
        'places' => AdjustmentOrder::STATUSES,
        'transitions' => [
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Complete'
                ],
                'from' => [
                    AdjustmentOrder::STATUS_CREATING
                ],
                'to' => AdjustmentOrder::STATUS_COMPLETED,
            ],
        ],
    ];
}
