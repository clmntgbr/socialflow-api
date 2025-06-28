<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\SocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\NilUuid;

/**
 * @extends AbstractRepository<SocialAccount>
 */
class SocialAccountRepository extends AbstractRepository implements SocialAccountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialAccount::class);
    }

    /**
     * @return \Generator<SocialAccount>
     */
    public function findSocialAccountsToValidate(): \Generator
    {
        $qb = $this
            ->createQueryBuilder('sa')
            ->where('sa.status = :status')
            ->andWhere('sa.id > :lastId')
            ->orderBy('sa.id', 'ASC')
            ->setMaxResults(100);

        $query = $qb->getQuery();

        do {
            $parameters = [
                'lastId' => $lastId ?? new NilUuid(),
                'status' => SocialAccountStatus::PENDING_VALIDATION->value,
            ];

            /** @var SocialAccount[] $results */
            $results = $query->execute($parameters);

            foreach ($results as $socialAccount) {
                $lastId = $socialAccount->getId();
                yield $socialAccount;
            }
        } while (false === empty($results));
    }
}
