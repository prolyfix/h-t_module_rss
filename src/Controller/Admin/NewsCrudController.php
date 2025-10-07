<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use ApiPlatform\Hydra\Collection;
use App\Controller\Admin\BaseCrudController;
use App\Field\TagJsonField;
use App\Form\MediaType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Prolyfix\RssBundle\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Vich\UploaderBundle\Form\Type\VichFileType;

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
            TextField::new('file')
                    ->onlyOnForms()
                    ->setFormType(VichFileType::class)

        ];
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/detail' => '@ProlyfixRss/news/detail.html.twig',
                'crud/index' => '@ProlyfixRss/news/index.html.twig',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title');
    }
}
