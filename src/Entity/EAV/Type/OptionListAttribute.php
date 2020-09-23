<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\Attribute;
use App\Entity\EAV\Option;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OptionListAttribute
 *
 * @ORM\Entity()
 */
class OptionListAttribute extends Attribute
{

    /**
     * @var Option
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EAV\Option", cascade={"persist"})
     */
    private $value;

    public function getTypeLabel(): string
    {
        return "Option List";
    }

    /**
     * @param ?Option $value
     *
     * @return Attribute
     */
    public function setValue($value): Attribute
    {
        if (!$value instanceof Option && !empty($value)) {
            $value = $this->getDefinition()->getOptions()->filter(function (Option $option) use ($value) {
                return $option->getId() == $value;
            })->first();
        } elseif (empty($value) || $value === false) {
            $value = null;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return Option
     */
    public function getValue()
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return !$this->getValue();
    }

    public function getJsonValue()
    {
        return $this->getValue() ? $this->getValue()->getId() : null;
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
            self::UI_RADIO,
        ];
    }

    public function hasOptions(): bool
    {
        return true;
    }
}
