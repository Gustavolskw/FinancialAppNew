import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";

type TransactionsTableProps = {
  transactions: DashboardTransaction[];
  emptyLabel: string;
  formatCurrency: (value: number) => string;
};

export function TransactionsTable({ transactions, emptyLabel, formatCurrency }: TransactionsTableProps) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4">
        <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Últimas movimentações</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">Entry e Expense da carteira carregada</p>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full min-w-[760px] text-left text-sm">
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
