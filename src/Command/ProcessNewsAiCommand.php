<?php

namespace Prolyfix\RssBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\RssBundle\Entity\News;
use Prolyfix\RssBundle\Service\NewsKnowledgeBaseProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:process-news-ai',
    description: 'Process news articles with AI to generate knowledge base suggestions',
)]
class ProcessNewsAiCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NewsKnowledgeBaseProcessor $processor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('news-id', null, InputOption::VALUE_OPTIONAL, 'Process specific news article by ID')
            ->addOption('days', null, InputOption::VALUE_OPTIONAL, 'Process news from last N days', 7)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum number of news to process', 10)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Process even if suggestions already exist')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $newsId = $input->getOption('news-id');
        $days = (int) $input->getOption('days');
        $limit = (int) $input->getOption('limit');
        $force = $input->getOption('force');

        if ($newsId) {
            return $this->processSpecificNews($io, (int) $newsId);
        }

        return $this->processRecentNews($io, $days, $limit, $force);
    }

    private function processSpecificNews(SymfonyStyle $io, int $newsId): int
    {
        $news = $this->entityManager->getRepository(News::class)->find($newsId);

        if (!$news) {
            $io->error(sprintf('News with ID %d not found', $newsId));
            return Command::FAILURE;
        }

        $io->title(sprintf('Processing News #%d: %s', $newsId, $news->getTitle()));

        try {
            $suggestion = $this->processor->processNews($news);

            if ($suggestion) {
                $io->success([
                    sprintf('Suggestion created successfully (ID: %d)', $suggestion->getId()),
                    sprintf('Type: %s', $suggestion->getSuggestionType()),
                    sprintf('Category: %s', $suggestion->getCategoryName()),
                    sprintf('Confidence: %.2f', $suggestion->getMatchConfidence() ?? 0),
                ]);
            } else {
                $io->info('No actionable instructions found in this news article');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Processing failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function processRecentNews(SymfonyStyle $io, int $days, int $limit, bool $force): int
    {
        $io->title(sprintf('Processing news from last %d days (limit: %d)', $days, $limit));

        $qb = $this->entityManager->getRepository(News::class)->createQueryBuilder('n');
        $qb->where('n.creationDate >= :date')
            ->setParameter('date', new \DateTime(sprintf('-%d days', $days)))
            ->orderBy('n.creationDate', 'DESC')
            ->setMaxResults($limit);

        // Exclude news that already have suggestions (unless force)
        if (!$force) {
            $qb->andWhere('NOT EXISTS (
                SELECT 1 FROM Prolyfix\RssBundle\Entity\NewsAiSuggestion s
                WHERE s.news = n
            )');
        }

        $newsList = $qb->getQuery()->getResult();

        if (empty($newsList)) {
            $io->info('No news articles to process');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Found %d news article(s) to process', count($newsList)));
        $io->newLine();

        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $skipped = 0;

        $progressBar = $io->createProgressBar(count($newsList));
        $progressBar->start();

        foreach ($newsList as $news) {
            $processed++;

            try {
                $suggestion = $this->processor->processNews($news);

                if ($suggestion) {
                    $succeeded++;
                    $io->writeln('');
                    $io->success([
                        sprintf('[%d/%d] News #%d: %s', $processed, count($newsList), $news->getId(), $news->getTitle()),
                        sprintf('  → Suggestion ID: %d', $suggestion->getId()),
                        sprintf('  → Type: %s', $suggestion->getSuggestionType()),
                        sprintf('  → Confidence: %.2f', $suggestion->getMatchConfidence() ?? 0),
                    ]);
                } else {
                    $skipped++;
                    $io->writeln('');
                    $io->text(sprintf('[%d/%d] News #%d: No instructions found (skipped)', 
                        $processed, count($newsList), $news->getId()));
                }

            } catch (\Exception $e) {
                $failed++;
                $io->writeln('');
                $io->error(sprintf('[%d/%d] News #%d: Failed - %s', 
                    $processed, count($newsList), $news->getId(), $e->getMessage()));
            }

            $progressBar->advance();
            $io->newLine();
        }

        $progressBar->finish();
        $io->newLine(2);

        // Summary
        $io->section('Processing Summary');
        $io->table(
            ['Status', 'Count'],
            [
                ['Processed', $processed],
                ['Suggestions Created', $succeeded],
                ['Skipped (no instructions)', $skipped],
                ['Failed', $failed],
            ]
        );

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
