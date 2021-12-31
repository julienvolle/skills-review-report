<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Interview;
use App\Entity\UserInterview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class UserInterviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInterview::class);
    }

    /**
     * Find user rights for an interview
     *
     * @throws NonUniqueResultException
     */
    public function findByUserAndInterview(Interview $interview, UserInterface $user): ?UserInterview
    {
        if (!$interview->getId()) {
            return null; // It's a new interview
        }

        return $this->createQueryBuilder('ui')
            ->where('ui.user = :user')
            ->andWhere('ui.interview = :interview')
            ->setParameters([
                'user'      => $user,
                'interview' => $interview,
            ])
            ->getQuery()
            ->setQueryCacheLifetime(86400) // TTL 24h
            ->getOneOrNullResult()
        ;
    }
}
