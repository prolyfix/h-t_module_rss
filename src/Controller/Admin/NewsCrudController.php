<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use ApiPlatform\Hydra\Collection;
use Prolyfix\HolidayAndTime\Controller\Admin\BaseCrudController;
use Prolyfix\HolidayAndTimeField\TagJsonField;
use Prolyfix\HolidayAndTime\Form\MediaType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Prolyfix\RssBundle\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Doctrine\ORM\QueryBuilder;
use Prolyfix\HolidayAndTime\Entity\User;
use Prolyfix\RssBundle\Filter\UnreadNewsFilter;
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
        $fields = [
            DateField::new('creationDate')->hideOnForm(), 
            TextField::new('title'),
            TextField::new('link')->hideOnIndex(),
            TextField::new('custom', 'Status')
                ->onlyOnIndex()
                ->setSortable(false)
                ->formatValue(function ($value, News $news) {
                    $user = $this->getUser();
                    if (!$user instanceof User) {
                        return 'read';
                    }

                    $readsStats = $news->getReadsStats() ?? [];
                    $isRead = (int) ($readsStats[$user->getId()] ?? 0) === 1;

                    return $isRead ? 'gelesen' : '<span class="badge text-bg-info">New</span>';
                })
                ->renderAsHtml(),
            TextEditorField::new('content')->hideOnIndex(),
            AssociationField::new('workingGroup')
                ->onlyOnForms()
                ->renderAsNativeWidget()
                ->setFormTypeOption('required', false),
            TextField::new('file')
                    ->onlyOnForms()
                    ->setFormType(VichFileType::class)

        ];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
            $fields[] = TextField::new('custom2', 'Reads')
                ->onlyOnIndex()
                ->setSortable(false)
                ->formatValue(function ($value, News $news) {
                    $readsStats = $news->getReadsStats() ?? [];
                    if (count($readsStats) === 0) {
                        return '0/0';
                    }

                    return sprintf('%d/%d', array_sum(array_map('intval', $readsStats)), count($readsStats));
                });
        }

        return $fields;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/detail' => '@ProlyfixRss/news/detail.html.twig',
                //'crud/index' => '@ProlyfixRss/news/index.html.twig',
            ])
            ->setDefaultSort(['creationDate' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $user = $this->getUser();
        $unreadFilter = UnreadNewsFilter::new();
        if ($user instanceof User) {
            $unreadFilter->setCurrentUserId($user->getId());
        }

        return $filters
            ->add('title')
            ->add($unreadFilter);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setIcon('fa fa-plus')
                ->setLabel('New')
                ->setHtmlAttributes(['class' => 'btn btn-primary','data-action' => ''])
            ;
        });
        return $actions;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return $queryBuilder;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $queryBuilder->andWhere('1 = 0');
        }

        $workingGroup = $user->getWorkingGroup();
        if ($workingGroup === null) {
            return $queryBuilder->andWhere('entity.workingGroup IS NULL');
        }

        return $queryBuilder
            ->andWhere('entity.workingGroup = :current_workingGroup OR entity.workingGroup IS NULL')
            ->setParameter('current_workingGroup', $workingGroup);
    }
}
