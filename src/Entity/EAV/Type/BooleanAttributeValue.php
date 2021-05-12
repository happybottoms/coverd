<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BooleanAttribute
 *
 * @ORM\Entity()
 */
class BooleanAttributeValue extends AttributeValue
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="boolean_value", type="boolean")
     */
    protected $value;

    public function getTypeLabel(): string
    {
        return "Boolean (yes/no)";
    }

    /**
     * @param boolean $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getValue()
    {
        return $this->value;
    }

    public function fixtureData()
    {
        return rand(1, 10) > 5;
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_TOGGLE,
            self::UI_YES_NO_RADIO,
        ];
    }

    public function getJsonValue()
    {
        return !!$this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }
}
