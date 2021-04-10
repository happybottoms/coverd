<?php

namespace App\Workflow;

use App\Entity\Orders\TransferOrder;

class TransferOrderWorkflow extends BaseWorkflow
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
            TransferOrder::class,
        ],
        'initial_marking' => TransferOrder::STATUS_CREATING,
        'places' => TransferOrder::STATUSES,
        'transitions' => [
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Complete'
                ],
                'from' => [
                    TransferOrder::STATUS_CREATING
                ],
                'to' => TransferOrder::STATUS_COMPLETED,
            ],
        ],
    ];
}
