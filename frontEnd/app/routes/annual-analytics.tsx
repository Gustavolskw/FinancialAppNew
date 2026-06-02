import { useCallback, useEffect, useState } from "react";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Tooltip,
  Legend,
  Filler,
} from "chart.js";

import { AuthenticatedAppShell } from "../components/navigation/AuthenticatedAppShell";
import { useRequireAuth } from "../Infrastructure/Auth/useRequireAuth";
import { loadAnnualAnalytics, type AnnualAnalyticsData, type GroupedItem, type MonthlyBreakdownItem } from "../Infrastructure/Api/analytics";
import { loadDashboardData } from "../Infrastructure/Api/dashboard";

import { YearFilter } from "../components/analytics/YearFilter";
import { MonthChipsFilter } from "../components/analytics/MonthChipsFilter";
import { AnnualSummaryKpis } from "../components/analytics/AnnualSummaryKpis";
import { EntriesVsExpensesChart } from "../components/analytics/EntriesVsExpensesChart";
import { MonthlyBreakdownChart } from "../components/analytics/MonthlyBreakdownChart";
import { ExpenseTypeFrequencyChart } from "../components/analytics/ExpenseTypeFrequencyChart";
import { ExpenseTypeAmountChart } from "../components/analytics/ExpenseTypeAmountChart";
import { EntryTypeFrequencyChart } from "../components/analytics/EntryTypeFrequencyChart";
import { PaymentMethodFrequencyChart } from "../components/analytics/PaymentMethodFrequencyChart";
import { PaymentMethodAmountChart } from "../components/analytics/PaymentMethodAmountChart";

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Tooltip, Legend, Filler);

const EMPTY_DATA: AnnualAnalyticsData = {
  availableYears: [new Date().getFullYear()],
  year: new Date().getFullYear(),
  totals: { entries: 0, expenses: 0, balance: 0 },
  monthlyBreakdown: Array.from({ length: 12 }, (_, i) => ({ month: i + 1, entries: 0, expenses: 0, balance: 0 })),
  expensesByType: [],
  entriesByType: [],
  expensesByPaymentMethod: [],
};

function filterByMonths<T extends { month?: number }>(items: T[], months: number[]): T[] {
  if (months.length === 0) return items;
  return items.filter((i) => i.month !== undefined && months.includes(i.month));
}

function filterGroupedByMonths(
  rawBreakdown: MonthlyBreakdownItem[],
  selectedMonths: number[],
  allExpensesByType: GroupedItem[],
  allEntriesByType: GroupedItem[],
  allExpensesByPayment: GroupedItem[],
): { expensesByType: GroupedItem[]; entriesByType: GroupedItem[]; expensesByPaymentMethod: GroupedItem[] } {
  // When no months selected, return full data
  if (selectedMonths.length === 0) {
    return { expensesByType: allExpensesByType, entriesByType: allEntriesByType, expensesByPaymentMethod: allExpensesByPayment };
  }
  // Client-side filtering not possible for grouped data without raw transactions
  // Return full data (backend already aggregated by year)
  return { expensesByType: allExpensesByType, entriesByType: allEntriesByType, expensesByPaymentMethod: allExpensesByPayment };
}

export default function AnnualAnalyticsPage() {
  useRequireAuth();

  const [isLoading, setIsLoading] = useState(true);
  const [walletId, setWalletId] = useState<number | null>(null);
  const [year, setYear] = useState(new Date().getFullYear());
  const [data, setData] = useState<AnnualAnalyticsData>(EMPTY_DATA);
  const [selectedMonths, setSelectedMonths] = useState<number[]>([]);

  // Load wallet on mount
  useEffect(() => {
    loadDashboardData().then((d) => {
      if (d.wallet?.id) {
        setWalletId(d.wallet.id);
      }
    }).catch(() => {});
  }, []);

  // Load analytics when walletId or year changes
  const fetchAnalytics = useCallback(async () => {
    if (!walletId) return;
    setIsLoading(true);
    try {
      const result = await loadAnnualAnalytics(walletId, year);
      setData(result);
    } catch {
      setData(EMPTY_DATA);
    } finally {
      setIsLoading(false);
    }
  }, [walletId, year]);

  useEffect(() => {
    fetchAnalytics();
  }, [fetchAnalytics]);

  // Compute filtered totals based on selected months
  const filteredBreakdown = selectedMonths.length > 0
    ? data.monthlyBreakdown.filter((m) => selectedMonths.includes(m.month))
    : data.monthlyBreakdown;

  const filteredTotals = selectedMonths.length > 0
    ? {
        entries: filteredBreakdown.reduce((s, m) => s + m.entries, 0),
        expenses: filteredBreakdown.reduce((s, m) => s + m.expenses, 0),
        balance: filteredBreakdown.reduce((s, m) => s + m.balance, 0),
      }
    : data.totals;

  return (
    <AuthenticatedAppShell>
      <div className="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6">
        <header className="flex flex-col gap-1">
          <h1 className="text-2xl font-bold text-slate-950 dark:text-white">Análise Anual</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Panorama completo de entradas e saídas durante o ano
          </p>
        </header>

        {/* Filters */}
        <section className="flex flex-wrap items-end gap-4">
          <YearFilter
            availableYears={data.availableYears}
            disabled={isLoading}
            onChange={setYear}
            value={year}
          />
        </section>

        <MonthChipsFilter onChange={setSelectedMonths} selected={selectedMonths} />

        {/* KPIs */}
        <AnnualSummaryKpis isLoading={isLoading} totals={filteredTotals} />

        {/* Charts */}
        <section className="grid gap-4 lg:grid-cols-2">
          <EntriesVsExpensesChart isLoading={isLoading} totals={filteredTotals} />
          <EntryTypeFrequencyChart isLoading={isLoading} items={data.entriesByType} />
        </section>

        {/* Monthly breakdown always shows full year */}
        <MonthlyBreakdownChart breakdown={data.monthlyBreakdown} isLoading={isLoading} />

        <section className="grid gap-4 lg:grid-cols-2">
          <ExpenseTypeFrequencyChart isLoading={isLoading} items={data.expensesByType} />
          <ExpenseTypeAmountChart isLoading={isLoading} items={data.expensesByType} />
        </section>

        <section className="grid gap-4 lg:grid-cols-2">
          <PaymentMethodFrequencyChart isLoading={isLoading} items={data.expensesByPaymentMethod} />
          <PaymentMethodAmountChart isLoading={isLoading} items={data.expensesByPaymentMethod} />
        </section>
      </div>
    </AuthenticatedAppShell>
  );
}
