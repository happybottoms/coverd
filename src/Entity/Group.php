<?php

namespace App\Entity;

use App\Entity\Orders\AdjustmentOrder;
use App\Entity\Orders\BulkDistribution;
use App\Entity\Orders\MerchandiseOrder;
use App\Entity\Orders\PartnerOrder;
use App\Entity\Orders\SupplyOrder;
use App\Entity\Orders\TransferOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User Groups
 *
 * @ORM\Entity()
 * @ORM\Table(name="groups")
 */
class Group extends CoreEntity
{
    public const AVAILABLE_ROLES = [
        User::ROLE_ADMIN,
        User::ROLE_PARTNER,

        Order::ROLE_MANAGE_OWN,
        Order::ROLE_VIEW_ALL,
        Order::ROLE_EDIT_ALL,

        Supplier::ROLE_VIEW,
        Supplier::ROLE_EDIT,

        Warehouse::ROLE_VIEW,
        Warehouse::ROLE_EDIT,

        Partner::ROLE_VIEW_ALL,
        Partner::ROLE_EDIT_ALL,
        Partner::ROLE_MANAGE_OWN,

        Client::ROLE_VIEW_ALL,
        Client::ROLE_EDIT_ALL,
        Client::ROLE_MANAGE_OWN,

        Product::ROLE_VIEW,
        Product::ROLE_EDIT,

        User::ROLE_VIEW,
        User::ROLE_EDIT,

        PartnerOrder::ROLE_EDIT,
        PartnerOrder::ROLE_VIEW,

        SupplyOrder::ROLE_EDIT,
        SupplyOrder::ROLE_VIEW,

        BulkDistribution::ROLE_VIEW,
        BulkDistribution::ROLE_EDIT,

        MerchandiseOrder::ROLE_EDIT,
        MerchandiseOrder::ROLE_VIEW,

        TransferOrder::ROLE_VIEW,
        TransferOrder::ROLE_EDIT,

        AdjustmentOrder::ROLE_EDIT,
        AdjustmentOrder::ROLE_VIEW,
    ];

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
     *
     * @var User $users
     */
    protected $users;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotBlank
     *
     * @var array
     */
    protected $roles;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function addRole(string $role)
    {
        $roles = new ArrayCollection($this->roles);
        if (!$roles->contains($role)) {
            $roles->add($role);
        }
        $this->setRoles($roles->toArray());
    }

    public function removeRole(string $role)
    {
        $roles = new ArrayCollection($this->roles);
        $roles->removeElement($role);
        $this->setRoles($roles->toArray());
    }
}
