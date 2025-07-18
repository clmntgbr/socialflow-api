<?php

namespace App\Service\SocialAccount;

use App\Application\Command\CreateOrUpdateLinkedinAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetAccounts\LinkedinGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\SocialAccount\LinkedinAccount;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessToken\LinkedinAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\LinkedinAccessTokenParameters;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Exception\MethodNotImplementedException;
use App\Exception\SocialAccountException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LinkedinSocialAccountService implements SocialAccountServiceInterface
{
    private const LINKEDIN_API_URL = 'https://api.linkedin.com';
    private const LINKEDIN_LOGIN_URL = 'https://www.linkedin.com';
    private const LINKEDIN_CONNECT_URL = self::LINKEDIN_LOGIN_URL.'/oauth/v2/authorization';
    private const LINKEDIN_ACCESS_TOKEN = self::LINKEDIN_API_URL.'/oauth/v2/accessToken';
    private const LINKEDIN_ACCOUNT = self::LINKEDIN_API_URL.'/v2/userinfo';

    public function __construct(
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient,
        private Denormalizer $denormalizer,
        private MessageBusInterface $bus,
        private string $linkedinClientId,
        private string $linkedinClientSecret,
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
            'response_type' => 'code',
            'client_id' => $this->linkedinClientId,
            'scope' => implode(',', $this->getScopes()),
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::LINKEDIN_CALLBACK_URL,
            'state' => $user->getState(),
        ];

        return self::LINKEDIN_CONNECT_URL.'?'.http_build_query($params);
    }

    public function getScopes(): array
    {
        return [
            'profile',
            'email,openid',
            'w_member_social',
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
            $params = new LinkedinAccessTokenParameters(
                code: $getSocialAccountCallback->code,
            );

            $accessToken = $this->getAccessToken($params);
            $accounts = $this->getAccounts($accessToken);

            $this->bus->dispatch(new CreateOrUpdateLinkedinAccount(
                groupId: $user->getActiveGroup()->getId(),
                linkedinAccount: $accounts->linkedinAccount,
                linkedinToken: $accessToken,
            ));

            return new RedirectResponse($this->frontUrl.'/social-accounts/validation');
        } catch (\Exception) {
            return new RedirectResponse(sprintf('%s?error=true&message=3', $this->frontUrl));
        }
    }

    public function getMe(SocialAccount $socialAccount): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param LinkedinAccessTokenParameters $params
     *
     * @return LinkedinAccessToken
     *
     * @throws SocialAccountException
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $params->code,
            'redirect_uri' => $this->apiUrl.SocialAccountServiceInterface::LINKEDIN_CALLBACK_URL,
            'client_id' => $this->linkedinClientId,
            'client_secret' => $this->linkedinClientSecret,
        ];

        $url = self::LINKEDIN_ACCESS_TOKEN.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Linkedin API error: received status code {$statusCode} when requesting access token.", $statusCode);
            }

            $content = $response->toArray();
            if (empty($content)) {
                throw new SocialAccountException('Linkedin API error: received empty response when requesting access token.');
            }

            return $this->denormalizer->denormalize($content, LinkedinAccessToken::class);
        } catch (\Exception) {
            throw new SocialAccountException('Could not retrieve access token from Linkedin API: an exception occurred during the request.');
        }
    }

    public function getLongAccessToken(string $token): AbstractAccessToken
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /**
     * @param LinkedinAccessToken $token
     *
     * @return LinkedinGetAccounts
     */
    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts
    {
        $url = self::LINKEDIN_ACCOUNT;

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => $token->tokenType.' '.$token->token,
                    'Connection' => 'Keep-Alive',
                    'Accept' => 'application / json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new SocialAccountException("Linkedin API error: received status code {$statusCode} when requesting accounts.", $statusCode);
            }

            $accounts = $response->toArray() ?: [];

            if (empty($accounts)) {
                throw new SocialAccountException('Linkedin API error: received empty response when requesting accounts.');
            }

            return new LinkedinGetAccounts(linkedinAccount: $this->denormalizer->denormalize($accounts, LinkedinAccount::class));
        } catch (\Exception $exception) {
            throw new SocialAccountException('Could not retrieve Linkedin accounts: an exception occurred during the request.');
        }
    }
}
