<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Prolyfix\RssBundle\Entity\RssFeedEntry;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RssFeedEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RssFeedEntry::class;
    }



}
