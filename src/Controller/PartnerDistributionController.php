<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\LineItem;
use App\Entity\Orders\BulkDistribution;
use App\Entity\Orders\BulkDistributionLineItem;
use App\Entity\Partner;
use App\Entity\User;
use App\Exception\UserInterfaceException;
use App\Transformers\BulkDistributionLineItemTransformer;
use App\Transformers\BulkDistributionTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PartnerOrderController
 * @package App\Controller
 *
 * @Route(path="/api/orders/distribution")
 */
class PartnerDistributionController extends OrderController
{
    protected $defaultEntityName = BulkDistribution::class;

    /**
     * @return BulkDistributionLineItem
     */
    protected function createLineItem()
    {
        return new BulkDistributionLineItem();
    }


    /**
     * Save a new partner distribution
     *
     * @Route(path="", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $params = $this->getParams($request);

        $order = new BulkDistribution();

        if ($params['partner']['id']) {
            $newPartner = $this->getEm()->find(Partner::class, $params['partner']['id']);
            $order->setPartner($newPartner);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);

        // Check if the partner has already submitted an order for the specified month.
        $existingOrder = $this->getRepository()->findOneBy([
            'partner' => $order->getPartner(),
            'distributionPeriod' => $order->getDistributionPeriod(),
        ]);

        if ($existingOrder) {
            throw new UserInterfaceException(
                sprintf(
                    '%s has already reported distributions for %s.',
                    $order->getPartner()->getTitle(),
                    $order->getDistributionPeriod()->format('M Y')
                )
            );
        }

        $this->getEm()->persist($order);
        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"}, defaults={"_format": "json"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \App\Exception\CommittedTransactionException
     */
    public function update(Request $request, $id)
    {
        $params = $this->getParams($request);
        /** @var BulkDistribution $order */
        $order = $this->getOrder($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);

        if (!$request->get('reason')) {
            $this->checkEditable($order);
        }

        if ($params['partner']['id']) {
            $newPartner = $this->getEm()->find(Partner::class, $params['partner']['id']);
            $order->setPartner($newPartner);
        }

        $params['lineItems'] = array_filter($params['lineItems'], function ($lineItem) {
            return isset($lineItem['client']['id']);
        });

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * @param BulkDistributionLineItem $lineItem
     * @param array $lineItemArray
     */
    protected function extraLineItemProcessing(LineItem $lineItem, array $lineItemArray)
    {
        $client = $this->getEm()->getRepository(Client::class)->findOneByPublicId($lineItemArray['client']['id']);
        $lineItem->setClient($client);
    }

    /**
     * Generate line items for each client for use in the edit view
     *
     * @Route(path="/new-line-items-for-partner/{id<\d+>}", methods={"GET"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function createLineItemsForPartner(Request $request, int $id)
    {
        /** @var Partner $partner */
        $partner = $this->getEm()->getRepository(Partner::class)->find($id);
        $clients = $partner->getClients()->getValues();

        // Make a dummy order since Line Items must have an order
        $order = new BulkDistribution($partner);

        $lineItems = array_map(function ($client) use ($order) {
            $line = new BulkDistributionLineItem();
            $line->setClient($client);
            $order->addLineItem($line);
            return $line;
        }, $clients);

        return $this->serialize($request, $lineItems, new BulkDistributionLineItemTransformer());
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/partner-can-order", methods={"GET"})
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function partnerCanOrder(Request $request)
    {
        $params = $this->getParams($request);
        $partner = $this->getRepository(Partner::class)->find($params['partnerId']);
        $distributionPeriod = new \DateTime($params['distributionPeriod']);

        $existingOrder = $this->getRepository()->findOneBy([
            'partner' => $partner,
            'distributionPeriod' => $distributionPeriod,
        ]);

        return $this->meta($existingOrder === null);
    }

    protected function buildFilterParams(Request $request)
    {
        $params = parent::buildFilterParams($request);

        if ($request->get('orderPeriod')) {
            $params->set('orderPeriod', new \DateTime($request->get('orderPeriod')));
        }
        if ($request->get('partner')) {
            $params->set('partner', $this->getRepository(Partner::class)->find($request->get('partner')));
        }

        /** @var User $user */
        $user = $this->getUser();

        // If the user isn't an admin,
        if (!$user->hasRole(BulkDistribution::ROLE_VIEW_ALL)) {
            $params->set('partner', $user->getActivePartner());
        }

        return $params;
    }

    protected function getDefaultTransformer()
    {
        return new BulkDistributionTransformer();
    }
}
