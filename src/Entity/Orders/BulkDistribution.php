<?php

namespace App\Entity\Orders;

use App\Entity\Client;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Partner;
use Doctrine\ORM\Mapping as ORM;
use Moment\Moment;

/**
 * Class Partner Distribution
 *
 * @ORM\Entity(repositoryClass="App\Repository\Orders\BulkDistributionOrderRepository")
 */
class BulkDistribution extends Order
{
    public const STATUS_PENDING = 'PENDING';

    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CREATING,
        self::STATUS_PENDING,
    ];

    public const ROLE_VIEW = "ROLE_DISTRIBUTION_ORDER_VIEW";
    public const ROLE_EDIT = "ROLE_DISTRIBUTION_ORDER_EDIT";

    /**
     * @var Partner $partner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $partner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=false)
     */
    protected $distributionPeriod;

    /**
     * Portal Order ID this bulk distribution came from
     *
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $portalOrderId;

    public function __construct(Partner $partner = null)
    {
        parent::__construct();

        $this->setStatus(self::STATUS_COMPLETED);

        if ($partner) {
            $this->setPartner($partner);
        }
    }

    public function getOrderTypeName(): string
    {
        return "Partner Distribution";
    }

    public function getOrderSequencePrefix(): string
    {
        return "DIST";
    }

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param Partner $partner
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function isComplete(): bool
    {
        return $this->getStatus() === self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->getStatus() !== self::STATUS_COMPLETED;
    }

    /**
     * @return \DateTime
     */
    public function getDistributionPeriod(): \DateTime
    {
        return $this->distributionPeriod;
    }

    /**
     * @param \DateTime|string $distributionPeriod
     */
    public function setDistributionPeriod($distributionPeriod)
    {
        if (is_string($distributionPeriod)) {
            $period = new Moment($distributionPeriod);
        } else {
            //TODO: This can be simplified with a ::fromDateTime in the next release of Moment.php
            $period = new Moment($distributionPeriod->format('U'));
            $period->setTimezone($distributionPeriod->getTimezone()->getName());
        }

        $period->startOf('month');

        $this->distributionPeriod = $period;
    }

    public function addMissingClients()
    {
        $clients = $this->getPartner()->getClients();
        $lineItems = $this->lineItems;

        $missingClients = $clients->filter(function (Client $client) use ($lineItems) {
            return !$lineItems->exists(function ($key, BulkDistributionLineItem $lineItem) use ($client) {
                return $lineItem->getClient()->getId() == $client->getId();
            });
        });

        foreach ($missingClients as $client) {
            $line = new BulkDistributionLineItem();
            $line->setClient($client);
            $this->addLineItem($line);
        }
    }

    /**
     * @return int
     */
    public function getPortalOrderId(): ?int
    {
        return $this->portalOrderId;
    }

    /**
     * @param int $portalOrderId
     */
    public function setPortalOrderId(?int $portalOrderId)
    {
        $this->portalOrderId = $portalOrderId;
    }

    public function removeLineItem(LineItem $lineItem): void
    {
        /** @var BulkDistributionLineItem $lineItem */
        //If the line item has an ID then look it up by that, otherwise look it up by product
        if ($lineItem->getId()) {
            $found = $this->lineItems->filter(function (LineItem $line) use ($lineItem) {
                return $line->getId() == $lineItem->getId();
            })->first();
        } else {
            $found = $this->lineItems->filter(function (BulkDistributionLineItem $line) use ($lineItem) {
                return $line->getClient()->getId() == $lineItem->getClient()->getId();
            })->first();
        }

        if ($found) {
            $this->lineItems->removeElement($found);
        }
    }
}
