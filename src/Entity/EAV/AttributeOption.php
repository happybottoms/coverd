<?php

namespace App\Entity\EAV;

use App\Entity\CoreEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Based on: https://github.com/Padam87/AttributeBundle
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_option")
 */
class AttributeOption extends CoreEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $value;

    /**
     * @var AttributeDefinition
     *
     * @ORM\ManyToOne(targetEntity="AttributeDefinition", inversedBy="options")
     * @ORM\JoinColumn(name="definition_id", referencedColumnName="id", nullable=false)
     */
    protected $definition;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param AttributeDefinition $definition
     *
     * @return $this
     */
    public function setDefinition(AttributeDefinition $definition): AttributeOption
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * @return AttributeDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
