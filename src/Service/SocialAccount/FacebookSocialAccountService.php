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
use App\Dto\Token\FacebookToken;
use App\Entity\User;
use App\Exception\SocialAccountException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookSocialAccountService implements SocialAccountServiceInterface
{
    private const FACEBOOK_API_URL = 'https://graph.facebook.com';
    private const FACEBOOK_LOGIN_URL = 'https://www.facebook.com/v21.0';
    private const FACEBOOK_CONNECT_URL = self::FACEBOOK_LOGIN_URL.'/dialog/oauth';
    private const FACEBOOK_ACCESS_TOKEN = self::FACEBOOK_API_URL.'/oauth/access_token';
    private const FACEBOOK_REVOKE_TOKEN = self::FACEBOOK_API_URL.'/oauth/revoke';
    private const FACEBOOK_ACCOUNT = self::FACEBOOK_API_URL.'/me';

    public function __construct(
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
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

            if (null === $accessToken) {
                throw new SocialAccountException('Failed to retrieve access token from Facebook API');
            }

            $accounts = $this->getAccounts($accessToken);

            if (empty($accounts)) {
                throw new SocialAccountException('Failed to retrieve accounts from Facebook API');
            }

            foreach ($accounts->facebookAccounts as $facebookAccount) {
                $longAccessToken = $this->getLongAccessToken($facebookAccount->token);

                if (null === $longAccessToken) {
                    continue;
                }

                $this->bus->dispatch(new CreateOrUpdateFacebookAccount(
                    organizationId: $user->getActiveOrganization()->getId(),
                    userId: $user->getId(),
                    facebookToken: $longAccessToken,
                    facebookAccount: $facebookAccount,
                ));
            }

            return new RedirectResponse($this->frontUrl.'/validation');
        } catch (\Exception $exception) {
            dd($exception->getMessage());

            return new RedirectResponse(sprintf('%s?error=true&message=3', $this->frontUrl));
        }
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
                throw new SocialAccountException("Facebook API returned status code {$statusCode}", $statusCode);
            }

            $content = $response->toArray();
            if (empty($content)) {
                throw new SocialAccountException('Empty response from Facebook API');
            }

            return $this->denormalizer->denormalize($content, FacebookAccessToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Failed to retrieve long access token from Facebook API');
        }
    }

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken
    {
        throw new \RuntimeException('Method not implemented.');
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
                throw new SocialAccountException("Facebook API returned status code {$statusCode}", $statusCode);
            }

            $content = $response->toArray();
            if (empty($content)) {
                throw new SocialAccountException('Empty response from Facebook API');
            }

            return $this->denormalizer->denormalize($content, FacebookAccessToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Failed to retrieve access token from Facebook API');
        }
    }

    /**
     * @param FacebookToken $accessToken
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
                throw new SocialAccountException("Facebook API returned status code {$statusCode}", $statusCode);
            }

            $accounts = $response->toArray()['accounts']['data'] ?? [];

            if (empty($accounts)) {
                throw new SocialAccountException('Empty response from Facebook API');
            }

            return new FacebookGetAccounts(facebookAccounts: $this->denormalizer->denormalize($accounts, FacebookAccount::class.'[]'));
        } catch (\Exception $exception) {
            throw new SocialAccountException('Failed to retrieve accounts from Facebook API');
        }
    }
}
