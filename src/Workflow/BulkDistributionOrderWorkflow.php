<?php

namespace App\Workflow;

use App\Entity\Orders\BulkDistribution;

class BulkDistributionOrderWorkflow extends BaseWorkflow
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
            BulkDistribution::class,
        ],
        'initial_marking' => BulkDistribution::STATUS_CREATING,
        'places' => BulkDistribution::STATUSES,
        'transitions' => [
            self::TRANSITION_PENDING => [
                'metadata' => [
                    'title' => 'Mark As Pending'
                ],
                'from' => [
                    BulkDistribution::STATUS_CREATING,
                ],
                'to' => BulkDistribution::STATUS_PENDING,
            ],
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Complete'
                ],
                'from' => [
                    BulkDistribution::STATUS_PENDING,
                ],
                'to' => BulkDistribution::STATUS_COMPLETED,
            ],
        ],
    ];
}
