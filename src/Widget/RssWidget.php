<?php 
namespace Prolyfix\RssBundle\Widget;

use App\Widget\WidgetInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\RssBundle\Entity\RssFeedEntry;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment as Twig;

class RssWidget implements WidgetInterface
{
    private EntityManagerInterface $em;
    private Security $security;
    private Twig $twig;

    public static function isGrantedForRole(): string
    {
        return 'ROLE_USER';
    }

    public function getContext(): array
    {
        return [
            'title' => 'RssWidget',
            'content' => 'RssWidget content',
        ];
    }

    public function getTemplate(): string
    {
        return '@ProlyfixRss/widget/rss_widget.html.twig';
    }

    public function getHeight(): int
    {
        return 3;
    }

    public function getWidth(): int
    {
        return 4;
    }   

    public function getName(): string
    {
        return 'RssWidget';
    }

    public function isForThisUserAvailable(): bool
    {
        return true;
    }
    public function __construct(EntityManagerInterface $em, Security $security, Twig $twig)
    {
        $this->em = $em;
        $this->security = $security;
        $this->twig = $twig;
    }

    public function render(): string
    {
        $feedEntries = $this->em->getRepository(RssFeedEntry::class)->findBy([], ['publishedAt' => 'DESC'], 5);
        return 	$this->twig->render('@ProlyfixRss/widget/rss_widget.html.twig',[
            'title' => 'RssWidget',
            'content' => 'RssWidget content',
            'feedEntries' => $feedEntries,
        ])		;
    }
    public static function getModule(): ?string
    {
        return 'RssBundle';
    }
}
