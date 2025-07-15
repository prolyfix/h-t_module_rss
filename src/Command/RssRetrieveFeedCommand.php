<?php

namespace Prolyfix\RssBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Prolyfix\RssBundle\Entity\RssFeedEntry;
use Prolyfix\RssBundle\Entity\RssFeedList;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rss:retrieve-feed',
    description: 'retrieve rss feed',
)]
class RssRetrieveFeedCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $feedsList = $this->em->getRepository(RssFeedList::class)->findAll();

        foreach ($feedsList as $feedList) {
            try{
            $feed = $feedList->getFeedName();
            
            $rss = simplexml_load_file($feed);

            foreach ($rss->channel->item as $item) {
                if($item->guid){
                    if($this->em->getRepository(RssFeedEntry::class)->findOneBy(['uniqId' => $item->guid])){
                        continue;
                    }
                }

                $rssFeedEntry = new RssFeedEntry();
                $rssFeedEntry->setTitle($item->title);
                $rssFeedEntry->setUniqId($item->guid);
                $rssFeedEntry->setDescription($item->description);
                $rssFeedEntry->setLink($item->link);
                $rssFeedEntry->setPublishedAt(new \DateTime($item->pubDate));
                $rssFeedEntry->setRssFeedList($feedList);
                
                $this->em->persist($rssFeedEntry);
            }
            }catch(\Exception $e){
                $io->error($e->getMessage());
            }   

        }
        $this->em->flush();
        $io->success('Feeds have been retrieved');

        return Command::SUCCESS;
    }
}
