<?php


namespace App\Entity\EAV;

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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Attribute
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
     * @var AttributeDefinition
     *
     * @ORM\ManyToOne(targetEntity="AttributeDefinition")
     * @ORM\JoinColumn(nullable=false)
     */
    private $definition;

    /**
     * @var AttributeValue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AttributeValue", mappedBy="attribute", orphanRemoval=true, cascade={"persist", "remove"}))
     */
    private $values;

    public function __construct(AttributeDefinition $definition)
    {
        $this->definition = $definition;
        $this->values = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDefinition()->getLabel();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDefinition(): AttributeDefinition
    {
        return $this->definition;
    }

    /**
     * @return AttributeValue[]|ArrayCollection
     */
    public function getValues()
    {
        return $this->values->map(function(AttributeValue $value) {
            return $value->getValue();
        });
    }

    public function getJsonValues(): array
    {
        $values = $this->values->map(function(AttributeValue $value) {
            return $value->getJsonValue();
        });

        return $values->getValues();
    }

    public function isEmpty(): bool
    {
        return $this->values->isEmpty();
    }

    public function hasRelationshipValue(): bool
    {
        return $this->createAttributeValue()::hasRelationship();
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $values = new ArrayCollection([$value]);
        $this->setValues($values);
    }

    public function setValues(iterable $values): void
    {
        // If it's an array, convert to ArrayCollection
        if (is_array($values)) {
            $values = new ArrayCollection($values);
        }

        $delta = 0;
        $newAttributeValues = new ArrayCollection();
        foreach ($values as $value) {

            if ($value instanceof AttributeValue) {
                $attributeValue = $value;
            } elseif (is_array($value) && isset($value['id'])) {
                $attributeValue = $this->getValueById($value['id']);
                $attributeValue->setValue($value);
            } else {
                $attributeValue = self::createNewValueFromDefinition($this->definition);
                $attributeValue->setAttribute($this);
                $attributeValue->setValue($value);
            }

            $attributeValue->setDelta($delta);
            $newAttributeValues->add($attributeValue);

            $delta++;
        }

        $this->values = $newAttributeValues;
    }

    private function getValueById(int $id): AttributeValue
    {
        if(!$this->hasRelationshipValue()) {
            throw new \Exception("Trying to find a value by ID on a non-relationship attribute");
        }

        print_r("Trying to find this value id: ".$id."\n");

        $result = $this->values->filter(function($value) use ($id) {
            print_r("Found: ".$value->getValue()->getId()."\n");
            return $value->getValue()->getId() === $id;
        });

        return $result->first();
    }

    private static function createNewValueFromDefinition(AttributeDefinition $definition): AttributeValue
    {
        return self::createNewValueFromType($definition->getType());
    }

    private static function createNewValueFromType(string $type): AttributeValue
    {
        $value = new StringAttributeValue();

        switch ($type) {
            case AttributeDefinition::TYPE_STRING:
                $value = new StringAttributeValue();
                break;
            case AttributeDefinition::TYPE_TEXT:
                $value = new TextAttributeValue();
                break;
            case AttributeDefinition::TYPE_INTEGER:
                $value = new IntegerAttributeValue();
                break;
            case AttributeDefinition::TYPE_FLOAT:
                $value = new FloatAttributeValue();
                break;
            case AttributeDefinition::TYPE_DATETIME:
                $value = new DatetimeAttributeValue();
                break;
            case AttributeDefinition::TYPE_OPTION_LIST:
                $value = new OptionListAttributeValue();
                break;
            case AttributeDefinition::TYPE_BOOLEAN:
                $value = new BooleanAttributeValue();
                break;
            case AttributeDefinition::TYPE_ADDRESS:
                $value = new AddressAttributeValue();
                break;
            case AttributeDefinition::TYPE_ZIPCODE:
                $value = new ZipCountyAttributeValue();
                break;
        }

        return $value;
    }

    public static function getTypeMetaData(string $type): array
    {
        $value = self::createNewValueFromType($type);
        return [
            'id' => $type,
            'label' => $value->getTypeLabel(),
            'hasOptions' => $value->hasOptions(),
            'displayInterfaces' => $value->getDisplayInterfaces(),
        ];
    }

    private function createAttributeValue(): AttributeValue
    {
        $value = self::createNewValueFromDefinition($this->definition);
        $value->setAttribute($this);
        return $value;
    }

    public static function getDefaultDisplayInterface(AttributeDefinition $definition): string
    {
        $value = self::createNewValueFromDefinition($definition);
        return $value->getDefaultDisplayInterface();
    }

    public function populateFixture(): void
    {
        $fixtureValue = $this->createAttributeValue()->fixtureData();
        if(is_iterable($fixtureValue)) {
            $this->setValues($fixtureValue);
        } else {
            $this->setValue($fixtureValue);
        }
    }

    public function isMultivalued(): bool
    {
        return $this->getDefinition()->isMultivalued();
    }

    public function hasOptions(): bool
    {
        $value = self::createNewValueFromDefinition($this->getDefinition());
        return $value->hasOptions();
    }

    public function getValueType(): string
    {
        return self::createNewValueFromDefinition($this->definition)->getValueType();
    }
}
