<?php

namespace App\Entity\Orders;

use App\Entity\Order;
use App\Entity\Supplier;
use App\Entity\SupplierAddress;
use App\Entity\Warehouse;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Supply Order
 *
 * @ORM\Entity(repositoryClass="App\Repository\Orders\SupplyOrderRepository")
 * @Gedmo\Loggable()
 *
 */
class SupplyOrder extends Order
{
    public const STATUS_ORDERED = 'ORDERED';
    public const STATUS_RECEIVED = 'RECEIVED';

    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CREATING,
        self::STATUS_ORDERED,
        self::STATUS_RECEIVED,
    ];

    public const ROLE_VIEW = "ROLE_SUPPLY_ORDER_VIEW";
    public const ROLE_EDIT = "ROLE_SUPPLY_ORDER_EDIT";

    /**
     * @var Supplier $supplier
     *
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", inversedBy="supplyOrders")
     */
    protected $supplier;

    /**
     * @var SupplierAddress $supplierAddress
     *
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="App\Entity\SupplierAddress")
     */
    protected $supplierAddress;

    /**
     * @var Warehouse $warehouse
     *
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="App\Entity\Warehouse")
     */
    protected $warehouse;

    /**
     * @ORM\Column(name="received_at", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $receivedAt;

    public function __construct(Supplier $supplier = null, Warehouse $warehouse = null)
    {
        parent::__construct();

        $this->setStatus(self::STATUS_ORDERED);

        if ($supplier) {
            $this->setSupplier($supplier);
        }
        if ($warehouse) {
            $this->setWarehouse($warehouse);
        }
    }

    public function getOrderTypeName(): string
    {
        return "Supply Order";
    }

    public function getOrderSequencePrefix(): string
    {
        return "SPLY";
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param Supplier $supplier
     */
    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return SupplierAddress
     */
    public function getSupplierAddress()
    {
        return $this->supplierAddress;
    }

    /**
     * @param SupplierAddress $supplierAddress
     */
    public function setSupplierAddress(SupplierAddress $supplierAddress)
    {
        $this->supplierAddress = $supplierAddress;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse $warehouse
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function isComplete(): bool
    {
        return $this->getStatus() === self::STATUS_RECEIVED;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->getStatus() !== self::STATUS_RECEIVED;
    }

    /**
     * @return \DateTime
     */
    public function getReceivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * @param \DateTime $receivedAt
     */
    public function setReceivedAt(\DateTime $receivedAt = null)
    {
        $this->receivedAt = $receivedAt;
    }

    public function completeOrder()
    {
        parent::completeOrder();

        if (!$this->getReceivedAt()) {
            $this->setReceivedAt(new \DateTime());
        }
    }

    public function isDeletable()
    {
        return true;
    }
}
