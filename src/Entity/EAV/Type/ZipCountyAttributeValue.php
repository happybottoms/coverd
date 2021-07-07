<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use App\Entity\EAV\EavAddress;
use App\Entity\ZipCounty;
use Doctrine\ORM\Mapping as ORM;
use http\Exception\UnexpectedValueException;

/**
 * Class ZipCOuntyAttribute
 *
 * @ORM\Entity()
 */
class ZipCountyAttributeValue extends AttributeValue
{

    /**
     * @var ZipCounty|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ZipCounty")
     * @ORM\JoinColumn(name="zip_county_id")
     */
    private $value;

    public function getTypeLabel(): string
    {
        return "Zip/County";
    }

    /**
     * @param ZipCounty|null $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        if (empty($value)) {
            $this->value = null;
        } elseif (!($value instanceof ZipCounty)) {
            throw new \TypeError(sprintf("Value is not an Zip/County. Got %s", get_class($value)));
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return ZipCounty|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getValueType(): string
    {
        return ZipCounty::class;
    }

    public function isEmpty(): bool
    {
        return is_null($this->getValue());
    }

    public function fixtureData()
    {
        $address = new EavAddress();
        $address->setStreet1("123 Main St");
        $address->setCity("Kansas City");
        $address->setState("MO");
        $address->setCountry("USA");
        $address->setPostalCode("64110");

        return $address;
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_ZIPCODE,
            self::UI_ZIPCODE_MULTI,
        ];
    }

    public function getJsonValue()
    {
        return !is_null($this->getValue()) ? $this->getValue()->getId() : null;
    }

    /**
     * Whether this type references external entity ids (e.g. ZipCode)
     */
    public static function hasReference(): bool
    {
        return true;
    }

    public static function hasRelationship(): bool
    {
        return true;
    }

}
