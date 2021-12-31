<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Framework;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class FrameworkRepository extends ServiceEntityRepository
{
    protected Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Framework::class);

        $this->security = $security;
    }

    /**
     * Optimize native findAll() request
     *
     * @param string|null $attributes To filter result based on user rights
     *
     * @return array|Framework[]
     */
    public function findAll(?string $attributes = null): array
    {
        $result = $this->createQueryBuilder('f')
            ->select(['f', 'l', 'c', 's'])
            ->leftJoin('f.levels', 'l')
            ->leftJoin('f.categories', 'c')
            ->leftJoin('c.skills', 's')
            ->getQuery()
            ->setQueryCacheLifetime(86400) // TTL 24h
            ->getResult()
        ;

        if ($attributes) {
            $result = \array_filter($result, function (Framework $framework) use ($attributes) {
                return $this->security->isGranted($attributes, $framework);
            });
        }

        return $result;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByGuid(string $guid): ?Framework
    {
        return $this->createQueryBuilder('f')
            ->select(['f', 'l', 'c', 's'])
            ->leftJoin('f.levels', 'l')
            ->leftJoin('f.categories', 'c')
            ->leftJoin('c.skills', 's')
            ->where('f.guid = :framework_guid')
            ->setParameter('framework_guid', $guid)
            ->getQuery()
            ->setQueryCacheLifetime(86400) // TTL 24h
            ->getOneOrNullResult()
        ;
    }

    /**
     * Check if a framework is used by an interview
     */
    public function isUsed(Framework $framework): bool
    {
        try {
            return (bool) $this->createQueryBuilder('f')
                ->select('COUNT(f)')
                ->innerJoin('f.interviews', 'i')
                ->where('f.id = :framework_id')
                ->setParameter('framework_id', $framework->getId())
                ->getQuery()
                ->setQueryCacheLifetime(86400) // TTL 24h
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return false;
        } catch (NonUniqueResultException $e) {
            return true;
        }
    }
}
