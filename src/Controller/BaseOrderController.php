<?php

namespace App\Controller;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Exception\CommittedTransactionException;
use App\Repository\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

abstract class BaseOrderController extends BaseController
{
    protected $defaultEntityName = Order::class;

    /**
     * Get a list of Sub-classed orders
     *
     * @Route(path="", methods={"GET"})
     * @IsGranted({"ROLE_ORDER_VIEW_ALL", "ROLE_ORDER_MANAGE_OWN"})
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('page', 1);
        $limit = $request->get('per_page', 10);

        $params = $this->buildFilterParams($request);

        /** @var OrderRepository $repo */
        $repo = $this->getRepository();

        $orders = $repo->findAllPaged(
            $page,
            $limit,
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        $total = $repo->findAllCount($params);

        $meta = [
            'pagination' => [
                'total' => (int) $total,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $limit),
                'next_page_url' => null,
                'prev_page_url' => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => ($page * $limit),
            ]
        ];

        return $this->serialize($request, $orders, null, $meta);
    }

    /**
     * Get a single Order
     *
     * @Route(path="/{id<\d+>}", methods={"GET"})
     * @IsGranted({"ROLE_ORDER_VIEW_ALL","ROLE_ORDER_MANAGE_OWN"})
     */
    public function show(Request $request, int $id, Registry $workflowRegistry): JsonResponse
    {
        $order = $this->getOrder($id);

        $this->denyAccessUnlessGranted($this->getViewVoter(), $order);

        $meta = [
            'enabledTransitions' => $this->getEnabledTransitions($workflowRegistry, $order),
        ];

        return $this->serialize($request, $order, null, $meta);
    }

    /**
     *
     * @Route(path="/bulk", methods={"GET"})
     * @IsGranted({"ROLE_ORDER_VIEW_ALL","ROLE_ORDER_MANAGE_OWN"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkShow(Request $request)
    {
        $ids = $request->get('ids');

        $orders = $this->getOrders($ids);

        return $this->serialize($request, $orders);
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     * @IsGranted({"ROLE_ORDER_EDIT_ALL","ROLE_ORDER_MANAGE_OWN"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $params = $this->getParams($request);
        /** @var Order $order */
        $order = $this->getOrder($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);

        $this->processLineItems($order, $params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Delete an order
     *
     * @Route(path="/{id<\d+>}", methods={"DELETE"})
     * @IsGranted({"ROLE_ADMIN"})
     */
    public function destroy(int $id): JsonResponse
    {
        $order = $this->getOrder($id);

        if (!$order->isDeletable()) {
            throw new \Exception('Order has been committed and cannot be deleted.');
        }

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);

        $this->getEm()->remove($order);

        $this->getEm()->flush();

        return $this->success(sprintf('Order %s deleted.', $order->getId()));
    }

    /**
     *
     * @Route(path="/bulk-change", methods={"PATCH"})
     * @IsGranted({"ROLE_ORDER_EDIT_ALL"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkChange(Request $request)
    {
        $ids = $request->get('ids');
        $changes = $request->get('changes');

        /** @var Order[] $orders */
        $orders = $this->getRepository()->findBy(['id' => $ids]);

        foreach ($orders as $order) {
            // TODO: get permissions working (#1)
            // $this->checkEditPermissions($order);
            $this->checkEditable($order);

            $order->applyChangesFromArray($changes);
            $order->updateTransactions();
        }

        $this->getEm()->flush();

        return $this->success(sprintf("Orders %s have been updated", implode(", ", $ids)));
    }

    /**
     *
     * @Route(path="/bulk-delete", methods={"PATCH"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $params = $this->getParams($request);
        $ids = $params['ids'] ?? null;

        /** @var Order[] $orders */
        $orders = $this->getRepository()->findBy(['id' => $ids]);

        foreach ($orders as $order) {
            $this->getEm()->remove($order);
        }

        $this->getEm()->flush();

        return $this->success(sprintf("Orders %s have been deleted", implode(", ", $ids)));
    }

    /**
     * NOTE: Call this method from child controllers so we can use the right roles for the IsGranted annotation
     *       -- The Route annotation will need to be copied to the child class, too
     * @Route("/{id}/transition", methods={"PATCH"})
     * @IsGranted({"ROLE_ORDER_EDIT"})
     */
    public function transition(Request $request, Registry $workflowRegistry, int $id): JsonResponse
    {
        /** @var Order $order */
        $order = $this->getOrder($id);
        $this->denyAccessUnlessGranted($this->getEditVoter(), $order);

        $params = $this->getParams($request);

        if ($params['transition']) {
            $workflowRegistry
                ->get($order)
                ->apply($order, $params['transition']);

            $this->getEm()->flush();
        }

        $meta = [
            'enabledTransitions' => $this->getEnabledTransitions($workflowRegistry, $order),
        ];

        return $this->serialize($request, $order, null, $meta);
    }

    protected function getOrder(int $id): Order
    {
        /** @var ?Order $order */
        $order = $this->getRepository()->find($id);

        if (!$order) {
            throw new NotFoundHttpException(sprintf('Unknown Order ID: %d', $id));
        }

        return $order;
    }

    /**
     * @param int[] $ids
     * @return Order[]
     * @throws NotFoundHttpException
     */
    protected function getOrders(array $ids)
    {
        /** @var Order[] $orders */
        $orders = $this->getRepository()->findBy(['id' => $ids]);

        return $orders;
    }

    protected function processLineItems(Order $order, array $lineItemsArray): void
    {
        foreach ($lineItemsArray as $lineItemArray) {
            if (!isset($lineItemArray['id']) && (!$lineItemArray['product']['id'] || !$lineItemArray['quantity'])) {
                continue;
            }

            if (isset($lineItemArray['id']) && $lineItemArray['id'] !== 0) {
                /** @var LineItem $line */
                $line = $order->getLineItem($lineItemArray['id']);
            } else {
                $line = $this->createLineItem();
                $order->addLineItem($line);
            }

            $this->extraLineItemProcessing($line, $lineItemArray);

            if (!$lineItemArray['product']['id']) {
                $order->removeLineItem($line);
                continue;
            }

            if ($line->getProduct()->getId() !== $lineItemArray['product']['id']) {
                $product = $this->getEm()->getReference(Product::class, $lineItemArray['product']['id']);
                $line->setProduct($product);
            }

            $line->applyChangesFromArray($lineItemArray);

            if (!$line->getQuantity()) {
                $order->removeLineItem($line);
                continue;
            }
        }
    }

    protected function extraLineItemProcessing(LineItem $line, array $lineItemArray): void
    {
        return;
    }

    protected function checkEditable(Order $order): void
    {
        if (!$order->isEditable()) {
            throw new CommittedTransactionException(
                sprintf(
                    'Order %d has committed transactions and cannot be edited. Please enter a correction order.',
                    $order->getId()
                )
            );
        }
    }

    /**
     * @param Request $request
     * @return ParameterBag
     */
    protected function buildFilterParams(Request $request)
    {
        $params = new ParameterBag();

        if ($request->get('status')) {
            $params->set('status', $request->get('status'));
        }
        if ($request->get('fulfillmentPeriod')) {
            $params->set('fulfillmentPeriod', $request->get('fulfillmentPeriod'));
        }

        return $params;
    }

    protected function getEnabledTransitions(Registry $workflowRegistry, Order $order): array
    {
        $workflow = $workflowRegistry->get($order);
        $enabledTransitions = $workflow->getEnabledTransitions($order);

        return array_map(function (Transition $transition) use ($workflow) {
            $title = $workflow->getMetadataStore()->getTransitionMetadata($transition)['title'];
            return [
                'transition' => $transition->getName(),
                'title' => $title
            ];
        }, $enabledTransitions);
    }

    /**
     * @return LineItem
     */
    abstract protected function createLineItem();

    abstract protected function getEditVoter(): string;
    abstract protected function getViewVoter(): string;
}
