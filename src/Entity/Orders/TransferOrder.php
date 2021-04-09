<?php

namespace App\Entity\Orders;

use App\Entity\Order;
use App\Entity\Partner;
use App\Entity\StorageLocation;
use App\Entity\Warehouse;
use App\Exception\UserInterfaceException;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Transfer Order
 *
 * @ORM\Entity(repositoryClass="App\Repository\Orders\TransferOrderRepository")
 */
class TransferOrder extends Order
{
    public const ROLE_VIEW = "ROLE_TRANSFER_ORDER_VIEW";
    public const ROLE_EDIT = "ROLE_TRANSFER_ORDER_EDIT";

    public const WORKFLOW = [
        'type' => 'state_machine',
        'audit_trail' => [
            'enabled' => true,
        ],
        'marking_store' => [
            'type' => 'method',
            'property' => 'status',
        ],
        'supports' => [
            self::class,
        ],
        'initial_marking' => self::STATUS_CREATING,
        'places' => self::STATUSES,
        'transitions' => [
            self::TRANSITION_COMPLETE => [
                'metadata' => [
                    'title' => 'Complete'
                ],
                'from' => [
                    self::STATUS_CREATING
                ],
                'to' => self::STATUS_COMPLETED,
            ],
        ],
    ];

    /**
     * @var StorageLocation $sourceLocation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\StorageLocation")
     */
    protected $sourceLocation;

    /**
     * @var StorageLocation $targetLocation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\StorageLocation")
     */
    protected $targetLocation;

    public function __construct(StorageLocation $sourceLocation = null, StorageLocation $targetLocation = null)
    {
        parent::__construct();

        if ($sourceLocation) {
            $this->setSourceLocation($sourceLocation);
        }
        if ($targetLocation) {
            $this->setTargetLocation($targetLocation);
        }
    }

    public function getOrderTypeName(): string
    {
        return "Stock Transfer";
    }

    public function getOrderSequencePrefix(): string
    {
        return "TXFR";
    }

    /**
     * @return StorageLocation
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }

    /**
     * @param StorageLocation $sourceLocation
     */
    public function setSourceLocation(StorageLocation $sourceLocation)
    {
        $this->sourceLocation = $sourceLocation;
    }

    /**
     * @return StorageLocation
     */
    public function getTargetLocation()
    {
        return $this->targetLocation;
    }

    /**
     * @param StorageLocation $targetLocation
     */
    public function setTargetLocation($targetLocation)
    {
        $this->targetLocation = $targetLocation;
    }

    public function validate()
    {
        if ($this->getSourceLocation() instanceof Warehouse && $this->getTargetLocation() instanceof Partner) {
            throw new UserInterfaceException(
                'Transferring from a Warehouse to a Partner must be handled in a Partner Order'
            );
        }

        if ($this->getTargetLocation()->getId() == $this->getSourceLocation()->getId()) {
            throw new UserInterfaceException('Cannot transfer from a location to itself');
        }
    }
}
