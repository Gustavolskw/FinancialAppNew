import type { AnalyticsTotals } from "../../Infrastructure/Api/analytics";
import { currency } from "../dashboard/dashboardMetrics";

type AnnualSummaryKpisProps = {
  totals: AnalyticsTotals;
  isLoading: boolean;
};

export function AnnualSummaryKpis({ totals, isLoading }: AnnualSummaryKpisProps) {
  const kpis = [
    { label: "Total Entradas", value: totals.entries, color: "text-emerald-600 dark:text-emerald-400" },
    { label: "Total Saídas", value: totals.expenses, color: "text-red-600 dark:text-red-400" },
    { label: "Saldo", value: totals.balance, color: totals.balance >= 0 ? "text-blue-600 dark:text-blue-400" : "text-red-600 dark:text-red-400" },
  ];

  return (
    <section className="grid gap-4 sm:grid-cols-3">
      {kpis.map((kpi) => (
        <div
          key={kpi.label}
          className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <p className="text-sm text-slate-500 dark:text-slate-400">{kpi.label}</p>
          <p className={`mt-1 text-2xl font-bold ${kpi.color}`}>
            {isLoading ? "..." : currency(kpi.value)}
          </p>
        </div>
      ))}
    </section>
  );
}
