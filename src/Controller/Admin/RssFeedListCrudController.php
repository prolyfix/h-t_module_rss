<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use Prolyfix\HolidayAndTime\Controller\Admin\BaseCrudController;
use Prolyfix\RssBundle\Entity\RssFeedList;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RssFeedListCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return RssFeedList::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('creationDate')->hideOnForm(),
            TextField::new('feedName'),
            TextField::new('name'),
        ];
    }
}
