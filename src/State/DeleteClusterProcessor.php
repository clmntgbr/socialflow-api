<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Denormalizer\PostDenormalizer;
use App\Entity\Post\Cluster;
use App\Entity\Post\FacebookPost;
use App\Entity\Post\InstagramPost;
use App\Entity\Post\LinkedinPost;
use App\Entity\Post\ThreadPost;
use App\Entity\Post\TwitterPost;
use App\Entity\Post\YoutubePost;
use App\Entity\SocialAccount\SocialAccount;
use App\Exception\SocialAccountException;
use Symfony\Component\HttpFoundation\Response;

class DeleteClusterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PostDenormalizer $denormalizer,
        private readonly RemoveProcessor $removeProcessor,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Cluster) {
            return $data;
        }

        if ($data->hasPublishedPosts()) {
            throw new SocialAccountException(message: 'You cant delete this cluster with published post', code: Response::HTTP_BAD_REQUEST);
        }

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
