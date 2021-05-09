<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IntegerAttribute
 *
 * @ORM\Entity()
 */
class IntegerAttributeValue extends AttributeValue
{
    /**
     * @var int
     *
     * @ORM\Column(name="integer_value", type="integer")
     */
    protected $value;

    public function getTypeLabel(): string
    {
        return "Integer";
    }

    /**
     * @param int $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        $this->value = (int) $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    public function fixtureData()
    {
        return rand(0, 100);
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_NUMBER,
        ];
    }
}
