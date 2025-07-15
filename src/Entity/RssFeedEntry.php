<?php

namespace Prolyfix\RssBundle\Entity;

use Prolyfix\RssBundle\Repository\RssFeedEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RssFeedEntryRepository::class)]
class RssFeedEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 511, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(length: 511, nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'rssFeedEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RssFeedList $rssFeedList = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(length: 511, nullable: true)]
    private ?string $uniqId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getRssFeedList(): ?RssFeedList
    {
        return $this->rssFeedList;
    }

    public function setRssFeedList(?RssFeedList $rssFeedList): static
    {
        $this->rssFeedList = $rssFeedList;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getUniqId(): ?string
    {
        return $this->uniqId;
    }

    public function setUniqId(?string $uniqId): static
    {
        $this->uniqId = $uniqId;

        return $this;
    }

}
