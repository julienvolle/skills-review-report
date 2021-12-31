<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Interview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class InterviewRepository extends ServiceEntityRepository
{
    protected Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Interview::class);

        $this->security = $security;
    }

    /**
     * Optimize native findAll() request
     *
     * @param string|null $attributes To filter result based on user rights
     *
     * @return array
     */
    public function findAll(?string $attributes = null): array
    {
        $result = $this->createQueryBuilder('i')
            ->select(['i', 'f', 'l', 'c', 's'])
            ->leftJoin('i.framework', 'f')
            ->leftJoin('f.levels', 'l')
            ->leftJoin('f.categories', 'c')
            ->leftJoin('c.skills', 's')
            ->getQuery()
            ->setQueryCacheLifetime(86400) // TTL 24h
            ->getResult()
        ;

        if ($attributes) {
            $result = \array_filter($result, function (Interview $interview) use ($attributes) {
                return $this->security->isGranted($attributes, $interview);
            });
        }

        return $result;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByGuid(string $guid): ?Interview
    {
        return $this->createQueryBuilder('i')
            ->select(['i', 'f', 'l', 'c', 's'])
            ->leftJoin('i.framework', 'f')
            ->leftJoin('f.levels', 'l')
            ->leftJoin('f.categories', 'c')
            ->leftJoin('c.skills', 's')
            ->where('i.guid = :interview_guid')
            ->setParameter('interview_guid', $guid)
            ->getQuery()
            ->setQueryCacheLifetime(86400) // TTL 24h
            ->getOneOrNullResult()
        ;
    }
}
