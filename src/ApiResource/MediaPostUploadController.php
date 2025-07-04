<?php

namespace App\ApiResource;

use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Repository\Post\MediaPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Handler\UploadHandler;

class MediaPostUploadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UploadHandler $uploadHandler,
        private readonly MediaPostRepository $mediaPostRepository,
        private readonly SerializerInterface $serializer,
    ) {}

    public function __invoke(
        #[MapUploadedFile()] ?File $file,
    ): JsonResponse 
    {
        if (null === $file) {
            throw new BadRequestHttpException('No file provided.');
        }

        $mediaPost = new MediaPost();
        $mediaPost->setFile($file);

        $this->uploadHandler->upload($mediaPost, 'file');
        $this->mediaPostRepository->save($mediaPost, true);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['media.read'])
            ->toArray();

        return new JsonResponse(
            data: $this->serializer->serialize($mediaPost, 'json', $context),
            status: Response::HTTP_OK,
            json: true
        );
    }
}
