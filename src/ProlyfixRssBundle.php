<?php

namespace Prolyfix\RssBundle;

use App\Entity\Module\ModuleConfiguration;
use App\Entity\Module\ModuleRight;
use App\Module\ModuleBundle;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Prolyfix\RssBundle\Entity\News;
use Prolyfix\RssBundle\Entity\RssFeedEntry;
use Prolyfix\RssBundle\Entity\RssFeedList;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProlyfixRssBundle extends ModuleBundle
{
    const IS_MODULE = true;
    
    private $authorizationChecker;
    public static function getTables(): array
    {
        return [
            RssFeedList::class,
            RssFeedEntry::class,
            News::class,
        ];
    }
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
    public static function getShortName(): string
    {
        return 'RssBundle';
    }
    public static function getModuleName(): string
    {
        return 'Rss';
    }
    public static function getModuleDescription(): string
    {
        return 'Rss Module';
    }
    public static function getModuleType(): string
    {
        return 'module';
    }
    public static function getModuleConfiguration(): array
    {
        return [
            (new ModuleConfiguration())
            ->setType('boolean')
            ->setName('lokalFeed')
            ->setDescription('allow lokal feed')
            ->setDefaultValue(["0"]),
            (new ModuleConfiguration())
            ->setType('boolean')
            ->setName('weeklyInformation per Email') 
            ->setDescription('send weekly information per email')
            ->setDefaultValue(["0"]),
        ];
    }

    public static function getModuleRights(): array
    {
        return [
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('user')
                ->setRole('ROLE_USER')
                ->setEntityClass(RssFeedList::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_ADMIN')
                ->setEntityClass(RssFeedEntry::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_USER')
                ->setEntityClass(News::class),                
        ];
    }

    public  function getMenuConfiguration(): array
    {
        return ['configuration' => [
            MenuItem::linkToCrud('Rss Feed List', 'fas fa-list', RssFeedList::class),
        ],
            'feed' => [
                MenuItem::section('Feed', 'fas fa-bullhorn'),
                MenuItem::linkToCrud('News', 'fas fa-list', News::class),
            ],
        ];
    }

    public static function getUserConfiguration(): array
    {
        return [];
    }

    public static function getModuleAccess(): array
    {
        return [];
    }

}