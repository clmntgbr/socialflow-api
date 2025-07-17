<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Group;
use App\Entity\User;
use App\Exception\AuthenticationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class GroupExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhereToCollection($queryBuilder, $resourceClass);
    }

    /**
     * @throws \Exception
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhereToItem($queryBuilder, $resourceClass);
    }

    /**
     * @throws \Exception
     */
    private function addWhereToCollection(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Group::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('You have to be authenticated.');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->join(sprintf('%s.members', $rootAlias), 'u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $user->getId());
    }

    /**
     * @throws \Exception
     */
    private function addWhereToItem(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Group::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AuthenticationException('You must be authenticated to access this group.', null);
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere($rootAlias.'.uuid = :uuid')
            ->setParameter('uuid', $user->getActiveGroup()->getId());
    }
}
