<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Abstract class for warehouses and partners
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
abstract class StorageLocation extends CoreEntity
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_INACTIVE = "INACTIVE";

    const TYPE_WAREHOUSE = "WAREHOUSE";
    const TYPE_PARTNER = "PARTNER";

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
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    protected $title;

    /**
     * @var StorageLocationAddress
     *
     * @ORM\OneToOne(targetEntity="StorageLocationAddress", mappedBy="storageLocation", cascade={"persist", "remove"})
     */
    protected $address;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @var ArrayCollection|StorageLocationContact[]
     *
     * @ORM\OneToMany(targetEntity="StorageLocationContact", mappedBy="storageLocation", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $contacts;

    /**
     * StorageLocation constructor.
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
        $this->setStatus(self::STATUS_ACTIVE);
        $this->contacts = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return StorageLocationAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param StorageLocationAddress $address
     */
    public function setAddress(StorageLocationAddress $address)
    {
        $this->address = $address;
        $address->setStorageLocation($this);
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
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return StorageLocationContact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     *
     * @param int $id
     * @return StorageLocationContact
     */
    public function getContact($id)
    {
        return $this->contacts->filter(function (StorageLocationContact $contact) use ($id) {
            return $contact->getId() == $id;
        })->first();
    }

    /**
     * @param ArrayCollection $contacts
     */
    public function setContacts(ArrayCollection $contacts)
    {
        foreach ($contacts as $contact) {
            /** @var StorageLocationContact $contact */
            $contact->setStorageLocation($this);
        }

        $this->contacts = $contacts;
    }

    public function addContact(StorageLocationContact $contact)
    {
        if(!isset($this->contacts)) $this->contacts = new ArrayCollection();
        $this->contacts->add($contact);
        $contact->setStorageLocation($this);
    }

    public function removeContact(StorageLocationContact $contact)
    {
        /** @var StorageLocationContact $found */
        $found = $this->contacts->filter(function(StorageLocationContact $c) use ($contact) {
            return $c->getId() === $contact->getId();
        })->first();

        $found->setStorageLocation(null);
        $this->contacts->removeElement($found);
    }

    /**
     * Take an associative array and apply the values to the properties of this entity
     *
     * @param array $changes
     */
    public function applyChangesFromArray($changes)
    {
        if(isset($changes['address'])) {
            if(isset($changes['address']['id'])) {
                $address = $this->getAddress();
            } else {
                $address = new StorageLocationAddress();
                $this->setAddress($address);
            }
            $address->applyChangesFromArray($changes['address']);
            unset($changes['address']);
        }

        if(isset($changes['contacts'])) {
            foreach ($changes['contacts'] as $changedContact) {
                if(isset($changedContact['id'])) {
                    $contact = $this->getContact($changedContact['id']);
                } elseif (!isset($changedContact['isDeleted']) || !$changedContact['isDeleted']) {
                    $contact = new StorageLocationContact();
                    $this->addContact($contact);
                } else {
                    continue;
                }
                $contact->applyChangesFromArray($changedContact);

                if((isset($changedContact['isDeleted']) && $changedContact['isDeleted']) || !$contact->isValid()) {
                    $this->removeContact($contact);
                }
            }
            unset($changes['contacts']);
        }

        parent::applyChangesFromArray($changes);
    }
}