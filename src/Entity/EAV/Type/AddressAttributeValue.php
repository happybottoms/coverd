<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\Attribute;
use App\Entity\EAV\AttributeValue;
use App\Entity\EAV\EavAddress;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AddressAttribute
 *
 * @ORM\Entity()
 */
class AddressAttributeValue extends AttributeValue
{

    /**
     * @var EavAddress|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\EAV\EavAddress", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="address_id")
     */
    private $value;

    public function __construct(Attribute $attribute = null)
    {
        parent::__construct($attribute);

        $this->value = new EavAddress();
    }

    public function getTypeLabel(): string
    {
        return "Address";
    }

    /**
     * @param EavAddress|array $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        if (is_array($value)) {
            $address = $this->getValue();
            $address->applyChangesFromArray($value);
        } else {
            $address = $value;
        }

        $this->value = $address;

        if (!($address instanceof EavAddress)) {
            throw new \TypeError(sprintf("Value is not an Address. It's an %s", get_class($address)));
        }

        return $this;
    }

    /**
     * @return EavAddress
     */
    public function getValue()
    {

        return $this->value ?: new EavAddress();
    }

    public function getValueType(): string
    {
        return EavAddress::class;
    }

    public static function hasRelationship(): bool
    {
        return true;
    }

    public function isEmpty(): bool
    {
        return $this->getValue()->isEmpty();
    }

    public function getJsonValue()
    {
        return $this->getValue()->getId();
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
            self::UI_ADDRESS,
        ];
    }
}
