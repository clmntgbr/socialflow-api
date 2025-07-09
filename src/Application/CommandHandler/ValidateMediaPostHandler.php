<?php

namespace App\Application\CommandHandler;

use App\Application\Command\ValidateMediaPost;
use App\Entity\Post\MediaPost;
use App\Repository\Post\MediaPostRepository;
use App\Service\Validate\ValidateServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ValidateMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private LoggerInterface $logger,
        private ValidateServiceFactory $validateServiceFactory,
    ) {
    }

    public function __invoke(ValidateMediaPost $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaId]);

        if (null === $mediaPost) {
            $this->logger->warning(sprintf('Failed to get media post: media with id [%s] not found.', (string) $message->mediaId), ['id' => (string) $message->mediaId]);

            return;
        }

        $service = $this->validateServiceFactory->get($mediaPost->getPost()->getType());
        $service->validateMediaPost($mediaPost);
    }
}
