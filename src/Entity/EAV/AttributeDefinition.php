<?php

namespace App\Entity\EAV;

use App\Entity\CoreEntity;
use App\Entity\EAV\Type\AddressAttributeValue;
use App\Entity\EAV\Type\BooleanAttributeValue;
use App\Entity\EAV\Type\DatetimeAttributeValue;
use App\Entity\EAV\Type\FloatAttributeValue;
use App\Entity\EAV\Type\IntegerAttributeValue;
use App\Entity\EAV\Type\OptionListAttributeValue;
use App\Entity\EAV\Type\StringAttributeValue;
use App\Entity\EAV\Type\TextAttributeValue;
use App\Entity\EAV\Type\ZipCountyAttributeValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Based on: https://github.com/Padam87/AttributeBundle
 *
 * @ORM\Entity(repositoryClass="App\Repository\DefinitionRepository")
 * @ORM\Table(name="attribute_definition", indexes={
 *      @ORM\Index(name="name_idx", columns={"name"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
abstract class AttributeDefinition extends CoreEntity
{
    public const TYPE_STRING = "STRING";
    public const TYPE_INTEGER = "INTEGER";
    public const TYPE_FLOAT = "FLOAT";
    public const TYPE_DATETIME = "DATETIME";
    public const TYPE_OPTION_LIST = "OPTION_LIST";
    public const TYPE_TEXT = "TEXT";
    public const TYPE_FILE = "FILE";
    public const TYPE_ADDRESS = "ADDRESS";
    public const TYPE_URL = "URL";
    public const TYPE_BOOLEAN = "BOOLEAN";
    public const TYPE_ZIPCODE = "ZIPCODE";

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Machine name for the field. Must be lowercase letters, numbers, or underscores.
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[a-z0-9_]+$/")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $helpText;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $displayInterface;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $required = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $orderIndex;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isMultivalued;

    /**
     * @var AttributeOption[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="AttributeOption",
     *     mappedBy="definition",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->isMultivalued = false;
        $this->orderIndex = 0;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->name;
    }

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
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setType(string $type): AttributeDefinition
    {
        $this->type = $type;

        // If not set, use the default interface of the attribute type.
        if (!$this->displayInterface) {
            $this->displayInterface = Attribute::getDefaultDisplayInterface($this);
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    public function setHelpText(?string $helpText): AttributeDefinition
    {
        $this->helpText = $helpText;

        return $this;
    }

    public function getDisplayInterface(): string
    {
        return $this->displayInterface;
    }

    public function setDisplayInterface(string $displayInterface): void
    {
        $this->displayInterface = $displayInterface;
    }

    /**
     * @param boolean $required
     *
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param integer $orderIndex
     *
     * @return $this
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    /**
     * @return integer
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    public function isMultivalued(): bool
    {
        return $this->getDisplayInterface() === OptionListAttributeValue::UI_CHECKBOX_GROUP ? true : !!$this->isMultivalued;
    }

    public function setIsMultivalued(bool $isMultivalued): void
    {
        $this->isMultivalued = $isMultivalued;
    }

    /**
     * @param AttributeOption $option
     *
     * @return $this
     */
    public function addOption(AttributeOption $option)
    {
        $this->options[] = $option;
        $option->setDefinition($this);

        return $this;
    }

    public function removeOption(AttributeOption $option): void
    {
        $this->options->removeElement($option);
    }

    /**
     * @return ArrayCollection
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOption(int $id): AttributeOption
    {
        return $this->options->filter(function (AttributeOption $option) use ($id) {
            return $option->getId() == $id;
        })->first();
    }

    public static function getAttributeTypes(): array
    {
        return [
            self::TYPE_DATETIME,
            self::TYPE_FLOAT,
            self::TYPE_INTEGER,
            self::TYPE_STRING,
            self::TYPE_OPTION_LIST,
            self::TYPE_TEXT,
            self::TYPE_BOOLEAN,
            self::TYPE_ADDRESS,
            self::TYPE_URL,
            self::TYPE_ZIPCODE,
        ];
    }

    public function applyChangesFromArray(array $changes): void
    {
        if (isset($changes['options'])) {
            foreach ($changes['options'] as $changedOption) {
                if (isset($changedOption['id'])) {
                    $option = $this->getOption($changedOption['id']);
                } elseif (!isset($changedOption['isDeleted']) || !$changedOption['isDeleted']) {
                    $option = new AttributeOption();
                    $this->addOption($option);
                } else {
                    continue;
                }
                $option->applyChangesFromArray($changedOption);

                if (isset($changedOption['isDeleted']) && $changedOption['isDeleted']) {
                    $this->removeOption($option);
                }
            }
            unset($changes['options']);
        }

        parent::applyChangesFromArray($changes);
    }
}
