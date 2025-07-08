<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\YoutubeAccessTokenParameters;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Exception\MethodNotImplementedException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Uid\Uuid;

class ThreadSocialAccountService implements SocialAccountServiceInterface
{
    public const THREAD_API_URL = 'https://threads.net';
    public const THREAD_CONNECT_URL = self::THREAD_API_URL.'/oauth/authorize';

    public function __construct(
        private UserRepository $userRepository,
        private string $threadClientId,
        private string $apiUrl,
    ) {
    }

    // https://developers.facebook.com/docs/threads/get-started/get-access-tokens-and-permissions/
    public function getConnectUrl(User $user): string
    {
        $user = $this->userRepository->update($user, [
            'state' => Uuid::v4(),
        ]);

        $params = [
            'client_id' => $this->threadClientId,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::THREAD_CALLBACK_URL,
            'scope' => implode(',', $this->getScopes()),
            'response_type' => 'code',
            'state' => $user->getState(),
        ];

        return self::THREAD_CONNECT_URL.'?'.http_build_query($params);
    }

    public function getScopes(): array
    {
        return [
            'threads_basic',
        ];
    }

    public function getMe(SocialAccount $socialAccount): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param YoutubeAccessTokenParameters $params
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getLongAccessToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
