<?php

namespace App\Controller;

use App\Entity\Orders\MerchandiseOrder;
use App\Entity\Orders\MerchandiseOrderLineItem;
use App\Entity\Warehouse;
use App\Transformers\MerchandiseOrderTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PartnerOrderController
 * @package App\Controller
 *
 * @Route(path="/api/orders/merchandise")
 */
class MerchandiseOrderController extends OrderController
{
    protected $defaultEntityName = MerchandiseOrder::class;

    /**
     * @return MerchandiseOrderLineItem
     */
    protected function createLineItem()
    {
        return new MerchandiseOrderLineItem();
    }

    /**
     * Save a new product
     *
     * @Route(path="", methods={"POST"})
     * @IsGranted({"ROLE_MERCHANDISE_ORDER_EDIT"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $params = $this->getParams($request);
        $order = new MerchandiseOrder();

        if ($params['warehouse']['id']) {
            $newWarehouse = $this->getEm()->find(Warehouse::class, $params['warehouse']['id']);
            $order->setWarehouse($newWarehouse);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->persist($order);
        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     * @IsGranted({"ROLE_MERCHANDISE_ORDER_EDIT"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \App\Exception\CommittedTransactionException
     */
    public function update(Request $request, $id)
    {
        $params = $this->getParams($request);
        /** @var MerchandiseOrder $order */
        $order = $this->getOrder($id);

        $this->checkEditable($order);

        if ($params['warehouse']['id']) {
            $newWarehouse = $this->getEm()->find(Warehouse::class, $params['warehouse']['id']);
            $order->setWarehouse($newWarehouse);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }



    protected function getDefaultTransformer()
    {
        return new MerchandiseOrderTransformer();
    }
}
