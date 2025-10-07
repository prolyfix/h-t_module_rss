<?php
namespace Prolyfix\RssBundle\EventListener;

use App\Event\ModifiableArrayEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\RssBundle\Entity\RssFeedEntry;
use Prolyfix\RssBundle\Entity\RssFeedList;

class WeeklyReportListener
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function onAppWeeklyReport(ModifiableArrayEvent $event): void
    {
        $moduleUserTabs = $event->getData();
        $user = $moduleUserTabs['user'];
        $userId = $user->getId();
        $internalFeed = $this->em->getRepository(RssFeedList::class)->findOneBy(['tenant' => $user->getTenant(), 'name' => 'internal']);
        if ($internalFeed) {
            $today = new \DateTime();
            $lastMonday = (clone $today)->modify('last monday')->setTime(0, 0, 0);
            $lastSunday = (clone $lastMonday)->modify('next sunday')->setTime(23, 59, 59);
            $feedEntries = $this->em->getRepository(RssFeedEntry::class)->findLastWeekEntries($internalFeed);
            $moduleUserTabs['feedEntries']['values'] = $feedEntries;
            $moduleUserTabs['feedEntries']['structure'] = [
                'publishedAt' => 'date',
                'title' => 'text',
                'link' => 'link',
            ];
        } else {
            return;
        }
        $event->setData($moduleUserTabs);
    }
}