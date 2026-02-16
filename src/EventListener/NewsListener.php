<?php
namespace Prolyfix\RssBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\HolidayAndTime\Entity\User;
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
        $news->setRssFeedEntry($feedEntry);
        $activeUsers = $this->entityManager->getRepository(User::class)->findActiveEmployeesOfCompany($news->getCreatedBy()->getCompany());
        $readsStats = [];
        foreach ($activeUsers as $user) {
            $readsStats[$user->getId()] = $user == $news->getCreatedBy() ? 1 : 0;
        }
        $news->setReadsStats($readsStats);
        
        $this->entityManager->persist($feedList);
        $this->entityManager->flush();


    }
}