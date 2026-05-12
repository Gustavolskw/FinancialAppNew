type DashboardKpiGridProps = {
  isLoading: boolean;
  balance: number;
  entriesTotal: number;
  expensesTotal: number;
  formatCurrency: (value: number) => string;
};

export function DashboardKpiGrid({
  isLoading,
  balance,
  entriesTotal,
  expensesTotal,
  formatCurrency,
}: DashboardKpiGridProps) {
  return (
    <section className="grid gap-4 md:grid-cols-3">
      {[
        ["Saldo atual", isLoading ? "Carregando..." : formatCurrency(balance), "text-blue-700 dark:text-blue-300"],
        ["Entradas", isLoading ? "Carregando..." : formatCurrency(entriesTotal), "text-emerald-600 dark:text-emerald-300"],
        ["Despesas", isLoading ? "Carregando..." : formatCurrency(expensesTotal), "text-red-600 dark:text-red-300"],
      ].map(([label, value, color]) => (
        <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" key={label}>
          <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
          <p className={`mt-3 text-2xl font-semibold ${color}`}>{value}</p>
        </div>
      ))}
    </section>
  );
}
