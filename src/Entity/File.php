<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class File extends CoreEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $publicId;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(type="blob")
     */
    protected $content;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $filesize;

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    /**
     * @param string|null $publicId
     */
    public function setPublicId(?string $publicId): void
    {
        $this->publicId = $publicId;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return resource
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        if (preg_match('/^data:.*;base64,(.*)$/', $content, $matches)) {
            $b64Content = $matches[1];
            $content = base64_decode($b64Content);
        }
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getFilesize(): int
    {
        return $this->filesize;
    }

    /**
     * @param int $filesize
     */
    public function setFilesize(int $filesize): void
    {
        $this->filesize = $filesize;
    }

    public function isEmpty(): bool
    {
        return empty($this->content) && empty($this->filename);
    }
}
