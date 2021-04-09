<?php

namespace App\Workflow;

use App\Entity\Orders\PartnerOrder;

class PartnerOrderWorkflow
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
        PartnerOrder::class,
    ],
    'initial_marking' => PartnerOrder::STATUS_CREATING,
    'places' => PartnerOrder::STATUSES,
    'transitions' => [
        PartnerOrder::TRANSITION_SUBMIT => [
            'metadata' => [
                'title' => 'Submit',
            ],
            'from' => [
                PartnerOrder::STATUS_CREATING,
            ],
            'to' => PartnerOrder::STATUS_SUBMITTED,
        ],
        PartnerOrder::TRANSITION_ACCEPT => [
            'metadata' => [
                'title' => 'Accept',
            ],
            'from' => [
                PartnerOrder::STATUS_COMPLETED,
                PartnerOrder::STATUS_SUBMITTED,
            ],
            'to' => PartnerOrder::STATUS_IN_PROCESS,
        ],
        PartnerOrder::TRANSITION_COMPLETE => [
            'metadata' => [
                'title' => 'Ship'
            ],
            'from' => [
                PartnerOrder::STATUS_IN_PROCESS,
            ],
            'to' => PartnerOrder::STATUS_COMPLETED,
        ],
        PartnerOrder::TRANSITION_REVERT_TO_CREATING => [
            'metadata' => [
                'title' => 'Revert To Creating'
            ],
            'from' => [
                PartnerOrder::STATUS_IN_PROCESS,
            ],
            'to' => PartnerOrder::STATUS_CREATING,
        ],
    ],
    ];
}
