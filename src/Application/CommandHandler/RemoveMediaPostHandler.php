<?php

namespace App\Application\CommandHandler;

use App\Application\Command\RemoveMediaPost;
use App\Entity\Post\MediaPost;
use App\Repository\Post\MediaPostRepository;
use App\Service\S3Service;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsMessageHandler]
final class RemoveMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly UploadHandler $uploadHandler,
        private readonly S3Service $s3Service,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RemoveMediaPost $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaPostId]);

        if (null === $mediaPost) {
            $this->logger->warning(sprintf('Failed to remove media post: media with id [%s] not found.', (string) $message->mediaPostId), ['id' => (string) $message->mediaPostId]);

            return;
        }

        $localPath = $this->s3Service->getLocalPath($mediaPost);
        $this->s3Service->delete($mediaPost);
        unlink($localPath);

        if ($message->delete) {
            $this->mediaPostRepository->delete($mediaPost);
        }
    }
}
