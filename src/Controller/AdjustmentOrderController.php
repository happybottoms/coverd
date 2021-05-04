<?php

namespace App\Controller;

use App\Entity\Orders\AdjustmentOrder;
use App\Entity\Orders\AdjustmentOrderLineItem;
use App\Entity\StorageLocation;
use App\Security\AdjustmentOrderVoter;
use App\Transformers\AdjustmentOrderTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

/**
 * @Route(path="/api/orders/adjustment")
 */
class AdjustmentOrderController extends BaseOrderController
{
    protected $defaultEntityName = AdjustmentOrder::class;

    /**
     * Save a new Adjustment
     *
     * @Route(path="", methods={"POST"})
     * @IsGranted({"ROLE_ADJUSTMENT_ORDER_EDIT"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $params = $this->getParams($request);

        $order = new AdjustmentOrder();

        if ($params['storageLocation']['id']) {
            $newLocation = $this->getEm()->find(StorageLocation::class, $params['storageLocation']['id']);
            $order->setStorageLocation($newLocation);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->denyAccessUnlessGranted(AdjustmentOrderVoter::EDIT, $order);

        $this->getEm()->persist($order);
        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     * @IsGranted({"ROLE_ADJUSTMENT_ORDER_EDIT"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \App\Exception\CommittedTransactionException
     */
    public function update(Request $request, int $id)
    {
        $params = $this->getParams($request);
        /** @var \App\Entity\Orders\AdjustmentOrder $order */
        $order = $this->getOrder($id);

        $this->checkEditable($order);

        $this->denyAccessUnlessGranted(AdjustmentOrderVoter::EDIT, $order);

        if ($params['storageLocation']['id']) {
            $newLocation = $this->getEm()->find(StorageLocation::class, $params['storageLocation']['id']);
            $order->setStorageLocation($newLocation);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * @Route("/{id}/transition", methods={"PATCH"})
     * @IsGranted({"ROLE_ADJUSTMENT_ORDER_EDIT"})
     */
    public function transition(Request $request, Registry $workflowRegistry, int $id): JsonResponse
    {
        return parent::transition($request, $workflowRegistry, $id);
    }

    protected function getDefaultTransformer(): AdjustmentOrderTransformer
    {
        return new AdjustmentOrderTransformer();
    }

    protected function createLineItem()
    {
        return new AdjustmentOrderLineItem();
    }

    protected function getEditVoter(): string
    {
        return AdjustmentOrderVoter::EDIT;
    }

    protected function getViewVoter(): string
    {
        return AdjustmentOrderVoter::VIEW;
    }
}
