<?php

namespace Prolyfix\RssBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;

final class UnreadNewsFilter implements FilterInterface
{
    use FilterTrait;

    private ?int $currentUserId = null;

    public static function new(string $propertyName = 'readsStats', ?string $label = 'Unread only'): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(BooleanFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function setCurrentUserId(?int $currentUserId): self
    {
        $this->currentUserId = $currentUserId;

        return $this;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $value = $filterDataDto->getValue();
        if ($value === null || $value === '') {
            return;
        }

        $isUnread = match ($value) {
            true, 1, '1', 'true', 'on' => true,
            false, 0, '0', 'false', 'off' => false,
            default => null,
        };
        if ($isUnread === null) {
            return;
        }

        $userId = (string) ($this->currentUserId ?? '');
        if ($userId === '') {
            return;
        }

        $entityAlias = $filterDataDto->getEntityAlias();
        $parameterName = $filterDataDto->getParameterName();
        $readPattern = sprintf('%%"%s":1%%', $userId);

        if ($isUnread) {
            $queryBuilder
                ->andWhere(sprintf('(%s.readsStats IS NULL OR %s.readsStats NOT LIKE :%s)', $entityAlias, $entityAlias, $parameterName))
                ->setParameter($parameterName, $readPattern);

            return;
        }

        $queryBuilder
            ->andWhere(sprintf('%s.readsStats LIKE :%s', $entityAlias, $parameterName))
            ->setParameter($parameterName, $readPattern);
    }
}
