<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use Prolyfix\RssBundle\Entity\RssFeedEntry;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RssFeedEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RssFeedEntry::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
