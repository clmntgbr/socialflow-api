<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template T of object
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
    /**
     * @param T $entity
     *
     * @return T
     */
    public function refresh(object $entity): object
    {
        $this->getEntityManager()->refresh($entity);

        return $entity;
    }

    /**
     * @param T $entity
     */
    public function delete(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param T $entity
     */
    public function save(object $entity, bool $persist = false): void
    {
        if ($persist) {
            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param T $entity
     *
     * @return T
     */
    public function update(object $entity, array $data): object
    {
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($entity, $method)) {
                $entity->$method($value);
                continue;
            }

            $method = 'add'.ucfirst($key);
            if (method_exists($entity, $method)) {
                $entity->$method($value);
            }
        }

        $this->save($entity);

        return $entity;
    }
}
