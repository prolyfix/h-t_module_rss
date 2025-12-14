<?php

namespace Prolyfix\RssBundle\Repository;

use Prolyfix\RssBundle\Entity\NewsAiSuggestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NewsAiSuggestion>
 */
class NewsAiSuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsAiSuggestion::class);
    }

    /**
     * Find pending suggestions for review
     */
    public function findPendingSuggestions(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('s.creationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find suggestions for a specific news item
     */
    public function findByNews(int $newsId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.news = :newsId')
            ->setParameter('newsId', $newsId)
            ->orderBy('s.creationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
