<?php

namespace App\Service\SocialAccount;

use App\Application\Command\CreateOrUpdateFacebookAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\SocialAccount\FacebookAccount;
use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetAccounts\FacebookGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\SocialAccount\SocialAccountActivate;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessToken\FacebookAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\FacebookAccessTokenParameters;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Enum\SocialAccountStatus;
use App\Exception\MethodNotImplementedException;
use App\Exception\SocialAccountException;
use App\Repository\SocialAccount\SocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SocialAccountService
{
    public function __construct(
        private SocialAccountRepository $socialAccountRepository,
        private Security $security,
    ) {
    }

    /**
     * @param SocialAccountActivate[] $socialAccountActivates
     */
    public function activate(array $socialAccountActivates): void
    {
        foreach ($socialAccountActivates as $socialAccountActivate) {
            /** @var SocialAccount $socialAccount */
            $socialAccount = $this->socialAccountRepository->find($socialAccountActivate->id);

            if (null === $socialAccount) {
                continue;
            }

            /** @var User $user */
            $user = $this->security->getUser();

            if (!$user->isSocialAccountInActiveGroup($socialAccount)) {
                continue;
            }

            if ($socialAccountActivate->status === SocialAccountStatus::ACTIVE->value) {
                $socialAccount->markAsActive();
                $this->socialAccountRepository->save($socialAccount, true);
                continue;
            }

            if ($socialAccountActivate->status === SocialAccountStatus::REMOVE->value) {
                $this->socialAccountRepository->delete($socialAccount);
            }
        }
    }
}
