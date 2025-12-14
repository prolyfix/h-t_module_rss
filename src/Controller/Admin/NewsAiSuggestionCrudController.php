<?php

namespace Prolyfix\RssBundle\Controller\Admin;

use App\Controller\Admin\BaseCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Prolyfix\RssBundle\Entity\NewsAiSuggestion;
use Prolyfix\RssBundle\Service\NewsKnowledgeBaseProcessor;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NewsAiSuggestionCrudController extends BaseCrudController
{
    public function __construct(
        private readonly NewsKnowledgeBaseProcessor $processor,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return NewsAiSuggestion::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            AssociationField::new('news')
                ->setLabel('News Article')
                ->formatValue(function ($value, $entity) {
                    return sprintf(
                        '<a href="%s">%s</a>',
                        $this->generateUrl('admin', [
                            'crudAction' => 'detail',
                            'crudControllerFqcn' => NewsCrudController::class,
                            'entityId' => $entity->getNews()->getId()
                        ]),
                        $entity->getNews()->getTitle()
                    );
                })
                ->setTemplatePath('admin/field/text_link.html.twig')
                ->hideOnForm(),

            ChoiceField::new('suggestionType')
                ->setChoices([
                    'Update Existing' => 'update',
                    'Create New' => 'create',
                ])
                ->hideOnForm(),

            ChoiceField::new('status')
                ->setChoices([
                    'Pending Review' => 'pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected',
                    'Applied' => 'applied',
                ])
                ->setFormTypeOption('disabled', $pageName !== Crud::PAGE_EDIT),

            TextField::new('categoryName')
                ->setLabel('Category')
                ->hideOnIndex(),

            TextField::new('matchedKnowledgebaseName')
                ->setLabel('Matched KB Article')
                ->onlyOnDetail(),

            NumberField::new('matchConfidence')
                ->setLabel('Match Confidence')
                ->setNumDecimals(2)
                ->hideOnForm(),

            TextareaField::new('extractedInstructions')
                ->setLabel('Extracted Instructions')
                ->hideOnIndex()
                ->setFormTypeOption('disabled', true),

            TextField::new('suggestedTitle')
                ->setLabel('Suggested Title')
                ->hideOnIndex(),

            TextareaField::new('suggestedContent')
                ->setLabel('Suggested Content')
                ->hideOnIndex()
                ->renderAsHtml(),

            DateTimeField::new('creationDate')
                ->hideOnForm(),

            DateTimeField::new('appliedAt')
                ->onlyOnDetail(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('AI Suggestion')
            ->setEntityLabelInPlural('AI Suggestions')
            ->setDefaultSort(['creationDate' => 'DESC'])
            ->setPageTitle(Crud::PAGE_INDEX, 'AI Knowledge Base Suggestions')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (NewsAiSuggestion $suggestion) => 
                sprintf('AI Suggestion for: %s', $suggestion->getNews()->getTitle())
            )
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status')
            ->add('suggestionType')
            ->add('categoryName')
            ->add('creationDate');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Custom action to approve suggestion
        $approveSuggestion = Action::new('approve', 'Approve')
            ->linkToCrudAction('approveSuggestion')
            ->displayIf(fn (NewsAiSuggestion $suggestion) => $suggestion->isPending())
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-check');

        // Custom action to reject suggestion
        $rejectSuggestion = Action::new('reject', 'Reject')
            ->linkToCrudAction('rejectSuggestion')
            ->displayIf(fn (NewsAiSuggestion $suggestion) => $suggestion->isPending())
            ->setCssClass('btn btn-danger')
            ->setIcon('fa fa-times');

        // Custom action to apply suggestion
        $applySuggestion = Action::new('apply', 'Apply to Knowledge Base')
            ->linkToCrudAction('applySuggestion')
            ->displayIf(fn (NewsAiSuggestion $suggestion) => $suggestion->isApproved() && !$suggestion->isApplied())
            ->setCssClass('btn btn-primary')
            ->setIcon('fa fa-arrow-right');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $approveSuggestion)
            ->add(Crud::PAGE_DETAIL, $rejectSuggestion)
            ->add(Crud::PAGE_DETAIL, $applySuggestion)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    /**
     * Approve a suggestion
     */
    public function approveSuggestion(AdminContext $context): RedirectResponse
    {
        $suggestion = $context->getEntity()->getInstance();
        
        if (!$suggestion instanceof NewsAiSuggestion) {
            throw new \RuntimeException('Invalid entity');
        }

        $suggestion->setStatus('approved');
        $this->entityManager->flush();

        $this->addFlash('success', 'Suggestion approved successfully');

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($suggestion->getId())
            ->generateUrl()
        );
    }

    /**
     * Reject a suggestion
     */
    public function rejectSuggestion(AdminContext $context): RedirectResponse
    {
        $suggestion = $context->getEntity()->getInstance();
        
        if (!$suggestion instanceof NewsAiSuggestion) {
            throw new \RuntimeException('Invalid entity');
        }

        $suggestion->setStatus('rejected');
        $this->entityManager->flush();

        $this->addFlash('warning', 'Suggestion rejected');

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    /**
     * Apply suggestion to knowledge base
     */
    public function applySuggestion(AdminContext $context): RedirectResponse
    {
        $suggestion = $context->getEntity()->getInstance();
        
        if (!$suggestion instanceof NewsAiSuggestion) {
            throw new \RuntimeException('Invalid entity');
        }

        try {
            $knowledgeBase = $this->processor->applySuggestion($suggestion);
            
            $this->addFlash('success', sprintf(
                'Knowledge base article "%s" has been %s successfully',
                $knowledgeBase->getName(),
                $suggestion->getSuggestionType() === 'update' ? 'updated' : 'created'
            ));

        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to apply suggestion: ' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($suggestion->getId())
            ->generateUrl()
        );
    }
}
