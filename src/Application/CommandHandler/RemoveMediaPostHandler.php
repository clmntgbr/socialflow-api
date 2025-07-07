<?php

namespace App\Application\CommandHandler;

use App\Application\Command\RemoveMediaPost;
use App\Entity\Post\MediaPost;
use App\Exception\MediaPostNotFoundException;
use App\Repository\Post\MediaPostRepository;
use App\Service\S3Service;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsMessageHandler]
final class RemoveMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly UploadHandler $uploadHandler,
        private readonly S3Service $s3Service,
    ) {
    }

    public function __invoke(RemoveMediaPost $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaPostId]);

        if (null === $mediaPost) {
            throw new MediaPostNotFoundException((string) $message->mediaPostId);
        }

        $this->s3Service->delete($mediaPost);
        $this->uploadHandler->remove($mediaPost, 'file');

        if ($message->delete) {
            $this->mediaPostRepository->delete($mediaPost);
        }
    }
}
