<?php

namespace Prolyfix\RssBundle\Repository;

use Prolyfix\RssBundle\Entity\RssFeedEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Prolyfix\RssBundle\Entity\RssFeedList;

/**
 * @extends ServiceEntityRepository<RssFeedEntry>
 */
class RssFeedEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RssFeedEntry::class);
    }

    public function findLastWeekEntries(RssFeedList $feed)
    {
        $date = new \DateTime();
        $date->modify('-7 days');

        return $this->createQueryBuilder('r')
            ->andWhere('r.publishedAt >= :date')
            ->andWhere('r.rssFeedList = :feed')
            ->setParameter('date', $date)
            ->setParameter('feed', $feed)
            ->orderBy('r.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return RssFeedEntry[] Returns an array of RssFeedEntry objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RssFeedEntry
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
