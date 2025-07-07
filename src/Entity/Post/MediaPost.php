<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as PostOperation;
use App\ApiResource\MediaPostUploadController;
use App\Entity\AbstractMedia;
use App\Entity\Trait\UuidTrait;
use App\Entity\UrnInterface;
use App\Repository\Post\MediaPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MediaPostRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/media_posts',
            normalizationContext: ['skip_null_values' => false, 'groups' => ['media.read']],
        ),
        new Get(
            uriTemplate: '/media_posts/{id}',
            normalizationContext: ['skip_null_values' => false, 'groups' => ['media.read']],
        ),
        new PostOperation(
            controller: MediaPostUploadController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            uriTemplate: '/media_posts',
            deserialize: false,
            normalizationContext: ['groups' => ['media.read']],
            denormalizationContext: ['groups' => ['media.write']],
        ),
    ]
)]
class MediaPost extends AbstractMedia implements UrnInterface
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['media.read'])]
    private ?Post $post = null;

    public function __construct()
    {
        parent::__construct();
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

    #[Groups(['media.read'])]
    public function getUrn(): string
    {
        return '/api/media_posts/'.(string) $this->getId();
    }
}
