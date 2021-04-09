<?php

use App\Entity\Client;
use App\Entity\Orders\AdjustmentOrder;
use App\Entity\Orders\BulkDistribution;
use App\Entity\Orders\MerchandiseOrder;
use App\Entity\Orders\PartnerOrder;
use App\Entity\Orders\SupplyOrder;
use App\Entity\Orders\TransferOrder;
use App\Entity\Partner;

$container->loadFromExtension('framework', [
    'workflows' => [
        'adjustment_order' => AdjustmentOrder::WORKFLOW,
        'bulkdistribution_order' => BulkDistribution::WORKFLOW,
        'client_management' => Client::WORKFLOW,
        'merchandise_order' => MerchandiseOrder::WORKFLOW,
        'partner_management' => Partner::WORKFLOW,
        'partner_order' => PartnerOrder::WORKFLOW,
        'supply_order' => SupplyOrder::WORKFLOW,
        'transfer_order' => TransferOrder::WORKFLOW,
    ],
]);
