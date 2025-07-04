<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\Entity\Trait\UuidTrait;
use App\Enum\PostStatus;
use App\Repository\Post\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    operations: [
        new Delete(),
    ]
)]
class Post implements PostInterface
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['cluster.read'])]
    private ?string $postId = null;

    #[ORM\Column(name: '`order`', type: Types::INTEGER)]
    #[Groups(['cluster.read', 'cluster.write'])]
    private int $order;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['cluster.read', 'cluster.write'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['cluster.read', 'cluster.write'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['cluster.read'])]
    private string $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cluster.read'])]
    private ?\DateTime $postedAt = null;

    #[ORM\ManyToOne(targetEntity: Cluster::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private Cluster $cluster;

    #[ORM\OneToMany(targetEntity: MediaPost::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    #[Groups(['cluster.read', 'cluster.write'])]
    private Collection $medias;

    public function __construct()
    {
        $this->order = 1;
        $this->id = Uuid::v4();
        $this->status = PostStatus::DRAFT->value;
        $this->medias = new ArrayCollection();
    }

    public function getType(): string
    {
        return '';
    }

    public function isFirst(): bool
    {
        return 1 === $this->order;
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

    /**
     * @return Collection<int, MediaPost>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(MediaPost $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setPost($this);
        }

        return $this;
    }

    public function removeMedia(MediaPost $media): static
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getPost() === $this) {
                $media->setPost(null);
            }
        }

        return $this;
    }
}
