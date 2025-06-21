<?php

namespace App\Controller;

use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\SocialAccount\GetSocialAccountConnectUrl;
use App\Entity\User;
use App\Service\SocialAccount\SocialAccountServiceFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class SocialAccountController extends AbstractController
{
    public function __construct(
        private readonly SocialAccountServiceFactory $socialAccountServiceFactory,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/social_account/{provider}/connect_url',
        name: 'social_account_connect_url',
        methods: ['GET'],
        requirements: ['provider' => 'facebook|linkedin|twitter|thread|youtube|instagram']
    )]
    public function getConnectUrl(
        #[MapQueryString()] GetSocialAccountConnectUrl $getSocialAccountConnectUrl,
        #[CurrentUser()] ?User $user,
        string $provider,
    ): JsonResponse {
        $service = $this->socialAccountServiceFactory->get($provider);

        $url = $service->getConnectUrl($user, $getSocialAccountConnectUrl->frontCallback);

        return new JsonResponse(
            data: $this->serializer->serialize(['url' => $url], 'json'),
            status: Response::HTTP_OK,
            json: true
        );
    }

    #[Route('/social_account/{provider}/callback',
        name: 'social_account_callback',
        methods: ['GET'],
        requirements: ['provider' => 'facebook|linkedin|twitter|thread|youtube|instagram']
    )]
    public function getCallback(
        #[MapQueryString()] GetSocialAccountCallback $getSocialAccountCallback,
        string $provider,
    ): RedirectResponse {
        $service = $this->socialAccountServiceFactory->get($provider);

        return $service->create($getSocialAccountCallback);
    }
}
