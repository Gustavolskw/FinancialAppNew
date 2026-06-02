<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Analytics\Wallet;

use App\Entity\Entry;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Infrastructure\Handler\Analytics\Interface\AnalyticsConfigurationInterface;
use Doctrine\ORM\EntityManagerInterface;

final class WalletAnnualAnalytics implements AnalyticsConfigurationInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /** {@inheritDoc} */
    public function getAvailableYears(int $walletId): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('DISTINCT t.year')
            ->from(Transaction::class, 't')
            ->where('IDENTITY(t.transactionWallet) = :walletId')
            ->setParameter('walletId', $walletId)
            ->orderBy('t.year', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        $years = array_map('intval', $result);
        $currentYear = (int) date('Y');

        if (!in_array($currentYear, $years, true)) {
            $years[] = $currentYear;
            rsort($years);
        }

        return $years;
    }

    /** {@inheritDoc} */
    public function compute(int $walletId, int $year): array
    {
        $expenses = $this->fetchExpenses($walletId, $year);
        $entries = $this->fetchEntries($walletId, $year);

        $monthly = $this->buildEmptyMonthlyBreakdown();
        [$expensesByType, $expensesByPayment] = $this->aggregateExpenses($expenses, $monthly);
        $entriesByType = $this->aggregateEntries($entries, $monthly);

        [$totalEntries, $totalExpenses] = $this->finalizeMonthly($monthly);

        return [
            'availableYears' => $this->getAvailableYears($walletId),
            'year' => $year,
            'totals' => [
                'entries' => round($totalEntries, 2),
                'expenses' => round($totalExpenses, 2),
                'balance' => round($totalEntries - $totalExpenses, 2),
            ],
            'monthlyBreakdown' => array_values($monthly),
            'expensesByType' => $this->sortAndRound($expensesByType),
            'entriesByType' => $this->sortAndRound($entriesByType),
            'expensesByPaymentMethod' => $this->sortAndRound($expensesByPayment),
        ];
    }

    /** @return Expense[] */
    private function fetchExpenses(int $walletId, int $year): array
    {
        return $this->em->createQueryBuilder()
            ->select('e', 't', 'et', 'pm')
            ->from(Expense::class, 'e')
            ->join('e.expenseTransaction', 't')
            ->join('e.expenseType', 'et')
            ->join('e.expensePaymentMethod', 'pm')
            ->where('IDENTITY(t.transactionWallet) = :walletId')
            ->andWhere('t.year = :year')
            ->setParameter('walletId', $walletId)
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();
    }

    /** @return Entry[] */
    private function fetchEntries(int $walletId, int $year): array
    {
        return $this->em->createQueryBuilder()
            ->select('en', 't', 'ent')
            ->from(Entry::class, 'en')
            ->join('en.transaction', 't')
            ->join('en.entryType', 'ent')
            ->where('IDENTITY(t.transactionWallet) = :walletId')
            ->andWhere('t.year = :year')
            ->setParameter('walletId', $walletId)
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{month: int, entries: float, expenses: float, balance: float}>
     */
    private function buildEmptyMonthlyBreakdown(): array
    {
        $monthly = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = ['month' => $m, 'entries' => 0.0, 'expenses' => 0.0, 'balance' => 0.0];
        }

        return $monthly;
    }

    /**
     * @param Expense[] $expenses
     * @param array<int, array<string, mixed>> $monthly passed by reference
     * @return array{array<int, array<string, mixed>>, array<int, array<string, mixed>>}
     */
    private function aggregateExpenses(array $expenses, array &$monthly): array
    {
        $byType = [];
        $byPayment = [];

        foreach ($expenses as $expense) {
            $transaction = $expense->getExpenseTransaction();
            $amount = (float) $transaction->getAmount();
            $month = $transaction->getMonth();

            if ($month >= 1 && $month <= 12) {
                $monthly[$month]['expenses'] += $amount;
            }

            $type = $expense->getExpenseType();
            $byType[$type->getId()] ??= ['label' => $type->getName(), 'id' => $type->getId(), 'count' => 0, 'total' => 0.0];
            $byType[$type->getId()]['count']++;
            $byType[$type->getId()]['total'] += $amount;

            $pm = $expense->getExpensePaymentMethod();
            $byPayment[$pm->getId()] ??= ['label' => $pm->getName(), 'id' => $pm->getId(), 'count' => 0, 'total' => 0.0];
            $byPayment[$pm->getId()]['count']++;
            $byPayment[$pm->getId()]['total'] += $amount;
        }

        return [$byType, $byPayment];
    }

    /**
     * @param Entry[] $entries
     * @param array<int, array<string, mixed>> $monthly passed by reference
     * @return array<int, array<string, mixed>>
     */
    private function aggregateEntries(array $entries, array &$monthly): array
    {
        $byType = [];

        foreach ($entries as $entry) {
            $transaction = $entry->getTransaction();
            $amount = (float) $transaction->getAmount();
            $month = $transaction->getMonth();

            if ($month >= 1 && $month <= 12) {
                $monthly[$month]['entries'] += $amount;
            }

            $type = $entry->getEntryType();
            $byType[$type->getId()] ??= ['label' => $type->getName(), 'id' => $type->getId(), 'count' => 0, 'total' => 0.0];
            $byType[$type->getId()]['count']++;
            $byType[$type->getId()]['total'] += $amount;
        }

        return $byType;
    }

    /**
     * Finalizes monthly balances and returns [totalEntries, totalExpenses].
     *
     * @param array<int, array<string, mixed>> $monthly passed by reference
     * @return array{float, float}
     */
    private function finalizeMonthly(array &$monthly): array
    {
        $totalEntries = 0.0;
        $totalExpenses = 0.0;

        foreach ($monthly as &$m) {
            $m['balance'] = round($m['entries'] - $m['expenses'], 2);
            $m['entries'] = round($m['entries'], 2);
            $m['expenses'] = round($m['expenses'], 2);
            $totalEntries += $m['entries'];
            $totalExpenses += $m['expenses'];
        }
        unset($m);

        return [$totalEntries, $totalExpenses];
    }

    /**
     * Sorts aggregation by total descending and rounds each total to 2 decimal places.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function sortAndRound(array $items): array
    {
        usort($items, fn ($a, $b) => $b['total'] <=> $a['total']);

        return array_map(
            fn ($i) => array_merge($i, ['total' => round($i['total'], 2)]),
            $items
        );
    }
}
