import { useEffect, useMemo, useState } from "react";

import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";

type TransactionsTableProps = {
  transactions: DashboardTransaction[];
  emptyLabel: string;
  formatCurrency: (value: number) => string;
};

type TransactionTab = "all" | "entry" | "expense";

function DescriptionModal({ description, onClose }: { description: string; onClose: () => void }) {
  useEffect(() => {
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    function handleKeyDown(event: KeyboardEvent) {
      if (event.key === "Escape") onClose();
    }

    document.addEventListener("keydown", handleKeyDown);

    return () => {
      document.body.style.overflow = prev;
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [onClose]);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-3 py-4 backdrop-blur-sm" onClick={onClose} role="presentation">
      <div
        className="w-full max-w-md rounded-lg border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        onClick={(e) => e.stopPropagation()}
        role="dialog"
      >
        <div className="mb-4 flex items-start justify-between">
          <h3 className="text-lg font-semibold text-slate-950 dark:text-white">Descrição</h3>
          <button
            aria-label="Fechar"
            className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white"
            onClick={onClose}
            type="button"
          >
            <svg className="h-4 w-4" fill="none" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6 6 18M6 6l12 12" />
            </svg>
          </button>
        </div>
        <p className="text-sm text-slate-700 dark:text-slate-300">{description}</p>
      </div>
    </div>
  );
}

export function TransactionsTable({ transactions, emptyLabel, formatCurrency }: TransactionsTableProps) {
  const [selectedDescription, setSelectedDescription] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<TransactionTab>("all");

  const filteredTransactions = useMemo(() => {
    if (activeTab === "all") return transactions;
    return transactions.filter((t) => t.type === activeTab);
  }, [transactions, activeTab]);

  const entriesCount = transactions.filter((t) => t.type === "entry").length;
  const expensesCount = transactions.filter((t) => t.type === "expense").length;

  return (
    <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="border-b border-slate-200 p-5 dark:border-slate-800">
        <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Últimas movimentações</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">Entradas e despesas da carteira</p>
      </div>

      <div className="border-b border-slate-200 px-5 dark:border-slate-800">
        <div className="flex gap-1">
          <button
            className={`border-b-2 px-4 py-3 text-sm font-semibold transition ${activeTab === "all"
              ? "border-blue-700 text-blue-700 dark:border-blue-300 dark:text-blue-300"
              : "border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300"
              }`}
            onClick={() => setActiveTab("all")}
            type="button"
          >
            Todas ({transactions.length})
          </button>
          <button
            className={`border-b-2 px-4 py-3 text-sm font-semibold transition ${activeTab === "entry"
              ? "border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400"
              : "border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300"
              }`}
            onClick={() => setActiveTab("entry")}
            type="button"
          >
            Entradas ({entriesCount})
          </button>
          <button
            className={`border-b-2 px-4 py-3 text-sm font-semibold transition ${activeTab === "expense"
              ? "border-red-600 text-red-600 dark:border-red-400 dark:text-red-400"
              : "border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300"
              }`}
            onClick={() => setActiveTab("expense")}
            type="button"
          >
            Despesas ({expensesCount})
          </button>
        </div>
      </div>

      {/* Mobile: Cards */}
      <div className="space-y-3 p-5 md:hidden">
        {filteredTransactions.map((transaction) => (
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
        {filteredTransactions.length === 0 && (
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
              {activeTab === "all" && <th className="py-3 pr-4 font-semibold">Tipo</th>}
              <th className="py-3 pr-4 font-semibold">Categoria</th>
              {activeTab !== "entry" && <th className="py-3 pr-4 font-semibold">Método</th>}
              <th className="py-3 pr-4 font-semibold">Local</th>
              <th className="py-3 pr-4 text-right font-semibold">Valor</th>
              <th className="py-3 text-center font-semibold">Descrição</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
            {filteredTransactions.map((transaction) => (
              <tr key={transaction.id}>
                {activeTab === "all" && (
                  <td className="py-3 pr-4">
                    <span className={transaction.type === "entry" ? "text-emerald-600" : "text-red-600"}>
                      {transaction.type === "entry" ? "Entrada" : "Despesa"}
                    </span>
                  </td>
                )}
                <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.category}</td>
                {activeTab !== "entry" && (
                  <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.paymentMethod}</td>
                )}
                <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.location || "-"}</td>
                <td className="py-3 pr-4 text-right font-semibold">{formatCurrency(transaction.amount)}</td>
                <td className="py-3 text-center">
                  <button
                    className="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200 dark:hover:bg-blue-500/20"
                    onClick={() => setSelectedDescription(transaction.description)}
                    type="button"
                  >
                    Exibir
                  </button>
                </td>
              </tr>
            ))}
            {filteredTransactions.length === 0 && (
              <tr>
                <td className="py-8 text-center text-slate-500 dark:text-slate-400" colSpan={activeTab === "entry" ? 4 : activeTab === "all" ? 6 : 5}>
                  {emptyLabel}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {selectedDescription && (
        <DescriptionModal
          description={selectedDescription}
          onClose={() => setSelectedDescription(null)}
        />
      )}
    </div>
  );
}
