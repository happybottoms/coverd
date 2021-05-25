<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use App\Entity\EAV\AttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OptionListAttribute
 *
 * @ORM\Entity()
 */
class OptionListAttributeValue extends AttributeValue
{

    /**
     * @var AttributeOption|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EAV\AttributeOption")
     */
    private $value;

    public function getTypeLabel(): string
    {
        return "Option List";
    }

    /**
     * @param AttributeOption|integer $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        if (empty($value)) {
            $this->value = null;
        } elseif (is_numeric($value)) {
            $value = $this->getDefinition()->getOptions()->filter(function (AttributeOption $option) use ($value) {
                return $option->getId() == $value;
            })->first();
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return AttributeOption|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getValueType(): string
    {
        return AttributeOption::class;
    }

    public function isEmpty(): bool
    {
        return !$this->getValue();
    }

    public function getJsonValue()
    {
        return $this->getValue() !== null ? $this->getValue()->getId() : null;
    }

    public function fixtureData()
    {
        $options = $this->getDefinition()->getOptions()->getValues();
        return $options[array_rand($options)];
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_SELECT_SINGLE,
            self::UI_SELECT_MULTI,
            self::UI_RADIO,
            self::UI_CHECKBOX_GROUP,
        ];
    }

    public static function hasOptions(): bool
    {
        return true;
    }

    public static function hasRelationship(): bool
    {
        return true;
    }
}
