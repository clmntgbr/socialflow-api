<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateLinkedinAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\OrganizationRepository;
use App\Repository\SocialAccount\LinkedinSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final class CreateOrUpdateLinkedinAccountHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private OrganizationRepository $organizationRepository,
        private LinkedinSocialAccountRepository $LinkedinSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreateOrUpdateLinkedinAccount $message): ?Uuid
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['id' => (string) $message->userId]);

        if (null === $user) {
            throw new \Exception(sprintf('User does not exist with id [%s]', (string) $message->userId));
        }

        /** @var ?Organization $organization */
        $organization = $this->organizationRepository->findOneBy(['id' => (string) $message->organizationId]);

        if (null === $organization) {
            throw new \Exception(sprintf('Organization does not exist with id [%s]', (string) $message->organizationId));
        }

        $linkedinAccount = $this->getlinkedinAccount($message, $organization);

        $date = new \DateTime();
        $date->modify(sprintf('+%s seconds', $message->linkedinToken->expiresIn));

        $linkedinAccount
            ->setUsername($message->linkedinAccount->name)
            ->setSocialAccountId($message->linkedinAccount->id)
            ->setOrganization($organization)
            ->setName($message->linkedinAccount->familyName.' '.$message->linkedinAccount->givenName)
            ->setIsVerified($message->linkedinAccount->verified)
            ->setEmail($message->linkedinAccount->email)
            ->setAvatarUrl($message->linkedinAccount->picture)
            ->setExpireAt($date)
            ->setToken($message->linkedinToken->token);

        $this->LinkedinSocialAccountRepository->save($linkedinAccount, true);

        if ($linkedinAccount->getStatus()->getValue() !== SocialAccountStatus::PENDING_VALIDATION->getValue()) {
            return null;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $linkedinAccount->getId(), status: SocialAccountStatus::PENDING_VALIDATION), [
            new DelayStamp(3600000),
            new AmqpStamp('async'),
        ]);

        return $linkedinAccount->getId();
    }

    private function getLinkedinAccount(CreateOrUpdateLinkedinAccount $message, Organization $organization): LinkedinSocialAccount
    {
        /** @var ?LinkedinSocialAccount $linkedinAccount */
        $linkedinAccount = $this->LinkedinSocialAccountRepository->findOneBy([
            'organization' => $organization,
            'socialAccountId' => $message->linkedinAccount->id,
        ]);

        if (null === $linkedinAccount) {
            return new LinkedinSocialAccount($message->accountId);
        }

        if ($linkedinAccount->getStatus()->getValue() === SocialAccountStatus::EXPIRED->getValue()) {
            $linkedinAccount->setStatus(SocialAccountStatus::ACTIVE->getValue());
        }

        return $linkedinAccount;
    }
}
