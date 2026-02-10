<?php

namespace Prolyfix\RssBundle\Entity;

use Prolyfix\HolidayAndTime\Entity\TimeData;
use Prolyfix\RssBundle\Repository\RssFeedListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RssFeedListRepository::class)]
class RssFeedList extends TimeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feedName = null;

    /**
     * @var Collection<int, RssFeedEntry>
     */
    #[ORM\OneToMany(targetEntity: RssFeedEntry::class, mappedBy: 'rssFeedList', orphanRemoval: true)]
    private Collection $rssFeedEntries;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct()
    {
        $this->rssFeedEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeedName(): ?string
    {
        return $this->feedName;
    }

    public function setFeedName(?string $feedName): static
    {
        $this->feedName = $feedName;

        return $this;
    }

    /**
     * @return Collection<int, RssFeedEntry>
     */
    public function getRssFeedEntries(): Collection
    {
        return $this->rssFeedEntries;
    }

    public function addRssFeedEntry(RssFeedEntry $rssFeedEntry): static
    {
        if (!$this->rssFeedEntries->contains($rssFeedEntry)) {
            $this->rssFeedEntries->add($rssFeedEntry);
            $rssFeedEntry->setRssFeedList($this);
        }

        return $this;
    }

    public function removeRssFeedEntry(RssFeedEntry $rssFeedEntry): static
    {
        if ($this->rssFeedEntries->removeElement($rssFeedEntry)) {
            // set the owning side to null (unless already changed)
            if ($rssFeedEntry->getRssFeedList() === $this) {
                $rssFeedEntry->setRssFeedList(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
