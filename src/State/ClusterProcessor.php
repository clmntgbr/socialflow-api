<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
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

class ClusterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PostDenormalizer $denormalizer,
        private readonly PersistProcessor $persistProcessor,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Cluster) {
            return $data;
        }

        $request = $context['request'] ?? null;
        $payload = json_decode($request->getContent(), true);

        $posts = $this->denormalizer->denormalize(
            data: $payload['posts'] ?? [],
            type: $this->getPostEntityClass($data->getSocialAccount()),
            context: ['cluster' => $data]
        );

        $data->initializePosts($posts);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function getPostEntityClass(SocialAccount $socialAccount): string
    {
        return match ($socialAccount->getType()) {
            'facebook' => (new FacebookPost())::class,
            'twitter' => (new TwitterPost())::class,
            'linkedin' => (new LinkedinPost())::class,
            'youtube' => (new YoutubePost())::class,
            'thread' => (new ThreadPost())::class,
            'instagram' => (new InstagramPost())::class,
            default => throw new \InvalidArgumentException('Invalid type of social account'),
        };
    }
}
