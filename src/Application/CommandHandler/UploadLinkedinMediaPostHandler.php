<?php

namespace App\Application\CommandHandler;

use App\Application\Command\UploadLinkedinMediaPost;
use App\Dto\Publish\Upload\UploadLinkedinPayload;
use App\Entity\Post\MediaPost;
use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Exception\PublishException;
use App\Repository\Post\MediaPostRepository;
use App\Service\Publish\LinkedinPublishService;
use App\Service\Publish\PublishServiceFactory;
use App\Service\S3Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UploadLinkedinMediaPostHandler
{
    public function __construct(
        private MediaPostRepository $mediaPostRepository,
        private readonly S3Service $s3Service,
        private PublishServiceFactory $publishServiceFactory,
    ) {
    }

    public function __invoke(UploadLinkedinMediaPost $message): void
    {
        /** @var ?MediaPost $mediaPost */
        $mediaPost = $this->mediaPostRepository->findOneBy(['id' => (string) $message->mediaId]);

        if (null === $mediaPost) {
            throw new \Exception(sprintf('MediaPost does not exist with id [%s]', (string) $message->mediaId));
        }

        $mediaPost->markAsProcessing();
        $this->mediaPostRepository->save($mediaPost);

        /** @var LinkedinSocialAccount $socialAccount */
        $socialAccount = $mediaPost->getPost()->getCluster()->getSocialAccount();

        try {
            /** @var LinkedinPublishService */
            $service = $this->publishServiceFactory->get($socialAccount->getType());

            $localPath = $this->s3Service->download($mediaPost);

            $payload = new UploadLinkedinPayload(
                mediaPost: $mediaPost,
                socialAccount: $socialAccount,
                uploadedLinkedinMediaId: $message->uploadedLinkedinMediaId,
                localPath: $localPath
            );

            $service->upload($payload);

            $mediaPost->markAsUploaded();
            $this->mediaPostRepository->save($mediaPost);
        } catch (\Exception $exception) {
            throw new PublishException(message: 'Failed to upload Linkedin media: '.$exception->getMessage(), code: Response::HTTP_BAD_REQUEST, previous: $exception);
        }
    }
}
