<?php

namespace Prolyfix\RssBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * AI Service for analyzing news content and generating knowledge base suggestions
 * Supports OpenAI and Anthropic (Claude) models
 */
class NewsAiAnalyzer
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private const ANTHROPIC_API_URL = 'https://api.anthropic.com/v1/messages';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $aiProvider = 'openai', // 'openai' or 'anthropic'
        private readonly string $aiApiKey = '',
        private readonly string $aiModel = 'gpt-4', // or 'claude-3-5-sonnet-20241022'
    ) {
    }

    /**
     * Analyze news content and extract actionable instructions
     */
    public function analyzeNewsContent(string $title, string $content): array
    {
        $prompt = $this->buildAnalysisPrompt($title, $content);

        try {
            $response = $this->callAiApi($prompt);
            return $this->parseAiResponse($response);
        } catch (\Exception $e) {
            $this->logger->error('AI Analysis failed', [
                'error' => $e->getMessage(),
                'title' => $title
            ]);
            throw $e;
        }
    }

    /**
     * Find matching knowledge base articles based on extracted instructions
     */
    public function findMatchingKnowledgeBase(string $instructions, array $knowledgeBaseArticles): ?array
    {
        $prompt = $this->buildMatchingPrompt($instructions, $knowledgeBaseArticles);

        try {
            $response = $this->callAiApi($prompt);
            return $this->parseMatchingResponse($response);
        } catch (\Exception $e) {
            $this->logger->error('AI Matching failed', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate content for knowledge base article using template
     */
    public function generateKnowledgeBaseContent(
        string $instructions,
        ?string $template = null,
        ?string $existingContent = null
    ): array {
        $prompt = $this->buildContentGenerationPrompt($instructions, $template, $existingContent);

        try {
            $response = $this->callAiApi($prompt);
            return $this->parseContentGenerationResponse($response);
        } catch (\Exception $e) {
            $this->logger->error('AI Content Generation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Build prompt for analyzing news content
     */
    private function buildAnalysisPrompt(string $title, string $content): string
    {
        return <<<PROMPT
You are an AI assistant helping to analyze company news and extract actionable work instructions.

News Title: {$title}
News Content: {$content}

Your task:
1. Identify if this news contains actionable work instructions or procedure changes
2. Extract specific instructions (e.g., "Say Hello Mr" instead of "Good Morning Sir")
3. Determine the topic/category (e.g., phone procedures, customer service, etc.)
4. Assess if this relates to existing procedures (update) or new procedures (create)

Respond in JSON format:
{
  "has_instructions": true/false,
  "instructions": "extracted specific instructions",
  "category": "suggested category name",
  "topic_keywords": ["keyword1", "keyword2"],
  "instruction_type": "procedure_change" | "new_procedure" | "general_info",
  "confidence": 0.0-1.0,
  "reasoning": "brief explanation of your analysis"
}
PROMPT;
    }

    /**
     * Build prompt for matching knowledge base articles
     */
    private function buildMatchingPrompt(string $instructions, array $knowledgeBaseArticles): string
    {
        $articlesJson = json_encode($knowledgeBaseArticles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
You are an AI assistant helping to match new instructions with existing knowledge base articles.

New Instructions: {$instructions}

Existing Knowledge Base Articles:
{$articlesJson}

Your task:
1. Find the most relevant existing article(s) that should be updated
2. Calculate confidence score for each match
3. Recommend whether to update existing article or create new one

Respond in JSON format:
{
  "action": "update" | "create",
  "matched_article_id": 123 (or null if create),
  "matched_article_name": "Article Name",
  "confidence": 0.0-1.0,
  "reasoning": "why this article matches or why new article needed"
}
PROMPT;
    }

    /**
     * Build prompt for generating knowledge base content
     */
    private function buildContentGenerationPrompt(
        string $instructions,
        ?string $template,
        ?string $existingContent
    ): string {
        $templateSection = $template
            ? "Use this template structure:\n{$template}\n\n"
            : "";

        $existingSection = $existingContent
            ? "Existing Content to Update:\n{$existingContent}\n\n"
            : "This is a new article.\n\n";

        return <<<PROMPT
You are an AI assistant helping to create or update knowledge base articles (Wissensdatenbank).

Instructions to incorporate: {$instructions}

{$templateSection}{$existingSection}

Your task:
1. Generate a clear, professional title
2. Write comprehensive content following German medical practice standards
3. Structure the content clearly with sections if needed
4. Ensure the specific instructions are clearly highlighted
5. If updating: integrate new instructions while preserving relevant existing content

Respond in JSON format:
{
  "title": "Suggested Article Title",
  "content": "Full article content in HTML format",
  "summary": "Brief summary of changes made",
  "sections": ["Section 1", "Section 2"]
}
PROMPT;
    }

    /**
     * Call AI API (OpenAI or Anthropic)
     */
    private function callAiApi(string $prompt): string
    {
        if ($this->aiProvider === 'anthropic') {
            return $this->callAnthropicApi($prompt);
        }

        return $this->callOpenAiApi($prompt);
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAiApi(string $prompt): string
    {
        $response = $this->httpClient->request('POST', self::OPENAI_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->aiModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that analyzes business communications and generates structured knowledge base content. Always respond in valid JSON format.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object']
            ],
        ]);

        $data = $response->toArray();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Call Anthropic API
     */
    private function callAnthropicApi(string $prompt): string
    {
        $response = $this->httpClient->request('POST', self::ANTHROPIC_API_URL, [
            'headers' => [
                'x-api-key' => $this->aiApiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->aiModel,
                'max_tokens' => 4096,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
            ],
        ]);

        $data = $response->toArray();
        return $data['content'][0]['text'] ?? '';
    }

    /**
     * Parse AI response for news analysis
     */
    private function parseAiResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (!$data) {
            throw new \RuntimeException('Invalid AI response format');
        }

        return [
            'has_instructions' => $data['has_instructions'] ?? false,
            'instructions' => $data['instructions'] ?? '',
            'category' => $data['category'] ?? '',
            'keywords' => $data['topic_keywords'] ?? [],
            'type' => $data['instruction_type'] ?? 'general_info',
            'confidence' => $data['confidence'] ?? 0.0,
            'reasoning' => $data['reasoning'] ?? '',
        ];
    }

    /**
     * Parse AI response for matching
     */
    private function parseMatchingResponse(string $response): ?array
    {
        $data = json_decode($response, true);

        if (!$data) {
            return null;
        }

        return [
            'action' => $data['action'] ?? 'create',
            'matched_id' => $data['matched_article_id'] ?? null,
            'matched_name' => $data['matched_article_name'] ?? null,
            'confidence' => $data['confidence'] ?? 0.0,
            'reasoning' => $data['reasoning'] ?? '',
        ];
    }

    /**
     * Parse AI response for content generation
     */
    private function parseContentGenerationResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (!$data) {
            throw new \RuntimeException('Invalid AI response format');
        }

        return [
            'title' => $data['title'] ?? '',
            'content' => $data['content'] ?? '',
            'summary' => $data['summary'] ?? '',
            'sections' => $data['sections'] ?? [],
        ];
    }
}
