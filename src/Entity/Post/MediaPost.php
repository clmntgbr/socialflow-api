<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as PostOperation;
use App\ApiResource\MediaPostUploadController;
use App\Entity\AbstractMedia;
use App\Entity\Trait\UuidTrait;
use App\Repository\Post\MediaPostRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MediaPostRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/medias/post',
            normalizationContext: ['skip_null_values' => false, 'groups' => ['media.read']],
        ),
        new Get(
            uriTemplate: '/medias/post/{id}',
            normalizationContext: ['skip_null_values' => false, 'groups' => ['media.read']],
        ),
        new PostOperation(
            controller: MediaPostUploadController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            uriTemplate: '/medias/post',
            deserialize: false,
            normalizationContext: ['groups' => ['media.read']],
            denormalizationContext: ['groups' => ['media.write']],
        ),
    ]
)]
#[Vich\Uploadable]
class MediaPost
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $post = null;

    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'image.name', size: 'image.size')]
    #[Assert\NotNull(groups: ['media.write'])]
    private ?File $file = null;

    #[ORM\Embedded(class: 'Vich\UploaderBundle\Entity\File')]
    private ?EmbeddedFile $image = null;

    public function __construct()
    {
        $this->image = new EmbeddedFile();
        $this->id = Uuid::v4();
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
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
}
