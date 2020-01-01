<?php

namespace App\Entity\EAV;

use App\Entity\CoreEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 * @ORM\EntityListeners({"App\Listener\AttributeListener"})
 *
 * Based on: https://github.com/Padam87/AttributeBundle
 */
abstract class Attribute
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Definition
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EAV\Definition", inversedBy="attributes")
     * @ORM\JoinColumn(name="definition_id", referencedColumnName="id", nullable=false)
     */
    private $definition;

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

    abstract public function setValue($value): Attribute;

    abstract public function getValue();

    public function isEmpty(): bool
    {
        return $this->getValue() === '' || is_null($this->getValue());
    }

    /**
     * Returns a value suitable for json responses.
     * @return string
     */
    public function getJsonValue() : ?string
    {
        return $this->getValue() ?: '';
    }

    /**
     * @param Definition $definition
     *
     * @return Attribute
     */
    public function setDefinition(Definition $definition = null)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * @return Definition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    abstract public function fixtureData();
}