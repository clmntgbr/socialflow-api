<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\UuidTrait;
use App\Enum\PostStatus;
use App\Repository\Post\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'linkedin' => 'LinkedinPost',
    'twitter' => 'TwitterPost',
    'facebook' => 'FacebookPost',
    'youtube' => 'YoutubePost',
    'thread' => 'ThreadPost',
    'instagram' => 'InstagramPost',
])]
#[ApiResource(
    operations: []
)]
class Post implements PostInterface
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['cluster.read', 'post.read'])]
    private ?string $postId = null;

    #[ORM\Column(name: '`order`', type: Types::INTEGER)]
    #[Groups(['cluster.read', 'post.read', 'post.write'])]
    private int $order;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['cluster.read', 'post.read', 'post.write'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['cluster.read', 'post.read', 'post.write'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['cluster.read', 'post.read'])]
    private string $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cluster.read', 'post.read'])]
    private ?\DateTime $postedAt = null;

    #[ORM\ManyToOne(targetEntity: Cluster::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post.write'])]
    private Cluster $cluster;

    public function __construct()
    {
        $this->order = 1;
        $this->id = Uuid::v4();
        $this->status = PostStatus::DRAFT->value;
    }

    public function getType(): string
    {
        return '';
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::PUBLISHED->value && null !== $this->postId;
    }

    public function isDraft(): bool
    {
        return $this->status === PostStatus::DRAFT->value;
    }

    public function isProgrammed(): bool
    {
        return $this->status === PostStatus::PROGRAMMED->value;
    }

    public function isError(): bool
    {
        return $this->status === PostStatus::ERROR->value;
    }

    public function setPublished(string $postId): void
    {
        $this->postId = $postId;
        $this->status = PostStatus::PUBLISHED->value;
        $this->postedAt = new \DateTime();
    }

    public function setFailed(): void
    {
        $this->postId = null;
        $this->status = PostStatus::ERROR->value;
        $this->postedAt = null;
    }

    public function getPostId(): ?string
    {
        return $this->postId;
    }

    public function setPostId(?string $postId): static
    {
        $this->postId = $postId;

        return $this;
    }

    public function getPostedAt(): ?\DateTime
    {
        return $this->postedAt;
    }

    public function setPostedAt(?\DateTime $postedAt): static
    {
        $this->postedAt = $postedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCluster(): ?Cluster
    {
        return $this->cluster;
    }

    public function setCluster(?Cluster $cluster): static
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

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
}
