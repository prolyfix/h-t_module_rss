<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use App\Controller\Admin\BaseCrudController;
use App\Field\TagJsonField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Prolyfix\RssBundle\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NewsCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('creationDate')->hideOnForm(), 
            TextField::new('title'),
            TextEditorField::new('content')->hideOnIndex(),
           // TagJsonField::new('tags')
        ];
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/detail' => '@ProlyfixRss/news/detail.html.twig',
            ]);
    }
}
