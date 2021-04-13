<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Order
 *
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\EntityListeners({"App\Listener\OrderListener"})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * Doing the following because purging broke the DB since table names aren't back-ticked
 * @ORM\Table("`order`")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
abstract class Order extends CoreEntity
{
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CREATING = 'CREATING';

    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CREATING,
    ];

    public const TRANSITION_COMPLETE = 'COMPLETE';

    public const ROLE_VIEW_ALL = "ROLE_ORDER_VIEW_ALL";
    public const ROLE_EDIT_ALL = "ROLE_ORDER_EDIT_ALL";
    public const ROLE_MANAGE_OWN = "ROLE_ORDER_MANAGE_OWN";

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=11, nullable=false)
     */
    protected $sequenceNo;

    /**
     * @var ArrayCollection|LineItem[]
     *
     * @ORM\OneToMany(targetEntity="LineItem", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $lineItems;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     */
    protected $status;


    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
        $this->setStatus(self::STATUS_COMPLETED);
    }

    /**
     * Human readable name of the order type
     *
     * @return string
     */
    abstract public function getOrderTypeName(): string;

    abstract public function getOrderSequencePrefix(): string;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getLineItems()
    {
        return $this->lineItems;
    }

    public function getSequenceNo(): ?string
    {
        return $this->sequenceNo;
    }

    public function setSequenceNo(int $sequenceNo): string
    {
        $padded = str_pad($sequenceNo, 6, "0", STR_PAD_LEFT);
        $this->sequenceNo = sprintf("%s-%s", $this->getOrderSequencePrefix(), $padded);

        return $this->sequenceNo;
    }

    /**
     * @param $id
     * @return LineItem|bool
     */
    public function getLineItem($id)
    {
        $found = $this->lineItems->filter(function (LineItem $line) use ($id) {
            return $id == $line->getId();
        })->first();

        return $found ?: false;
    }

    public function addLineItem(LineItem $lineItem)
    {
        $this->lineItems->add($lineItem);
        $lineItem->setOrder($this);
    }

    public function removeLineItem(LineItem $lineItem)
    {
        //If the line item has an ID then look it up by that, otherwise look it up by product
        if ($lineItem->getId()) {
            $found = $this->lineItems->filter(function (LineItem $line) use ($lineItem) {
                return $line->getId() == $lineItem->getId();
            })->first();
        } else {
            $found = $this->lineItems->filter(function (LineItem $line) use ($lineItem) {
                return $line->getProduct()->getId() == $lineItem->getProduct()->getId();
            })->first();
        }

        if ($found) {
            $this->lineItems->removeElement($found);
        }
    }

    public function generateTransactions()
    {
        $this->getLineItems()->map(function (LineItem $lineItem) {
            $lineItem->generateTransactions();
        });
    }

    public function updateTransactions()
    {
        $this->getLineItems()->map(function (LineItem $lineItem) {
            $lineItem->updateTransactions();
        });
    }

    public function commitTransactions()
    {
        $this->getLineItems()->map(function (LineItem $lineItem) {
            $lineItem->commitTransactions();
        });
    }

    public function completeOrder()
    {
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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
     * @return bool
     */
    public function isDeletable()
    {
        return $this->isEditable();
    }

    /**
     * @return LineItem[]
     */
    public function getAggregateLineItems(): array
    {
        /** @var LineItem[] $aggLines */
        $aggLines = [];
        foreach ($this->lineItems as $lineItem) {
            $productId = $lineItem->getProduct()->getId();
            if (!key_exists($productId, $aggLines)) {
                $aggLines[$productId] = clone $lineItem;
            } else {
                $aggLines[$productId]->setQuantity($aggLines[$productId]->getQuantity() + $lineItem->getQuantity());
            }
        }

        return $aggLines;
    }
}
