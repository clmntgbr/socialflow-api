<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateFacebookAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Group;
use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Entity\SocialAccount\TokenSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Exception\GroupNotFoundException;
use App\Repository\GroupRepository;
use App\Repository\SocialAccount\FacebookSocialAccountRepository;
use App\Repository\SocialAccount\TokenSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class CreateOrUpdateFacebookAccountHandler extends CreateOrUpdateAccountHandlerAbstract
{
    public function __construct(
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private TokenSocialAccountRepository $tokenSocialAccountRepository,
        private FacebookSocialAccountRepository $facebookSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($facebookSocialAccountRepository);
    }

    public function __invoke(CreateOrUpdateFacebookAccount $message): void
    {
        /** @var ?Group $group */
        $group = $this->groupRepository->findOneBy(['id' => (string) $message->groupId]);

        if (null === $group) {
            throw new GroupNotFoundException((string) $message->groupId);
        }

        /** @var FacebookSocialAccount $facebookAccount */
        $facebookAccount = $this->getAccount(
            socialAccountId: $message->facebookAccount->id,
            group: $group,
            class: FacebookSocialAccount::class
        );

        $date = new \DateTime();
        $date->modify('+60 days');

        $token = $this->tokenSocialAccountRepository->findOneBy(['socialAccountId' => $message->facebookAccount->id]);

        if (null === $token) {
            $token = new TokenSocialAccount();
        }

        $token
            ->setSocialAccountId($message->facebookAccount->id)
            ->setExpireAt($date)
            ->setToken($message->facebookToken->token);

        $facebookAccount
            ->setLink($message->facebookAccount->link)
            ->setUsername($message->facebookAccount->username)
            ->setSocialAccountId($message->facebookAccount->id)
            ->setGroup($group)
            ->setFollowers($message->facebookAccount->followers)
            ->setFollowings($message->facebookAccount->followings)
            ->setWebsite($message->facebookAccount->website)
            ->setEmail($message->facebookAccount->email)
            ->setTokenSocialAccount($token)
            ->setAvatarUrl($message->facebookAccount->picture);

        $this->facebookSocialAccountRepository->save($facebookAccount, true);

        if ($facebookAccount->getStatus() !== SocialAccountStatus::PENDING_VALIDATION->value) {
            return;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $facebookAccount->getId(), status: SocialAccountStatus::PENDING_VALIDATION), [
            new DelayStamp(360000000),
            new AmqpStamp('async-low'),
        ]);
    }
}
