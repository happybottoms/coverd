<?php

namespace App\Entity\Orders;

use App\Entity\Client;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Partner;
use App\Entity\Warehouse;
use App\Helpers\Bag;
use App\Helpers\Pack;
use Doctrine\ORM\Mapping as ORM;
use Moment\Moment;

/**
 * Class Partner Order
 *
 * @ORM\Entity(repositoryClass="App\Repository\Orders\PartnerOrderRepository")
 */
class PartnerOrder extends Order
{
    public const STATUS_COMPLETED = 'SHIPPED';
    public const STATUS_IN_PROCESS = 'IN_PROCESS';
    public const STATUS_SUBMITTED = 'SUBMITTED';

    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CREATING,
        self::STATUS_IN_PROCESS,
        self::STATUS_SUBMITTED,
    ];

    public const ROLE_VIEW = 'ROLE_PARTNER_ORDER_VIEW';
    public const ROLE_EDIT = 'ROLE_PARTNER_ORDER_EDIT';

    /**
     * @var Partner $partner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $partner;

    /**
     * @var Warehouse $warehouse
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Warehouse")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $warehouse;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=false, options={"model_timezone":"UTC"})
     */
    protected $orderPeriod;

    /**
     * @var ?integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $portalOrderId;

    public function __construct(Partner $partner = null, Warehouse $warehouse = null)
    {
        parent::__construct();

        $this->setStatus(self::STATUS_IN_PROCESS);

        if ($partner) {
            $this->setPartner($partner);
        }
        if ($warehouse) {
            $this->setWarehouse($warehouse);
        }
    }

    public function getOrderTypeName(): string
    {
        return "Partner Order";
    }

    public function getOrderSequencePrefix(): string
    {
        return "PTNR";
    }

    public function getPartner(): Partner
    {
        return $this->partner;
    }

    public function setPartner(Partner $partner): void
    {
        $this->partner = $partner;
    }

    public function getWarehouse(): Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function isComplete(): bool
    {
        return $this->getStatus() === self::STATUS_COMPLETED;
    }

    public function isEditable(): bool
    {
        return $this->getStatus() !== self::STATUS_COMPLETED;
    }

    public function addMissingClients(): void
    {
        $clients = $this->getPartner()->getClients();
        $lineItems = $this->lineItems;

        $missingClients = $clients->filter(function (Client $client) use ($lineItems) {
            return !$lineItems
                ->filter(function (PartnerOrderLineItem $line) {
                    return $line->isClientLineItem();
                })
                ->exists(function ($key, PartnerOrderLineItem $lineItem) use ($client) {
                    return $lineItem->getClient()->getId() === $client->getId();
                });
        });

        foreach ($missingClients as $client) {
            $line = new PartnerOrderLineItem();
            $line->setClient($client);
            $this->addLineItem($line);
        }
    }

    public function removeLineItem(LineItem $lineItem): void
    {
        /** @var PartnerOrderLineItem $lineItem */
        //If the line item has an ID then look it up by that, otherwise look it up by product
        if ($lineItem->getId()) {
            $found = $this->lineItems->filter(function (LineItem $line) use ($lineItem) {
                return $line->getId() == $lineItem->getId();
            })->first();
        } else {
            if ($lineItem->isClientLineItem()) {
                $found = $this->lineItems->filter(function (PartnerOrderLineItem $line) use ($lineItem) {
                    if ($line->isClientLineItem()) {
                        return $line->getClient()->getId() == $lineItem->getClient()->getId();
                    }
                    return false;
                })->first();
            } else {
                $found = $this->lineItems->filter(function (LineItem $line) use ($lineItem) {
                    return $line->getProduct()->getId() == $lineItem->getProduct()->getId();
                })->first();
            }
        }

        if ($found) {
            $this->lineItems->removeElement($found);
        }
    }

    public function buildBags(): array
    {
        $bags = [];
        $remainderBags = [];

        $bag = new Bag();
        $currentRemainderBag = null;

        foreach ($this->getLineItems() as $lineItem) {
            $product = $lineItem->getProduct();
            $quantity = $lineItem->getQuantity();
            if (empty($quantity)) {
                continue;
            }
            while ($quantity > 0) {
                $pack = new Pack($product, $this->getPartner()->getPartnerType());
                if (!$bag->addPack($pack)) {
                    if (!$bag->isEmpty()) {
                        $bags[] = $bag;
                    }
                    $bag = new Bag();
                    $bag->addPack($pack);
                }
                $quantity -= $pack->quantity;
            }

            // Done with this product.
            //If the current bag isn't full move all the packs to the current remainder bag we have going.
            if (!$bag->isFull()) {
                if (count($remainderBags) == 0 && !$currentRemainderBag) {
                    $currentRemainderBag = new Bag();
                }
                foreach ($bag->packs as $pack) {
                    if (!$currentRemainderBag->addPack($pack)) {
                        $remainderBags[] = $currentRemainderBag;
                        $currentRemainderBag = new Bag();
                        $currentRemainderBag->addPack($pack);
                    }
                }
                // Reset the bag since we moved all the packs out.
                $bag = new Bag();
            }
        }

        if (!$bag->isEmpty()) {
            $bags[] = $bag;
        }

        if ($currentRemainderBag) {
            $remainderBags[] = $currentRemainderBag;
        }
        return array_merge($bags, $remainderBags);
    }

    /**
     * @return \DateTime
     */
    public function getOrderPeriod(): \DateTime
    {
        return $this->orderPeriod;
    }

    /**
     * @param \DateTime|string $orderPeriod
     */
    public function setOrderPeriod($orderPeriod): void
    {
        if (is_string($orderPeriod)) {
            $period = new Moment($orderPeriod);
        } else {
            //TODO: This can be simplified with a ::fromDateTime in the next release of Moment.php
            $period = new Moment($orderPeriod->format('U'));
            $period->setTimezone($orderPeriod->getTimezone()->getName());
        }

        $period->startOf('month');

        $this->orderPeriod = $period;
    }

    public function getPortalOrderId(): ?int
    {
        return $this->portalOrderId;
    }

    public function setPortalOrderId(?int $portalOrderId)
    {
        $this->portalOrderId = $portalOrderId;
    }
}
