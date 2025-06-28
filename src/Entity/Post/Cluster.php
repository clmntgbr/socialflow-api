<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as PostOperation;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\Trait\UuidTrait;
use App\Enum\ClusterStatus;
use App\Repository\Post\ClusterRepository;
use App\State\ClusterProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ClusterRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['cluster.read']],
        ),
        new PostOperation(
            processor: ClusterProcessor::class,
            normalizationContext: ['groups' => ['cluster.read']],
            denormalizationContext: ['groups' => ['cluster.write', 'post.write']],
        ),
    ]
)]
class Cluster
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['cluster.read', 'post.read'])]
    private string $status;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'cluster', cascade: ['persist', 'remove'])]
    #[Groups(['cluster.read', 'cluster.write'])]
    private Collection $posts;

    #[ORM\ManyToOne(targetEntity: SocialAccount::class, inversedBy: 'clusters')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cluster.read', 'post.read', 'cluster.write'])]
    private SocialAccount $socialAccount;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->status = ClusterStatus::DRAFT->value;
        $this->posts = new ArrayCollection();
    }

    public function initializePosts(array $posts)
    {
        $this->posts = new ArrayCollection($posts);
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setCluster($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCluster() === $this) {
                $post->setCluster(null);
            }
        }

        return $this;
    }

    public function getSocialAccount(): ?SocialAccount
    {
        return $this->socialAccount;
    }

    public function setSocialAccount(?SocialAccount $socialAccount): static
    {
        $this->socialAccount = $socialAccount;

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
}
