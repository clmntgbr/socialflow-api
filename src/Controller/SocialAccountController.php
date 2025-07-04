<?php

namespace App\Controller;

use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;
use App\Service\SocialAccount\SocialAccountServiceFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class SocialAccountController extends AbstractController
{
    public function __construct(
        private readonly SocialAccountServiceFactory $socialAccountServiceFactory,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private string $frontUrl,
    ) {
    }

    #[Route('/social_account/{provider}/connect_url',
        name: 'social_account_connect_url',
        methods: ['GET'],
        requirements: ['provider' => 'facebook|linkedin|twitter|thread|youtube|instagram']
    )]
    public function getConnectUrl(
        #[CurrentUser()] ?User $user,
        string $provider,
    ): JsonResponse {
        $service = $this->socialAccountServiceFactory->get($provider);

        $url = $service->getConnectUrl($user);

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

        $errors = $this->validator->validate(
            value: $getSocialAccountCallback,
            groups: [$provider]
        );

        if ($errors->count() > 0) {
            return new RedirectResponse(sprintf('%s?error=true&message=3', $this->frontUrl));
        }

        return $service->create($getSocialAccountCallback);
    }
}
