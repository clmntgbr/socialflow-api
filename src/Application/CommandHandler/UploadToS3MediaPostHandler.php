<?php

namespace App\Application\CommandHandler;

use App\Application\Command\RemoveUnusedMediaPost;
use App\Application\Command\UploadToS3MediaPost;
use App\Entity\Post\MediaPost;
use App\Exception\MediaPostNotFoundException;
use App\Repository\Post\MediaPostRepository;
use App\Service\S3Service;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsMessageHandler]
final class UploadToS3MediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly UploadHandler $uploadHandler,
        private readonly S3Service $s3Service,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(UploadToS3MediaPost $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaId]);

        if (null === $mediaPost) {
            throw new MediaPostNotFoundException((string) $message->mediaId);
        }

        $this->s3Service->upload($mediaPost);
        $this->uploadHandler->remove($mediaPost, 'file');

        $this->messageBus->dispatch(new RemoveUnusedMediaPost(mediaPostId: $mediaPost->getId()), [
            new DelayStamp(21600000),
            new AmqpStamp('async-low'),
        ]);
    }
}
