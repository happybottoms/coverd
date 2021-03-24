<?php

namespace App\Controller;

use App\Entity\Orders\SupplyOrder;
use App\Entity\Orders\SupplyOrderLineItem;
use App\Entity\Supplier;
use App\Entity\SupplierAddress;
use App\Entity\Warehouse;
use App\Security\SupplyOrderVoter;
use App\Transformers\SupplyOrderTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/orders/supply")
 */
class SupplyOrderController extends OrderController
{
    protected $defaultEntityName = SupplyOrder::class;

    /**
     * @return \App\Entity\Orders\SupplyOrderLineItem
     */
    protected function createLineItem()
    {
        return new SupplyOrderLineItem();
    }

    /**
     * Save a new Supply Order
     *
     * @Route(path="", methods={"POST"})
     * @IsGranted({"ROLE_SUPPLY_ORDER_EDIT"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $params = $this->getParams($request);

        $order = new SupplyOrder();

        if ($params['warehouse']['id']) {
            $newWarehouse = $this->getEm()->find(Warehouse::class, $params['warehouse']['id']);
            $order->setWarehouse($newWarehouse);
        }

        if ($params['supplier']['id']) {
            $newSupplier = $this->getEm()->find(Supplier::class, $params['supplier']['id']);
            $order->setSupplier($newSupplier);
        }

        if ($params['supplierAddress']['id']) {
            $newSupplierAddress = $this->getEm()->find(SupplierAddress::class, $params['supplierAddress']['id']);
            $order->setSupplierAddress($newSupplierAddress);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->denyAccessUnlessGranted(SupplyOrderVoter::EDIT, $order);

        $this->getEm()->persist($order);
        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     * @IsGranted({"ROLE_SUPPLY_ORDER_EDIT"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \App\Exception\CommittedTransactionException
     */
    public function update(Request $request, $id)
    {
        $params = $this->getParams($request);
        /** @var SupplyOrder $order */
        $order = $this->getOrder($id);

        $this->denyAccessUnlessGranted(SupplyOrderVoter::EDIT, $order);

        $this->checkEditable($order);

        if ($params['warehouse']['id']) {
            $newWarehouse = $this->getEm()->find('App\Entity\Warehouse', $params['warehouse']['id']);
            $order->setWarehouse($newWarehouse);
        }

        if ($params['supplier']['id']) {
            $newSupplier = $this->getEm()->find('App\Entity\Supplier', $params['supplier']['id']);
            $order->setSupplier($newSupplier);
        }

        if ($params['supplierAddress']['id']) {
            $newSupplierAddress = $this->getEm()->find('App\Entity\SupplierAddress', $params['supplierAddress']['id']);
            $order->setSupplierAddress($newSupplierAddress);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }



    protected function getDefaultTransformer()
    {
        return new SupplyOrderTransformer();
    }
}
