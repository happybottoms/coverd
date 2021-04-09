<?php

use App\Workflow\AdjustmentOrderWorkflow;
use App\Workflow\BulkDistributionOrderWorkflow;
use App\Workflow\ClientWorkflow;
use App\Workflow\MerchandiseOrderWorkflow;
use App\Workflow\PartnerOrderWorkflow;
use App\Workflow\PartnerWorkflow;
use App\Workflow\SupplyOrderWorkflow;
use App\Workflow\TransferOrderWorkflow;

$container->loadFromExtension('framework', [
    'workflows' => [
        'adjustment_order' => AdjustmentOrderWorkflow::WORKFLOW,
        'bulkdistribution_order' => BulkDistributionOrderWorkflow::WORKFLOW,
        'client_management' => ClientWorkflow::WORKFLOW,
        'merchandise_order' => MerchandiseOrderWorkflow::WORKFLOW,
        'partner_management' => PartnerWorkflow::WORKFLOW,
        'partner_order' => PartnerOrderWorkflow::WORKFLOW,
        'supply_order' => SupplyOrderWorkflow::WORKFLOW,
        'transfer_order' => TransferOrderWorkflow::WORKFLOW,
    ],
]);
