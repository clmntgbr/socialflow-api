<?php

namespace App\Service\SocialAccount;

use App\Dto\AccessToken\AbstractToken;
use App\Dto\AccessToken\FacebookToken;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;
use App\Exception\SocialAccountException;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookSocialAccountService implements SocialAccountServiceInterface
{
    private const FACEBOOK_API_URL = 'https://graph.facebook.com';
    private const FACEBOOK_LOGIN_URL = 'https://www.facebook.com/v21.0';
    private const FACEBOOK_CONNECT_URL = self::FACEBOOK_LOGIN_URL.'/dialog/oauth';
    private const FACEBOOK_ACCESS_TOKEN = self::FACEBOOK_API_URL.'/oauth/access_token';

    public function __construct(
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
        private string $apiUrl,
        private string $frontUrl,
        private string $facebookClientId,
        private string $facebookClientSecret,
    ) {
    }

    public function getConnectUrl(User $user, string $callback): string
    {
        $user = $this->userRepository->update($user, [
            'state' => Uuid::v4(),
            'callback' => $callback,
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

    public function create(GetSocialAccountCallback $getSocialAccountCallback)
    {
        $user = $this->userRepository->findOneBy(['state' => $getSocialAccountCallback->state]);

        if (null === $user) {
            return new RedirectResponse(sprintf('%s', $this->frontUrl));
        }

        $accessToken = $this->getToken($getSocialAccountCallback->code);
        dd($accessToken);
    }

    public function delete()
    {
    }

    public function getToken(string $code): AbstractToken
    {
        $params = [
            'client_id' => $this->facebookClientId,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::FACEBOOK_CALLBACK_URL,
            'client_secret' => $this->facebookClientSecret,
            'code' => $code,
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
            if ($statusCode !== 200) {
                throw new SocialAccountException(
                    "Facebook API returned status code {$statusCode}",
                    $statusCode
                );
            }

            $content = $response->getContent();
            if (empty($content)) {
                throw new SocialAccountException('Empty response from Facebook API');
            }

            return $this->serializer->deserialize($response->getContent(), FacebookToken::class, 'json');
        } catch (TransportExceptionInterface $e) {
            throw new SocialAccountException(message: 'Network error while contacting Facebook API: ' . $e->getMessage(), previous: $e);
        } catch (ClientExceptionInterface $e) {
            throw new SocialAccountException(message: 'Facebook API error: ' . $e->getMessage(), previous: $e);
        } catch (Exception $e) {
            throw new SocialAccountException(message: 'Failed to parse Facebook API response: ' . $e->getMessage(), previous: $e);
        }
    }

    public function getAccount()
    {
    }
}
