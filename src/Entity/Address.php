<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class Address extends CoreEntity
{
    public const DEFAULT_VALIDATIONS = [
        'street1' => 'required',
        'city' => 'required',
        'state' => 'required',
        'postalCode' => 'required',
    ];

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $street1;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $street2;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $state;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $postalCode;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $country;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getStreet1()
    {
        return $this->street1;
    }

    /**
     * @param string $street1
     */
    public function setStreet1($street1)
    {
        $this->street1 = $street1;
    }

    /**
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @param string $street2
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function isValid()
    {
        return !empty($this->street1) && !empty($this->city) && !empty($this->state) && !empty($this->postalCode);
    }

    public function isEmpty(): bool
    {
        return empty($this->street1) && empty($this->city) && empty($this->state) && empty($this->postalCode);
    }
}
