<?php

namespace App\ApiResource;

use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Handler\UploadHandler;

class MediaPostUploadController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UploadHandler $uploadHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('No file provided.');
        }

        $mediaPost = new MediaPost();
        $mediaPost->setFile($uploadedFile);

        // Optionnel : lier à un Post si ID fourni (via formulaire ou query param)
        $postId = $request->request->get('post');
        if ($postId) {
            $post = $this->em->getRepository(Post::class)->find($postId);
            if (!$post) {
                throw new BadRequestHttpException('Invalid post ID');
            }
            $mediaPost->setPost($post);
        }

        $this->uploadHandler->upload($mediaPost, 'file');

        $this->em->persist($mediaPost);
        $this->em->flush();

        return new JsonResponse(
            data: [],
            status: Response::HTTP_OK
        );
    }
}
