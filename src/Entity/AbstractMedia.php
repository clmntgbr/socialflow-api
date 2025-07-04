<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

abstract class AbstractMedia
{
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['media.read', 'media.write', 'cluster.read'])]
    protected string $filename;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['media.read', 'media.write', 'cluster.read'])]
    protected ?string $url = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['media.read', 'media.write', 'cluster.read'])]
    protected string $mimeType;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['media.read', 'media.write', 'cluster.read'])]
    protected int $size;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }
}
