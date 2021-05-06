<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class DateTimeAttribute
 *
 * @ORM\Entity()
 */
class DatetimeAttributeValue extends AttributeValue
{
    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(name="datetime_value", type="datetime_immutable")
     */
    protected $value;

    public function getTypeLabel(): string
    {
        return "Date and Time";
    }

    /**
     * @param \DateTimeImmutable|string $value
     *
     * @return AttributeValue
     * @throws \Exception
     */
    public function setValue($value): AttributeValue
    {
        if($value === '') {
            $value = null;
        } elseif (is_string($value)) {
            $value = \DateTimeImmutable::createFromFormat(\DateTime::RFC3339, $value) ?: null;
        }

        if (is_null($value)) {
            throw new \Exception('Invalid DateTime passed to setValue');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getValueType(): string
    {
        return \DateTime::class;
    }

    public function getJsonValue()
    {
        return $this->value->format('c');
    }

    public function fixtureData()
    {
        return new \DateTimeImmutable();
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_DATETIME,
        ];
    }
}
