<?php

namespace App\Workflow;

use App\Entity\Orders\PartnerOrder;

class PartnerOrderWorkflow extends BaseWorkflow
{
    public const TRANSITION_ACCEPT = 'ACCEPT';
    public const TRANSITION_COMPLETE = 'SHIP';
    public const TRANSITION_REVERT_TO_CREATING = 'BACK_TO_CREATING';
    public const TRANSITION_SUBMIT = 'SUBMIT';

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
            self::TRANSITION_SUBMIT => [
                'metadata' => [
                    'title' => 'Submit',
                ],
                'from' => [
                    PartnerOrder::STATUS_CREATING,
                ],
                'to' => PartnerOrder::STATUS_SUBMITTED,
            ],
            self::TRANSITION_ACCEPT => [
                'metadata' => [
                    'title' => 'Accept',
                ],
                'from' => [
                    PartnerOrder::STATUS_COMPLETED,
                    PartnerOrder::STATUS_SUBMITTED,
                ],
                'to' => PartnerOrder::STATUS_IN_PROCESS,
            ],
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Ship'
                ],
                'from' => [
                    PartnerOrder::STATUS_IN_PROCESS,
                ],
                'to' => PartnerOrder::STATUS_COMPLETED,
            ],
            self::TRANSITION_REVERT_TO_CREATING => [
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
