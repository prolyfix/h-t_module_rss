<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use ApiPlatform\Hydra\Collection;
use App\Controller\Admin\BaseCrudController;
use App\Field\TagJsonField;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Prolyfix\RssBundle\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Prolyfix\RssBundle\Service\NewsKnowledgeBaseProcessor;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class NewsCrudController extends BaseCrudController
{
    public function __construct(
        private readonly NewsKnowledgeBaseProcessor $processor,
        Security $security,
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        AdminUrlGenerator $adminUrlGenerator,
        RequestStack $requestStack,
        MessageBusInterface $messageBus
    ) {
        parent::__construct($security, $em, $eventDispatcher, $adminUrlGenerator, $requestStack, $messageBus);
    }

    public static function getEntityFqcn(): string
    {
        return News::class;
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('creationDate')->hideOnForm(), 
            TextField::new('title')
                ->formatValue(function ($value, $entity) {
                    return sprintf(
                        '<a href="%s">%s</a>',
                        $this->generateUrl('admin', [
                            'crudAction' => 'detail',
                            'crudControllerFqcn' => self::class,
                            'entityId' => $entity->getId()
                        ]),
                        $value
                    );
                })
                ->setTemplatePath('admin/field/text_link.html.twig'),
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
                //'crud/index' => '@ProlyfixRss/news/index.html.twig',
            ])
            ->setDefaultSort(['creationDate' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        
        // AI Processing Action
        $processWithAi = Action::new('processWithAi', 'AI Process', 'fa fa-robot')
            ->linkToCrudAction('processWithAi')
            ->setCssClass('btn btn-info')
            ->setHtmlAttributes(['title' => 'Analyze news and generate knowledge base suggestions']);

        $actions->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setIcon('fa fa-plus')
                ->setLabel('New')
                ->setHtmlAttributes(['class' => 'btn btn-primary','data-action' => ''])
            ;
        });

        $actions
            ->add(Crud::PAGE_DETAIL, $processWithAi)
            ->add(Crud::PAGE_INDEX, $processWithAi);

        return $actions;
    }

    /**
     * Process news with AI to generate knowledge base suggestions
     */
    public function processWithAi(AdminContext $context): RedirectResponse
    {
        $news = $context->getEntity()->getInstance();
        
        if (!$news instanceof News) {
            throw new \RuntimeException('Invalid entity');
        }

        try {
            $suggestion = $this->processor->processNews($news);
            
            if ($suggestion) {
                $this->addFlash('success', sprintf(
                    'AI analysis complete! A %s suggestion has been created for knowledge base.',
                    $suggestion->getSuggestionType() === 'update' ? 'update' : 'new article'
                ));

                // Redirect to the suggestion detail page
                return $this->redirect($this->adminUrlGenerator
                    ->setController(NewsAiSuggestionCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($suggestion->getId())
                    ->generateUrl()
                );
            } else {
                $this->addFlash('info', 'No actionable instructions found in this news article.');
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'AI processing failed: ' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($news->getId())
            ->generateUrl()
        );
    }
}
