<?php

namespace Prolyfix\RssBundle\Entity;

use App\Entity\TimeData;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\RssBundle\Repository\NewsAiSuggestionRepository;

/**
 * Stores AI-generated suggestions for knowledge base updates based on news content
 */
#[ORM\Entity(repositoryClass: NewsAiSuggestionRepository::class)]
class NewsAiSuggestion extends TimeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: News::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?News $news = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $extractedInstructions = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $suggestedTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $suggestedContent = null;

    #[ORM\Column(length: 50)]
    private ?string $suggestionType = null; // 'update' or 'create'

    #[ORM\Column(nullable: true)]
    private ?int $matchedKnowledgebaseId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $matchedKnowledgebaseName = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $matchConfidence = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending'; // pending, approved, rejected, applied

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $categoryName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $templateUsed = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $aiMetadata = null; // Store AI reasoning, model used, etc.

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $appliedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNews(): ?News
    {
        return $this->news;
    }

    public function setNews(?News $news): static
    {
        $this->news = $news;
        return $this;
    }

    public function getExtractedInstructions(): ?string
    {
        return $this->extractedInstructions;
    }

    public function setExtractedInstructions(string $extractedInstructions): static
    {
        $this->extractedInstructions = $extractedInstructions;
        return $this;
    }

    public function getSuggestedTitle(): ?string
    {
        return $this->suggestedTitle;
    }

    public function setSuggestedTitle(?string $suggestedTitle): static
    {
        $this->suggestedTitle = $suggestedTitle;
        return $this;
    }

    public function getSuggestedContent(): ?string
    {
        return $this->suggestedContent;
    }

    public function setSuggestedContent(?string $suggestedContent): static
    {
        $this->suggestedContent = $suggestedContent;
        return $this;
    }

    public function getSuggestionType(): ?string
    {
        return $this->suggestionType;
    }

    public function setSuggestionType(string $suggestionType): static
    {
        $this->suggestionType = $suggestionType;
        return $this;
    }

    public function getMatchedKnowledgebaseId(): ?int
    {
        return $this->matchedKnowledgebaseId;
    }

    public function setMatchedKnowledgebaseId(?int $matchedKnowledgebaseId): static
    {
        $this->matchedKnowledgebaseId = $matchedKnowledgebaseId;
        return $this;
    }

    public function getMatchedKnowledgebaseName(): ?string
    {
        return $this->matchedKnowledgebaseName;
    }

    public function setMatchedKnowledgebaseName(?string $matchedKnowledgebaseName): static
    {
        $this->matchedKnowledgebaseName = $matchedKnowledgebaseName;
        return $this;
    }

    public function getMatchConfidence(): ?float
    {
        return $this->matchConfidence;
    }

    public function setMatchConfidence(?float $matchConfidence): static
    {
        $this->matchConfidence = $matchConfidence;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): static
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    public function getTemplateUsed(): ?string
    {
        return $this->templateUsed;
    }

    public function setTemplateUsed(?string $templateUsed): static
    {
        $this->templateUsed = $templateUsed;
        return $this;
    }

    public function getAiMetadata(): ?array
    {
        return $this->aiMetadata;
    }

    public function setAiMetadata(?array $aiMetadata): static
    {
        $this->aiMetadata = $aiMetadata;
        return $this;
    }

    public function getAppliedAt(): ?\DateTimeImmutable
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(?\DateTimeImmutable $appliedAt): static
    {
        $this->appliedAt = $appliedAt;
        return $this;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
