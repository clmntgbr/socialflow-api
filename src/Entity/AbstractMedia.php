<?php

namespace App\Entity;

use App\Enum\MediaStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
abstract class AbstractMedia
{
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'image.name', originalName: 'image.originalName', size: 'image.size', mimeType: 'image.mimeType', dimensions: 'image.dimensions')]
    #[Assert\NotNull(groups: ['media.write'])]
    private ?File $file = null;

    #[ORM\Embedded(class: 'Vich\UploaderBundle\Entity\File')]
    protected ?EmbeddedFile $image = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $duration = null;

    #[ORM\Column(type: Types::STRING)]
    protected string $status;

    public function __construct()
    {
        $this->status = MediaStatus::CREATED->value;
        $this->image = new EmbeddedFile();
    }

    public function setFile(?File $imageFile = null): void
    {
        $this->file = $imageFile;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setImage(EmbeddedFile $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }

    #[Groups(['media.read', 'cluster.read'])]
    public function getSize(): ?int
    {
        return $this->image->getSize();
    }

    #[Groups(['media.read', 'cluster.read'])]
    public function getMimeType(): ?string
    {
        return $this->image->getMimeType();
    }

    #[Groups(['media.read', 'cluster.read'])]
    public function getName(): ?string
    {
        return $this->image->getName();
    }

    public function getOriginalName(): ?string
    {
        return $this->image->getOriginalName();
    }

    #[Groups(['media.read', 'cluster.read'])]
    public function getHeight(): ?int
    {
        return $this->image->getHeight();
    }

    public function getWidth(): ?int
    {
        return $this->image->getWidth();
    }

    #[Groups(['media.read', 'cluster.read'])]
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(MediaStatus $mediaStatus): void
    {
        $this->status = $mediaStatus->value;
    }

    public function markAsProcessing()
    {
        $this->status = MediaStatus::PROCESSING->value;
    }

    public function markAsUploaded()
    {
        $this->status = MediaStatus::UPLOADED->value;
    }

    public function markAsPublished()
    {
        $this->status = MediaStatus::PUBLISHED->value;
    }
}
