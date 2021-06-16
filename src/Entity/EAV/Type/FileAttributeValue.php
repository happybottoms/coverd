<?php

namespace App\Entity\EAV\Type;

use App\Entity\EAV\AttributeValue;
use App\Entity\EAV\EavFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FileAttributeValue
 *
 * @ORM\Entity()
 */
class FileAttributeValue extends AttributeValue
{

    /**
     * @var EavFile|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\EAV\EavFile", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="file_id")
     */
    private $value;

    public function getTypeLabel(): string
    {
        return "File Upload";
    }

    /**
     * @param EavFile|array $value
     *
     * @return AttributeValue
     */
    public function setValue($value): AttributeValue
    {
        if (is_array($value)) {
            $file = $this->getValue();
            $file->applyChangesFromArray($value);
        } else {
            $file = $value;
        }

        $this->value = $file;

        if (!($file instanceof EavFile)) {
            throw new \TypeError(sprintf("Value is not a File. It's an %s", get_class($file)));
        }

        return $this;
    }

    /**
     * @return EavFile
     */
    public function getValue()
    {

        return $this->value ?: new EavFile();
    }

    public function getValueType(): string
    {
        return EavFile::class;
    }

    public static function hasRelationship(): bool
    {
        return true;
    }

    public function isEmpty(): bool
    {
        return $this->getValue()->isEmpty();
    }

    public function getJsonValue()
    {
        return $this->getValue()->getId();
    }

    public function fixtureData(): EavFile
    {
        $file = new EavFile();
        $file->setFilename('text.txt');
        $file->setFilesize(10);
        $file->setMimeType('text/plain');
        $file->setContent('test string');

        return $file;
    }

    public function getDisplayInterfaces(): array
    {
        return [
            self::UI_FILE_UPLOAD,
        ];
    }
}
