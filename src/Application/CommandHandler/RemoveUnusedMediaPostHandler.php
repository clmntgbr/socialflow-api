<?php

namespace App\Application\CommandHandler;

use App\Application\Command\RemoveUnusedMediaPost;
use App\Entity\Post\MediaPost;
use App\Exception\MediaPostNotFoundException;
use App\Repository\Post\MediaPostRepository;
use App\Service\S3Service;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsMessageHandler]
final class RemoveUnusedMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly UploadHandler $uploadHandler,
        private readonly S3Service $s3Service,
    ) {
    }

    public function __invoke(RemoveUnusedMediaPost $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaPostId]);

        if (null === $mediaPost) {
            throw new MediaPostNotFoundException((string) $message->mediaPostId);
        }

        if (null !== $mediaPost->getPost()) {
            return;
        }

        $this->s3Service->delete($mediaPost);
        $this->uploadHandler->remove($mediaPost, 'file');
        $this->mediaPostRepository->delete($mediaPost);
    }
}
