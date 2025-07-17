<?php

namespace App\Service\SocialAccount;

use App\Application\Command\CreateOrUpdateFacebookAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\SocialAccount\FacebookAccount;
use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetAccounts\FacebookGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessToken\FacebookAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\FacebookAccessTokenParameters;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Exception\MethodNotImplementedException;
use App\Exception\SocialAccountException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookSocialAccountService implements SocialAccountServiceInterface
{
    private const FACEBOOK_API_URL = 'https://graph.facebook.com';
    private const FACEBOOK_LOGIN_URL = 'https://www.facebook.com/v21.0';
    private const FACEBOOK_CONNECT_URL = self::FACEBOOK_LOGIN_URL.'/dialog/oauth';
    private const FACEBOOK_ACCESS_TOKEN = self::FACEBOOK_API_URL.'/oauth/access_token';
    private const FACEBOOK_ACCOUNT = self::FACEBOOK_API_URL.'/me';

    public function __construct(
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient,
        private Denormalizer $denormalizer,
        private MessageBusInterface $bus,
        private string $apiUrl,
        private string $frontUrl,
        private string $facebookClientId,
        private string $facebookClientSecret,
    ) {
    }

    public function getConnectUrl(User $user): string
    {
        $user = $this->userRepository->update($user, [
            'state' => Uuid::v4(),
        ]);

        $params = [
            'client_id' => $this->facebookClientId,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::FACEBOOK_CALLBACK_URL,
            'scope' => implode(',', $this->getScopes()),
            'state' => $user->getState(),
        ];

        return self::FACEBOOK_CONNECT_URL.'?'.http_build_query($params);
    }

    public function getScopes(): array
    {
        return [
            'email',
            'pages_manage_cta',
            'pages_show_list',
            'read_page_mailboxes',
            'business_management',
            'pages_messaging',
            'pages_messaging_subscriptions',
            'page_events',
            'pages_read_engagement',
            'pages_manage_metadata',
            'pages_read_user_content',
            'pages_manage_ads',
            'pages_manage_posts',
            'pages_manage_engagement',
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
            $params = new FacebookAccessTokenParameters($getSocialAccountCallback->code);

            $accessToken = $this->getAccessToken($params);
            $accounts = $this->getAccounts($accessToken);

            foreach ($accounts->facebookAccounts as $facebookAccount) {
                $longAccessToken = $this->getLongAccessToken($facebookAccount->token);

                $this->bus->dispatch(new CreateOrUpdateFacebookAccount(
                    groupId: $user->getActiveGroup()->getId(),
                    facebookToken: $longAccessToken,
                    facebookAccount: $facebookAccount,
                ));
            }

            return new RedirectResponse($this->frontUrl.'/validation');
        } catch (\Exception $exception) {
            dd($exception);

            return new RedirectResponse(sprintf('%s?error=true&message=3', $this->frontUrl));
        }
    }

    public function getMe(SocialAccount $socialAccount): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @return FacebookAccessToken
     *
     * @throws SocialAccountException
     */
    public function getLongAccessToken(string $token): AbstractAccessToken
    {
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->facebookClientId,
            'client_secret' => $this->facebookClientSecret,
            'fb_exchange_token' => $token,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::FACEBOOK_CALLBACK_URL,
        ];

        $url = self::FACEBOOK_ACCESS_TOKEN.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Facebook API error: received status code {$statusCode} when requesting access token.", $statusCode);
            }

            $content = $response->toArray();
            if (empty($content)) {
                throw new SocialAccountException('Facebook API error: received empty response when requesting access token.');
            }

            return $this->denormalizer->denormalize($content, FacebookAccessToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Could not retrieve long access token from Facebook API: an exception occurred during the request.');
        }
    }

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param FacebookAccessTokenParameters $params
     *
     * @return FacebookAccessToken
     *
     * @throws SocialAccountException
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        $params = [
            'client_id' => $this->facebookClientId,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::FACEBOOK_CALLBACK_URL,
            'client_secret' => $this->facebookClientSecret,
            'code' => $params->code,
        ];

        $url = self::FACEBOOK_ACCESS_TOKEN.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Facebook API error: received status code {$statusCode} when requesting access token.", $statusCode);
            }

            $content = $response->toArray();
            if (empty($content)) {
                throw new SocialAccountException('Facebook API error: received empty response when requesting access token.');
            }

            return $this->denormalizer->denormalize($content, FacebookAccessToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Could not retrieve access token from Facebook API: an exception occurred during the request.');
        }
    }

    /**
     * @param FacebookAccessToken $accessToken
     *
     * @return FacebookGetAccounts
     */
    public function getAccounts(AbstractAccessToken $accessToken): AbstractGetAccounts
    {
        $params = [
            'fields' => 'accounts{name,access_token,followers_count,fan_count,bio,emails,id,link,page_token,picture{url},website},email',
            'access_token' => $accessToken->token,
        ];

        $url = self::FACEBOOK_ACCOUNT.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Facebook API error: received status code {$statusCode} when requesting access token.", $statusCode);
            }

            $accounts = $response->toArray()['accounts']['data'] ?? [];

            if (empty($accounts)) {
                throw new SocialAccountException('Facebook API error: received empty response when requesting accounts.');
            }

            return new FacebookGetAccounts(facebookAccounts: $this->denormalizer->denormalize($accounts, FacebookAccount::class.'[]'));
        } catch (\Exception $exception) {
            throw new SocialAccountException('Could not retrieve Facebook accounts: an exception occurred during the request.');
        }
    }
}
