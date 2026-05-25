import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";

type TransactionsTableProps = {
  transactions: DashboardTransaction[];
  emptyLabel: string;
  formatCurrency: (value: number) => string;
};

export function TransactionsTable({ transactions, emptyLabel, formatCurrency }: TransactionsTableProps) {
  return (
    <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="border-b border-slate-200 p-5 dark:border-slate-800">
        <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Últimas movimentações</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">Entry e Expense da carteira carregada</p>
      </div>

      {/* Mobile: Cards */}
      <div className="space-y-3 p-5 md:hidden">
        {transactions.map((transaction) => (
          <div
            key={transaction.id}
            className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
          >
            <div className="mb-3 flex items-start justify-between">
              <div className="flex-1">
                <h3 className="font-semibold text-slate-900 dark:text-white">{transaction.description}</h3>
                <span
                  className={`mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold ${transaction.type === "entry"
                      ? "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
                      : "bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300"
                    }`}
                >
                  {transaction.type === "entry" ? "Entrada" : "Despesa"}
                </span>
              </div>
            </div>

            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-slate-500 dark:text-slate-400">Categoria</span>
                <span className="font-medium text-slate-900 dark:text-white">{transaction.category}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500 dark:text-slate-400">Método</span>
                <span className="font-medium text-slate-900 dark:text-white">{transaction.paymentMethod}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500 dark:text-slate-400">Local</span>
                <span className="font-medium text-slate-900 dark:text-white">{transaction.location || "-"}</span>
              </div>
              <div className="flex justify-between border-t border-slate-100 pt-2 dark:border-slate-700">
                <span className="font-semibold text-slate-700 dark:text-slate-300">Valor</span>
                <span className="text-lg font-bold text-slate-900 dark:text-white">{formatCurrency(transaction.amount)}</span>
              </div>
            </div>
          </div>
        ))}
        {transactions.length === 0 && (
          <div className="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
            {emptyLabel}
          </div>
        )}
      </div>

      {/* Desktop: Table */}
      <div className="hidden overflow-x-auto px-5 md:block">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500 dark:border-slate-800 dark:text-slate-400">
            <tr>
              <th className="py-3 pr-4 font-semibold">Descrição</th>
              <th className="py-3 pr-4 font-semibold">Tipo</th>
              <th className="py-3 pr-4 font-semibold">Categoria</th>
              <th className="py-3 pr-4 font-semibold">Método</th>
              <th className="py-3 pr-4 font-semibold">Local</th>
              <th className="py-3 text-right font-semibold">Valor</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
            {transactions.map((transaction) => (
              <tr key={transaction.id}>
                <td className="py-3 pr-4 font-medium text-slate-900 dark:text-white">{transaction.description}</td>
                <td className="py-3 pr-4">
                  <span className={transaction.type === "entry" ? "text-emerald-600" : "text-red-600"}>
                    {transaction.type === "entry" ? "Entrada" : "Despesa"}
                  </span>
                </td>
                <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.category}</td>
                <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.paymentMethod}</td>
                <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.location || "-"}</td>
                <td className="py-3 text-right font-semibold">{formatCurrency(transaction.amount)}</td>
              </tr>
            ))}
            {transactions.length === 0 && (
              <tr>
                <td className="py-8 text-center text-slate-500 dark:text-slate-400" colSpan={6}>
                  {emptyLabel}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
