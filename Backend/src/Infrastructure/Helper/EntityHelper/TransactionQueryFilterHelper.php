<?php

namespace App\Infrastructure\Helper\EntityHelper;

use App\Infrastructure\DTO\Params\DTO\ParamDto;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use Doctrine\ORM\QueryBuilder;

final class TransactionQueryFilterHelper
{
    public static function applyTransactionFilters(
        QueryBuilder $qb,
        QueryParamsInterface $params,
        string $transactionAlias
    ): void {
        /** @var ParamDto $param */
        foreach ($params->getSortParams() as $param) {
            $paramName = $param->getName();
            $paramValue = $param->getValue();

            if ($paramValue === null || $paramValue === '') {
                continue;
            }

            match ($paramName) {
                'amount',
                'month',
                'year' => self::applyExactFilter($qb, $transactionAlias, $paramName, $paramValue),
                'location',
                'description' => self::applyTextFilter($qb, $transactionAlias, $paramName, $paramValue),
                'walletId' => self::applyWalletFilter($qb, $transactionAlias, $paramValue),
                'date' => self::applyDateFilter($qb, $transactionAlias, $paramValue),
                default => null,
            };
        }
    }

    private static function applyExactFilter(QueryBuilder $qb, string $alias, string $field, mixed $value): void
    {
        $placeholder = self::placeholder($alias, $field);

        $qb->andWhere(sprintf('%s.%s = :%s', $alias, $field, $placeholder))
            ->setParameter($placeholder, $value);
    }

    private static function applyTextFilter(QueryBuilder $qb, string $alias, string $field, mixed $value): void
    {
        $placeholder = self::placeholder($alias, $field);

        $qb->andWhere(sprintf('%s.%s LIKE :%s', $alias, $field, $placeholder))
            ->setParameter($placeholder, '%' . $value . '%');
    }

    private static function applyWalletFilter(QueryBuilder $qb, string $alias, mixed $walletId): void
    {
        $placeholder = self::placeholder($alias, 'walletId');

        $qb->andWhere(sprintf('IDENTITY(%s.transactionWallet) = :%s', $alias, $placeholder))
            ->setParameter($placeholder, (int) $walletId);
    }

    private static function applyDateFilter(QueryBuilder $qb, string $alias, mixed $value): void
    {
        try {
            $date = new \DateTimeImmutable((string) $value);
        } catch (\Exception) {
            return;
        }

        $startPlaceholder = self::placeholder($alias, 'dateStart');
        $endPlaceholder = self::placeholder($alias, 'dateEnd');

        if (str_contains((string) $value, 'T') || str_contains((string) $value, ':')) {
            $qb->andWhere(sprintf('%s.date = :%s', $alias, $startPlaceholder))
                ->setParameter($startPlaceholder, $date);

            return;
        }

        $qb->andWhere(sprintf('%s.date >= :%s', $alias, $startPlaceholder))
            ->andWhere(sprintf('%s.date < :%s', $alias, $endPlaceholder))
            ->setParameter($startPlaceholder, $date->setTime(0, 0))
            ->setParameter($endPlaceholder, $date->modify('+1 day')->setTime(0, 0));
    }

    private static function placeholder(string $alias, string $field): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $alias . '_' . $field) ?? $alias . '_' . $field;
    }
}
