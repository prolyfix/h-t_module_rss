<?php

namespace Prolyfix\RssBundle\Entity;

use Prolyfix\HolidayAndTime\Entity\Commentable;
use Prolyfix\HolidayAndTime\Entity\TimeData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Prolyfix\RssBundle\Repository\NewsRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;
use Dom\Comment;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
#[Vich\Uploadable]
class News extends Commentable
{

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;


    #[ORM\Column(length: 255,nullable:true)]
    private ?string $filename = null;

    #[Vich\UploadableField(mapping: 'medias', fileNameProperty: 'filename')]
    #[Groups(['module_configuration_value:write'])]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $theuid = null;

    #[ORM\Column(nullable: true)]
    private ?array $readBy = null;

    #[ORM\OneToOne(inversedBy: 'news', cascade: ['persist', 'remove'])]
    private ?RssFeedEntry $rssFeedEntry = null;

    #[ORM\Column(nullable: true)]
    private ?array $readsStats = null;

     /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setFile(?File $file = null): void
    {
        $this->file = $file;
        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->theuid = uniqid();
        }

    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }
    public function __construct()
    {
        parent::__construct();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }


    public function getReadBy(): ?array
    {
        return $this->readBy;
    }

    public function setReadBy(?array $readBy): static
    {
        $this->readBy = $readBy;

        return $this;
    }

    public function getRssFeedEntry(): ?RssFeedEntry
    {
        return $this->rssFeedEntry;
    }

    public function setRssFeedEntry(?RssFeedEntry $rssFeedEntry): static
    {
        $this->rssFeedEntry = $rssFeedEntry;

        return $this;
    }

    public function getReadsStats(): ?array
    {
        return $this->readsStats;
    }

    public function setReadsStats(?array $readsStats): static
    {
        $this->readsStats = $readsStats;

        return $this;
    }

}
