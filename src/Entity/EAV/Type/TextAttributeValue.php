<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TextAttribute
 *
 * @ORM\Entity()
 */
class TextAttributeValue extends AttributeValue
{

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_value", type="text", nullable=true)
     */
    protected $value;

    public function getTypeLabel(): string
    {
        return "Long Text";
    }

    /**
     * @param string|null $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        if ($value === "") {
            $value = null;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function fixtureData()
    {
        return "This is a test";
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_TEXTAREA,
        ];
    }
}
