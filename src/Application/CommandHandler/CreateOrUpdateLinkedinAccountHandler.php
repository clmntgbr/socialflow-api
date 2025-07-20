<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateLinkedinAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Group;
use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Entity\SocialAccount\TokenSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Exception\GroupNotFoundException;
use App\Repository\GroupRepository;
use App\Repository\SocialAccount\LinkedinSocialAccountRepository;
use App\Repository\SocialAccount\TokenSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class CreateOrUpdateLinkedinAccountHandler extends CreateOrUpdateAccountHandlerAbstract
{
    public function __construct(
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private TokenSocialAccountRepository $tokenSocialAccountRepository,
        private LinkedinSocialAccountRepository $LinkedinSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($LinkedinSocialAccountRepository);
    }

    public function __invoke(CreateOrUpdateLinkedinAccount $message): void
    {
        /** @var ?Group $group */
        $group = $this->groupRepository->findOneBy(['id' => (string) $message->groupId]);

        if (null === $group) {
            throw new GroupNotFoundException((string) $message->groupId);
        }

        /** @var LinkedinSocialAccount $linkedinAccount */
        $linkedinAccount = $this->getAccount(
            socialAccountId: $message->linkedinAccount->id,
            group: $group,
            class: LinkedinSocialAccount::class
        );

        $date = new \DateTime();
        $date->modify(sprintf('+%s seconds', $message->linkedinToken->expiresIn));

        $token = $this->tokenSocialAccountRepository->findOneBy(['socialAccountId' => $message->linkedinAccount->id]);

        if (null === $token) {
            $token = new TokenSocialAccount();
        }

        $token
            ->setSocialAccountId($message->linkedinAccount->id)
            ->setExpireAt($date)
            ->setToken($message->linkedinToken->token);

        $linkedinAccount
            ->setUsername($message->linkedinAccount->name)
            ->setSocialAccountId($message->linkedinAccount->id)
            ->setGroup($group)
            ->setName($message->linkedinAccount->familyName.' '.$message->linkedinAccount->givenName)
            ->setIsVerified($message->linkedinAccount->verified)
            ->setEmail($message->linkedinAccount->email)
            ->setTokenSocialAccount($token)
            ->setAvatarUrl($message->linkedinAccount->picture);

        $this->LinkedinSocialAccountRepository->save($linkedinAccount, true);

        if ($linkedinAccount->getStatus() !== SocialAccountStatus::PENDING_ACTIVATION->value) {
            return;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $linkedinAccount->getId(), status: SocialAccountStatus::PENDING_ACTIVATION), [
            new DelayStamp(360000000),
            new AmqpStamp('async-low'),
        ]);
    }
}
