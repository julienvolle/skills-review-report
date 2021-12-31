<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Framework;
use App\Entity\UserFramework;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class UserFrameworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFramework::class);
    }

    /**
     * Find user rights for a framework
     *
     * @throws NonUniqueResultException
     */
    public function findByUserAndFramework(Framework $framework, UserInterface $user): ?UserFramework
    {
        if (!$framework->getId()) {
            return null; // It's a new framework
        }

        return $this->createQueryBuilder('uf')
            ->select(['uf'])
            ->where('uf.user = :user')
            ->andWhere('uf.framework = :framework')
            ->setParameters([
                'user'      => $user,
                'framework' => $framework,
            ])
            ->getQuery()
            ->setQueryCacheLifetime(86400) // TTL 24h
            ->getOneOrNullResult()
        ;
    }
}
