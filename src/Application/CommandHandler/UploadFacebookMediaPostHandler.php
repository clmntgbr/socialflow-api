<?php

namespace App\Application\CommandHandler;

use App\Application\Command\UploadFacebookMediaPost;
use App\Dto\Publish\UploadMedia\UploadedFacebookMediaId;
use App\Entity\Post\MediaPost;
use App\Exception\MediaPostNotFoundException;
use App\Exception\PublishException;
use App\Repository\Post\MediaPostRepository;
use App\Service\Publish\FacebookPublishService;
use App\Service\Publish\PublishServiceFactory;
use App\Service\S3Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsMessageHandler]
final class UploadFacebookMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly UploadHandler $uploadHandler,
        private readonly S3Service $s3Service,
        private PublishServiceFactory $publishServiceFactory,
    ) {
    }

    public function __invoke(UploadFacebookMediaPost $message): UploadedFacebookMediaId
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaId]);

        if (null === $mediaPost) {
            throw new MediaPostNotFoundException((string) $message->mediaId);
        }

        $socialAccount = $mediaPost->getPost()->getCluster()->getSocialAccount();

        try {
            /** @var FacebookPublishService */
            $service = $this->publishServiceFactory->get($socialAccount->getType());

            $localPath = $this->s3Service->download($mediaPost);

            return $service->uploadMedia($socialAccount, $localPath);
        } catch (\Exception $exception) {
            throw new PublishException(message: 'Failed to upload Facebook media: '.$exception->getMessage(), code: Response::HTTP_BAD_REQUEST, previous: $exception);
        }
    }
}
