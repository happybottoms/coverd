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
    private $attributeOption;

    public function getTypeLabel(): string
    {
        return "Option List";
    }

    /**
     * @param AttributeOption|integer|string $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        $orig_value = $value;
        if (!$value) {
            $this->attributeOption = null;
            return $this;
        } elseif (is_numeric($value)) {
            $value = $this->getDefinition()->getOptions()->filter(function (AttributeOption $option) use ($value) {
                return $option->getId() == $value;
            })->first();
        } elseif (is_string($value)) {
            $value = $this->getDefinition()->getOptions()->filter(function (AttributeOption $option) use ($value) {
                return $option->getValue() == $value;
            })->first();
        }

        if (!$value) {
            throw new \Exception(sprintf("couldn't find: %s for %s", $orig_value, $this->getDefinition()->getName()));
        }

        $this->attributeOption = $value;

        return $this;
    }

    /**
     * @return AttributeOption|null
     */
    public function getValue()
    {
        return $this->attributeOption;
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
}
