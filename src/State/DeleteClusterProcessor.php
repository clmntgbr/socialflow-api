<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Post\Cluster;
use App\Exception\SocialAccountException;
use Symfony\Component\HttpFoundation\Response;

class DeleteClusterProcessor implements ProcessorInterface
{
    public function __construct(
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
