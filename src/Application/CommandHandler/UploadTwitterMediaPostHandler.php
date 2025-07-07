<?php

namespace App\Application\CommandHandler;

use App\Application\Command\UploadTwitterMediaPost;
use App\Dto\Publish\Upload\UploadTwitterPayload;
use App\Dto\Publish\UploadMedia\UploadedTwitterMediaId;
use App\Entity\Post\MediaPost;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Exception\MediaPostNotFoundException;
use App\Exception\PublishException;
use App\Repository\Post\MediaPostRepository;
use App\Service\Publish\PublishServiceFactory;
use App\Service\Publish\TwitterPublishService;
use App\Service\S3Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UploadTwitterMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly S3Service $s3Service,
        private PublishServiceFactory $publishServiceFactory,
    ) {
    }

    public function __invoke(UploadTwitterMediaPost $message): UploadedTwitterMediaId
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaId]);

        if (null === $mediaPost) {
            throw new MediaPostNotFoundException((string) $message->mediaId);
        }

        $mediaPost->markAsProcessing();
        $this->mediaPostRepository->save($mediaPost);

        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $mediaPost->getPost()->getCluster()->getSocialAccount();

        try {
            /** @var TwitterPublishService */
            $service = $this->publishServiceFactory->get($socialAccount->getType());

            $localPath = $this->s3Service->download($mediaPost);

           $payload = new UploadTwitterPayload(
                mediaPost: $mediaPost, 
                socialAccount: $socialAccount, 
                localPath: $localPath
            );
            
            $mediaId = $service->upload($payload);
            $service->checkUploadStatus($socialAccount, $mediaId);

            $mediaPost->markAsUploaded();
            $this->mediaPostRepository->save($mediaPost);

            return $mediaId;
        } catch (\Exception $exception) {
            throw new PublishException(message: 'Failed to upload Twitter media: '.$exception->getMessage(), code: Response::HTTP_BAD_REQUEST, previous: $exception);
        }
    }
}
