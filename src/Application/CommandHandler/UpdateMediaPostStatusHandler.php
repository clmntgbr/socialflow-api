<?php

namespace App\Application\CommandHandler;

use App\Application\Command\UpdateClusterStatus;
use App\Application\Command\UpdateMediaPostStatus;
use App\Entity\Post\Cluster;
use App\Entity\Post\MediaPost;
use App\Enum\ClusterStatus;
use App\Exception\ClusterNotFoundException;
use App\Exception\MediaPostNotFoundException;
use App\Repository\Post\ClusterRepository;
use App\Repository\Post\MediaPostRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateMediaPostStatusHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
    ) {
    }

    public function __invoke(UpdateMediaPostStatus $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaPostId]);

        if (null === $mediaPost) {
            throw new MediaPostNotFoundException((string) $message->mediaPostId);
        }

        $mediaPost->setStatus($message->status);
        $this->mediaPostRepository->save($mediaPost);
    }
}
