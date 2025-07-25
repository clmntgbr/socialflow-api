<?php

namespace App\Service\SocialAccount;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Application\Command\CreateOrUpdateTwitterAccount;
use App\Application\Command\UpdateTwitterSocialAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetAccounts\TwitterGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\SocialAccount\TwitterAccount;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessToken\TwitterAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\TwitterAccessTokenParameters;
use App\Dto\Token\RequestToken\TwitterRequestToken;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Entity\User;
use App\Exception\ConnectUrlException;
use App\Exception\MethodNotImplementedException;
use App\Exception\SocialAccountException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TwitterSocialAccountService implements SocialAccountServiceInterface
{
    private const TWITTER_API_URL = 'https://api.x.com';
    private const TWITTER_CONNECT_URL = self::TWITTER_API_URL.'/oauth/authenticate';
    private const TWITTER_ACCESS_TOKEN = self::TWITTER_API_URL.'/oauth/access_token';

    public function __construct(
        private UserRepository $userRepository,
        private Denormalizer $denormalizer,
        private SerializerInterface $serializer,
        private HttpClientInterface $httpClient,
        private MessageBusInterface $bus,
        private string $apiUrl,
        private string $frontUrl,
        private readonly string $twitterApiKey,
        private readonly string $twitterApiSecret,
        private ?TwitterOAuth $twitterOAuth = null,
    ) {
        $this->twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret);
    }

    public function getConnectUrl(User $user): string
    {
        $user = $this->userRepository->update($user, [
            'state' => Uuid::v4(),
        ]);

        try {
            $requestToken = $this->getRequestToken($user->getState());
        } catch (\Exception $exception) {
            throw new ConnectUrlException(message: $exception->getMessage(), previous: $exception);
        }

        $params = [
            'oauth_token' => $requestToken->oauthToken,
        ];

        return self::TWITTER_CONNECT_URL.'?'.http_build_query($params);
    }

    public function getScopes(): array
    {
        return [
            'created_at',
            'description',
            'entities',
            'id',
            'location',
            'most_recent_tweet_id',
            'name',
            'pinned_tweet_id',
            'profile_image_url',
            'protected',
            'public_metrics',
            'url',
            'username',
            'verified',
            'verified_type',
            'withheld',
        ];
    }

    private function getRequestToken(string $state): TwitterRequestToken
    {
        $url = $this->apiUrl.SocialAccountServiceInterface::TWITTER_CALLBACK_URL.'?'.http_build_query([
            'state' => $state,
        ]);

        try {
            $response = $this->twitterOAuth->oauth('oauth/request_token', [
                'oauth_callback' => $url,
            ]);

            return $this->denormalizer->denormalize($response, TwitterRequestToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Could not obtain request token from Twitter: an exception occurred during the request.');
        }
    }

    /**
     * @param TwitterSocialAccount $twitterSocialAccount
     *
     * @return TwitterGetAccounts
     */
    public function getMe(SocialAccount $twitterSocialAccount): void
    {
        $account = $this->getAccounts(new TwitterAccessToken(
            oauthToken: $twitterSocialAccount->getTokenSocialAccount()->getToken(),
            oauthTokenSecret: $twitterSocialAccount->getTokenSocialAccount()->getTokenSecret(),
        ));

        $this->bus->dispatch(new UpdateTwitterSocialAccount(
            socialAccountId: $twitterSocialAccount->getSocialAccountId(),
            twitterAccount: $account->twitterAccount,
        ));
    }

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['state' => $getSocialAccountCallback->state]);

        if (null === $user) {
            return new RedirectResponse(sprintf('%s', $this->frontUrl));
        }

        try {
            $params = new TwitterAccessTokenParameters(
                oauthToken: $getSocialAccountCallback->oauthToken,
                oauthVerifier: $getSocialAccountCallback->oauthVerifier,
            );

            $accessToken = $this->getAccessToken($params);
            $accounts = $this->getAccounts($accessToken);

            $this->bus->dispatch(new CreateOrUpdateTwitterAccount(
                groupId: $user->getActiveGroup()->getId(),
                twitterAccount: $accounts->twitterAccount,
                twitterToken: $accessToken,
            ));

            return new RedirectResponse($this->frontUrl.'/social-accounts/activation');
        } catch (\Exception) {
            return new RedirectResponse(sprintf('%s?error=true&message=3', $this->frontUrl));
        }
    }

    /**
     * @param TwitterAccessTokenParameters $params
     *
     * @return TwitterAccessToken
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        $params = [
            'oauth_token' => $params->oauthToken,
            'oauth_verifier' => $params->oauthVerifier,
        ];

        $url = self::TWITTER_ACCESS_TOKEN.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('POST', $url);

            $statusCode = $response->getStatusCode();

            if (200 !== $statusCode) {
                throw new SocialAccountException("Twitter API error: received status code {$statusCode} when requesting access token.", $statusCode);
            }

            return TwitterAccessToken::fromString($response->getContent());
        } catch (\Exception) {
            throw new SocialAccountException('Could not retrieve access token from Twitter API: an exception occurred during the request.');
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
     * @param TwitterAccessToken $token
     *
     * @return TwitterGetAccounts
     */
    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts
    {
        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $token->oauthToken, $token->oauthTokenSecret);
            $twitterOAuth->setApiVersion('2');

            $response = $twitterOAuth->get('users/me', [
                'expansions' => ['pinned_tweet_id'],
                'user.fields' => implode(',', $this->getScopes()),
            ]);

            $response = $response->data ?? $response;

            return new TwitterGetAccounts(twitterAccount: $this->serializer->deserialize(json_encode($response), TwitterAccount::class, 'json'));
        } catch (\Exception) {
            throw new SocialAccountException('Could not retrieve Twitter accounts: an exception occurred during the request.');
        }
    }
}
