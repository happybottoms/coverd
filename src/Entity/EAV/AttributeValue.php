<?php

namespace App\Entity\EAV;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 *
 * Based on: https://github.com/Padam87/AttributeBundle
 */
abstract class AttributeValue
{
    public const UI_TEXT = "TEXT";
    public const UI_NUMBER = "NUMBER";
    public const UI_TEXTAREA = "TEXTAREA";
    public const UI_DATETIME = "DATETIME";
    public const UI_SELECT_SINGLE = "SELECT_SINGLE";
    public const UI_SELECT_MULTI = "SELECT_MULTI";
    public const UI_FILE_UPLOAD = "FILE_UPLOAD";
    public const UI_RADIO = "RADIO";
    public const UI_CHECKBOX_GROUP = "CHECKBOX_GROUP";
    public const UI_TOGGLE = "TOGGLE";
    public const UI_YES_NO_RADIO = "YES_NO_RADIO";
    public const UI_ADDRESS = "ADDRESS";
    public const UI_URL = "URL";
    public const UI_ZIPCODE = "ZIPCODE";

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $delta;

    /**
     * @var Attribute|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EAV\Attribute", inversedBy="values")
     */
    private $attribute;

    public function __construct(Attribute $attribute = null)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getDefinition()->getLabel();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a human readable name for this attribute type.
     */
    abstract public function getTypeLabel(): string;

    /**
     * @param mixed $value
     */
    abstract public function setValue($value): AttributeValue;

    /**
     * @return mixed
     */
    abstract public function getValue();

    /**
     * @return int
     */
    public function getDelta(): int
    {
        return $this->delta;
    }

    /**
     * @param int $delta
     */
    public function setDelta(int $delta): void
    {
        $this->delta = $delta;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function getValueType(): string
    {
        return 'string';
    }

    abstract public function getDisplayInterfaces(): array;

    /**
     * By default this will just get the first interface in the list. Override as necessary.
     */
    public function getDefaultDisplayInterface(): string
    {
        $interfaces = $this->getDisplayInterfaces();
        return reset($interfaces);
    }

    /**
     * Whether this type supports list options (dropdown, radio, checkboxes, etc)
     */
    public static function hasOptions(): bool
    {
        return false;
    }

    /**
     * Whether this type references another entity as it's value
     */
    public static function hasRelationship(): bool
    {
        return false;
    }

    public function isEmpty(): bool
    {
        return $this->getValue() === '' || is_null($this->getValue());
    }

    /**
     * Returns a value suitable for json responses.
     * @return mixed
     */
    public function getJsonValue()
    {
        return $this->getValue() ?: '';
    }

    public function getDefinition(): AttributeDefinition
    {
        if(!$this->attribute) {
            throw new \Exception("Attempting to get the definition of a value without and attribute.");
        }

        return $this->attribute->getDefinition();
    }

    /**
     * @return mixed
     */
    abstract public function fixtureData();
}
