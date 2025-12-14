<?php

namespace Prolyfix\RssBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\KnowledgebaseBundle\Entity\Knowledgebase;
use Prolyfix\KnowledgebaseBundle\Entity\Category;
use Prolyfix\RssBundle\Entity\News;
use Prolyfix\RssBundle\Entity\NewsAiSuggestion;
use Psr\Log\LoggerInterface;

/**
 * Service for processing news articles and generating knowledge base suggestions
 */
class NewsKnowledgeBaseProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NewsAiAnalyzer $aiAnalyzer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Process a news article and generate knowledge base suggestions
     */
    public function processNews(News $news): ?NewsAiSuggestion
    {
        try {
            // Step 1: Analyze news content
            $this->logger->info('Analyzing news content', ['news_id' => $news->getId()]);
            $analysis = $this->aiAnalyzer->analyzeNewsContent(
                $news->getTitle() ?? '',
                $news->getContent() ?? ''
            );

            // Check if news contains actionable instructions
            if (!$analysis['has_instructions'] || $analysis['confidence'] < 0.5) {
                $this->logger->info('No actionable instructions found', [
                    'news_id' => $news->getId(),
                    'confidence' => $analysis['confidence']
                ]);
                return null;
            }

            // Step 2: Find matching knowledge base articles
            $knowledgeBaseArticles = $this->getKnowledgeBaseArticlesForMatching($analysis['keywords']);
            $match = $this->aiAnalyzer->findMatchingKnowledgeBase(
                $analysis['instructions'],
                $knowledgeBaseArticles
            );

            // Step 3: Generate suggested content
            $template = $this->getTemplateForCategory($analysis['category']);
            $existingContent = null;

            if ($match && $match['action'] === 'update' && $match['matched_id']) {
                $existingArticle = $this->entityManager
                    ->getRepository(Knowledgebase::class)
                    ->find($match['matched_id']);
                $existingContent = $existingArticle?->getDescription();
            }

            $generatedContent = $this->aiAnalyzer->generateKnowledgeBaseContent(
                $analysis['instructions'],
                $template,
                $existingContent
            );

            // Step 4: Create suggestion entity
            $suggestion = new NewsAiSuggestion();
            $suggestion->setNews($news);
            $suggestion->setExtractedInstructions($analysis['instructions']);
            $suggestion->setSuggestedTitle($generatedContent['title']);
            $suggestion->setSuggestedContent($generatedContent['content']);
            $suggestion->setSuggestionType($match['action'] ?? 'create');
            $suggestion->setMatchedKnowledgebaseId($match['matched_id'] ?? null);
            $suggestion->setMatchedKnowledgebaseName($match['matched_name'] ?? null);
            $suggestion->setMatchConfidence($match['confidence'] ?? 0.0);
            $suggestion->setCategoryName($analysis['category']);
            $suggestion->setTemplateUsed($template);
            $suggestion->setAiMetadata([
                'analysis' => $analysis,
                'match' => $match,
                'generation' => $generatedContent,
                'model_used' => 'configured_ai_model',
                'processed_at' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);

            $this->entityManager->persist($suggestion);
            $this->entityManager->flush();

            $this->logger->info('AI suggestion created', [
                'news_id' => $news->getId(),
                'suggestion_id' => $suggestion->getId(),
                'type' => $suggestion->getSuggestionType()
            ]);

            return $suggestion;

        } catch (\Exception $e) {
            $this->logger->error('News processing failed', [
                'news_id' => $news->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Apply an approved suggestion to knowledge base
     */
    public function applySuggestion(NewsAiSuggestion $suggestion): Knowledgebase
    {
        if (!$suggestion->isApproved()) {
            throw new \RuntimeException('Suggestion must be approved before applying');
        }

        if ($suggestion->getSuggestionType() === 'update') {
            return $this->updateKnowledgeBase($suggestion);
        }

        return $this->createKnowledgeBase($suggestion);
    }

    /**
     * Update existing knowledge base article
     */
    private function updateKnowledgeBase(NewsAiSuggestion $suggestion): Knowledgebase
    {
        $article = $this->entityManager
            ->getRepository(Knowledgebase::class)
            ->find($suggestion->getMatchedKnowledgebaseId());

        if (!$article) {
            throw new \RuntimeException('Knowledge base article not found');
        }

        $article->setName($suggestion->getSuggestedTitle());
        $article->setDescription($suggestion->getSuggestedContent());

        $suggestion->setStatus('applied');
        $suggestion->setAppliedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->logger->info('Knowledge base updated', [
            'kb_id' => $article->getId(),
            'suggestion_id' => $suggestion->getId()
        ]);

        return $article;
    }

    /**
     * Create new knowledge base article
     */
    private function createKnowledgeBase(NewsAiSuggestion $suggestion): Knowledgebase
    {
        $article = new Knowledgebase();
        $article->setName($suggestion->getSuggestedTitle());
        $article->setDescription($suggestion->getSuggestedContent());

        // Find or create category
        $category = $this->findOrCreateCategory($suggestion->getCategoryName());
        $article->setCategory($category);

        $this->entityManager->persist($article);

        $suggestion->setStatus('applied');
        $suggestion->setAppliedAt(new \DateTimeImmutable());
        $suggestion->setMatchedKnowledgebaseId($article->getId());

        $this->entityManager->flush();

        $this->logger->info('Knowledge base created', [
            'kb_id' => $article->getId(),
            'suggestion_id' => $suggestion->getId()
        ]);

        return $article;
    }

    /**
     * Find or create category
     */
    private function findOrCreateCategory(string $categoryName): Category
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);

        if (!$category) {
            $category = new Category();
            $category->setName($categoryName);
            $this->entityManager->persist($category);
        }

        return $category;
    }

    /**
     * Get knowledge base articles for matching
     */
    private function getKnowledgeBaseArticlesForMatching(array $keywords): array
    {
        $qb = $this->entityManager
            ->getRepository(Knowledgebase::class)
            ->createQueryBuilder('k')
            ->select('k.id, k.name, k.description')
            ->setMaxResults(20);

        // Add keyword filtering if provided
        if (!empty($keywords)) {
            $conditions = [];
            foreach ($keywords as $i => $keyword) {
                $conditions[] = 'k.name LIKE :keyword' . $i . ' OR k.description LIKE :keyword' . $i;
                $qb->setParameter('keyword' . $i, '%' . $keyword . '%');
            }
            $qb->where(implode(' OR ', $conditions));
        }

        $results = $qb->getQuery()->getArrayResult();

        return array_map(function($article) {
            return [
                'id' => $article['id'],
                'name' => $article['name'],
                'description' => mb_substr(strip_tags($article['description'] ?? ''), 0, 200) . '...'
            ];
        }, $results);
    }

    /**
     * Get template for specific category (Arbeitsanweisung, etc.)
     */
    private function getTemplateForCategory(string $category): ?string
    {
        // Define templates for different categories
        $templates = [
            'Arbeitsanweisung' => <<<TEMPLATE
<h2>Arbeitsanweisung: [Titel]</h2>

<h3>1. Zweck und Ziel</h3>
<p>[Beschreibung des Zwecks dieser Anweisung]</p>

<h3>2. Geltungsbereich</h3>
<p>[Für wen gilt diese Anweisung]</p>

<h3>3. Durchführung</h3>
<ol>
    <li>[Schritt 1]</li>
    <li>[Schritt 2]</li>
    <li>[Schritt 3]</li>
</ol>

<h3>4. Wichtige Hinweise</h3>
<ul>
    <li>[Hinweis 1]</li>
    <li>[Hinweis 2]</li>
</ul>

<h3>5. Verantwortlichkeiten</h3>
<p>[Wer ist verantwortlich]</p>

<h3>6. Dokumentation</h3>
<p>[Wie wird dokumentiert]</p>
TEMPLATE,

            'Telefonannahme' => <<<TEMPLATE
<h2>Telefonannahme: [Titel]</h2>

<h3>Begrüßung</h3>
<p>[Standardbegrüßung]</p>

<h3>Gesprächsführung</h3>
<ol>
    <li>[Schritt 1]</li>
    <li>[Schritt 2]</li>
</ol>

<h3>Verabschiedung</h3>
<p>[Standardverabschiedung]</p>

<h3>Besondere Situationen</h3>
<ul>
    <li>[Situation 1]</li>
    <li>[Situation 2]</li>
</ul>
TEMPLATE,

            'Patientenaufnahme' => <<<TEMPLATE
<h2>Patientenaufnahme: [Titel]</h2>

<h3>Vorbereitung</h3>
<p>[Was ist vorzubereiten]</p>

<h3>Ablauf</h3>
<ol>
    <li>[Schritt 1]</li>
    <li>[Schritt 2]</li>
</ol>

<h3>Dokumentation</h3>
<p>[Was ist zu dokumentieren]</p>

<h3>Nachbereitung</h3>
<p>[Follow-up Schritte]</p>
TEMPLATE,
        ];

        // Find matching template
        foreach ($templates as $templateCategory => $template) {
            if (stripos($category, $templateCategory) !== false) {
                return $template;
            }
        }

        // Default template
        return <<<TEMPLATE
<h2>[Titel]</h2>

<h3>Beschreibung</h3>
<p>[Beschreibung des Themas]</p>

<h3>Anweisungen</h3>
<ol>
    <li>[Anweisung 1]</li>
    <li>[Anweisung 2]</li>
</ol>

<h3>Wichtige Hinweise</h3>
<ul>
    <li>[Hinweis 1]</li>
</ul>
TEMPLATE;
    }
}
