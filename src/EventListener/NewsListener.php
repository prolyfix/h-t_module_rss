<?php
namespace Prolyfix\RssBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\RssBundle\Entity\News;
use Prolyfix\RssBundle\Entity\RssFeedEntry;
use Prolyfix\RssBundle\Entity\RssFeedList;

final class NewsListener
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function postPersist(News $news, $event): void
    {
        $company = $news->getTenant();
        $feedList = $this->entityManager->getRepository(RssFeedList::class)->findOneBy(['tenant' => $company,'name'=>'internal']);
        if($feedList == null){
            $feedList = (new RssFeedList())->setName('internal')->setFeedName('internal');
            $this->entityManager->persist($feedList);
            $this->entityManager->flush();
        }

        $feedEntry = new RssFeedEntry();
        $feedEntry->setTitle($news->getTitle());
        $feedEntry->setDescription($news->getContent());
        $feedEntry->setPublishedAt(new \DateTime());
        $feedEntry->setLink('/admin?crudAction=detail&crudControllerFqcn=Prolyfix%5CRssBundle%5CController%5CAdmin%5CNewsCrudController&entityId=' . $news->getId());
        $feedEntry->setRssFeedList($feedList);
        $feedEntry->setCreatedBy($news->getCreatedBy());
        $feedList->addRssFeedEntry($feedEntry);
        $this->entityManager->persist($feedEntry);
        $this->entityManager->persist($feedList);
        $this->entityManager->flush();

    }
}