<?php

namespace App\Entity\Orders;

use App\Entity\Order;
use App\Entity\Warehouse;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Merchandise Order
 *
 * @ORM\Entity(repositoryClass="App\Repository\Orders\MerchandiseOrderRepository")
 */
class MerchandiseOrder extends Order
{
    public const ROLE_VIEW = "ROLE_MERCHANDISE_ORDER_VIEW";
    public const ROLE_EDIT = "ROLE_MERCHANDISE_ORDER_EDIT";

    /**
     * @var Warehouse $warehouse
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Warehouse")
     */
    protected $warehouse;

    public function __construct(Warehouse $warehouse = null)
    {
        parent::__construct();

        $this->setStatus(self::STATUS_COMPLETED);

        if ($warehouse) {
            $this->setWarehouse($warehouse);
        }
    }

    public function getOrderTypeName(): string
    {
        return "Merchandise Order";
    }

    public function getOrderSequencePrefix(): string
    {
        return "MRCH";
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
        return $this->getStatus() === self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->getStatus() !== self::STATUS_COMPLETED;
    }
}
