<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrganization;
use App\Application\Command\PublishPost;
use App\Entity\Organization;
use App\Entity\Post\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Enum\SocialAccountStatus;
use App\Repository\Post\PostRepository;
use App\Repository\UserRepository;
use App\Service\Publish\PublishServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PublishPostHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private PublishServiceFactory $publishServiceFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(PublishPost $message): void
    {
        /** @var ?Post $post */
        $post = $this->postRepository->findOneBy(['id' => (string) $message->postId]);

        if (null === $post) {
            $this->logger->info('Post does not exist', ['id' => (string) $message->postId]);
            return;
        }

        $socialAccount = $post->getCluster()->getSocialAccount();

        if (!$socialAccount->isActive()) {
            $this->logger->info('This social account cant publish', ['id' => (string) $socialAccount->getId(), 'status' => $socialAccount->getStatus()]);
            return;
        }

        if ($post->isPublished()) {
            $this->logger->info('This post is already published', ['id' => (string) $post->getId(), 'status' => $post->getStatus()]);
            return;
        }

        try {
            $service = $this->publishServiceFactory->get($socialAccount->getType());
            $getPost = $service->post($post);
            $post->setPublished($getPost->getId());
        } catch (\Exception) {
            $post->setFailed();
        }

        $this->postRepository->save($post);
    }
}
