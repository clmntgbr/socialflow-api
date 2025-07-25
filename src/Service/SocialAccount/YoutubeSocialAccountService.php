<?php

namespace App\Service\SocialAccount;

use App\Application\Command\CreateOrUpdateYoutubeAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetAccounts\YoutubeGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\SocialAccount\YoutubeAccount;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessToken\YoutubeAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\YoutubeAccessTokenParameters;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Exception\MethodNotImplementedException;
use App\Exception\SocialAccountException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YoutubeSocialAccountService implements SocialAccountServiceInterface
{
    private const YOUTUBE_API_URL = 'https://www.googleapis.com';
    private const YOUTUBE_OAUTH_URL = 'https://accounts.google.com/o/oauth2/v2';
    private const YOUTUBE_OAUTH2_URL = 'https://oauth2.googleapis.com';
    private const YOUTUBE_CONNECT_URL = self::YOUTUBE_OAUTH_URL.'/auth';
    private const YOUTUBE_ACCESS_TOKEN = self::YOUTUBE_OAUTH2_URL.'/token';
    private const YOUTUBE_ACCOUNT = self::YOUTUBE_API_URL.'/youtube/v3/channels?part=snippet,contentDetails,statistics&mine=true';

    public function __construct(
        private HttpClientInterface $httpClient,
        private UserRepository $userRepository,
        private Denormalizer $denormalizer,
        private MessageBusInterface $bus,
        private string $youtubeClientId,
        private string $youtubeClientSecret,
        private string $apiUrl,
        private string $frontUrl,
    ) {
    }

    public function getConnectUrl(User $user): string
    {
        $user = $this->userRepository->update($user, [
            'state' => Uuid::v4(),
        ]);

        $params = [
            'client_id' => $this->youtubeClientId,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::YOUTUBE_CALLBACK_URL,
            'scope' => implode(' ', $this->getScopes()),
            'response_type' => 'code',
            'prompt' => 'select_account',
            'access_type' => 'offline',
            'state' => $user->getState(),
        ];

        return self::YOUTUBE_CONNECT_URL.'?'.http_build_query($params);
    }

    public function getScopes(): array
    {
        return [
            'openid',
            'email',
            'profile',
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.channel-memberships.creator',
        ];
    }

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['state' => $getSocialAccountCallback->state]);

        if (null === $user) {
            return new RedirectResponse(sprintf('%s', $this->frontUrl));
        }

        try {
            $params = new YoutubeAccessTokenParameters(
                code: $getSocialAccountCallback->code,
            );

            $accessToken = $this->getAccessToken($params);
            $accounts = $this->getAccounts($accessToken);

            foreach ($accounts->youtubeAccounts as $youtubeAccount) {
                $this->bus->dispatch(new CreateOrUpdateYoutubeAccount(
                    groupId: $user->getActiveGroup()->getId(),
                    youtubeToken: $accessToken,
                    youtubeAccount: $youtubeAccount,
                ));
            }

            return new RedirectResponse($this->frontUrl.'/social-accounts/activation');
        } catch (\Exception $exception) {
            return new RedirectResponse(sprintf('%s?error=true&message=3', $this->frontUrl));
        }
    }

    public function getMe(SocialAccount $socialAccount): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param YoutubeAccessTokenParameters $params
     *
     * @return YoutubeAccessToken
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $params->code,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::YOUTUBE_CALLBACK_URL,
            'client_id' => $this->youtubeClientId,
            'client_secret' => $this->youtubeClientSecret,
        ];

        $url = self::YOUTUBE_ACCESS_TOKEN.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Youtube API error: received status code {$statusCode} when requesting access token.", $statusCode);
            }

            $content = $response->toArray();
            if (empty($content)) {
                throw new SocialAccountException('Youtube API error: received empty response when requesting access token.');
            }

            return $this->denormalizer->denormalize($content, YoutubeAccessToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Could not retrieve access token from Youtube API: an exception occurred during the request.');
        }
    }

    public function getLongAccessToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param YoutubeAccessToken $token
     *
     * @return YoutubeGetAccounts
     */
    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts
    {
        $url = self::YOUTUBE_ACCOUNT;

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => $token->tokenType.' '.$token->token,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Youtube API error: received status code {$statusCode} when requesting accounts.", $statusCode);
            }

            /** @var array $accounts */
            $accounts = $response->toArray()['items'] ?? [];

            if (empty($accounts)) {
                throw new SocialAccountException('Youtube API error: received empty response when requesting accounts.');
            }

            return new YoutubeGetAccounts(youtubeAccounts: $this->denormalizer->denormalize($accounts, $this->getYoutubeAccountArrayType()));
        } catch (\Exception $exception) {
            throw new SocialAccountException('Could not retrieve Youtube accounts: an exception occurred during the request.');
        }
    }

    private function getYoutubeAccountArrayType(): string
    {
        return YoutubeAccount::class.'[]';
    }
}
