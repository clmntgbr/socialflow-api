<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrganization;
use App\Application\Command\RemoveUnusedMediaPost;
use App\Entity\Organization;
use App\Entity\Post\MediaPost;
use App\Entity\User;
use App\Repository\Post\MediaPostRepository;
use App\Repository\UserRepository;
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
            throw new \Exception(sprintf('MediaPost does not exist with id [%s]', (string) $message->mediaPostId));
        }
        
        if ($mediaPost->getPost() !== null) {
            return;
        }

        $this->s3Service->delete($mediaPost);
        $this->uploadHandler->remove($mediaPost, 'file');
        $this->mediaPostRepository->delete($mediaPost);
    }
}
