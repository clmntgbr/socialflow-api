<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as PostOperation;
use App\Entity\AbstractMedia;
use App\Entity\Trait\UuidTrait;
use App\Repository\Post\MediaPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
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
            uriTemplate: '/medias/post',
            normalizationContext: ['groups' => ['media.read']],
            denormalizationContext: ['groups' => ['media.write']],
        ),
    ]
)]
class MediaPost extends AbstractMedia
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $post = null;

    public function __construct()
    {
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
}
