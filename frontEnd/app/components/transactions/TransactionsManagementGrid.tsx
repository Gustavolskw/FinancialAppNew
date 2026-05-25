import { useEffect, useRef, useState } from "react";

import { currency } from "../dashboard/dashboardMetrics";
import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";
import type { TransactionType } from "../../Infrastructure/Api/movements";
import { Select } from "../shared/Select";

type TransactionsManagementGridProps = {
  activeType: TransactionType;
  emptyLabel: string;
  isDeleting: boolean;
  pagination: TransactionsPaginationProps;
  selectedIds: Set<number>;
  transactions: DashboardTransaction[];
  onBulkDelete: () => void;
  onDelete: (transaction: DashboardTransaction) => void;
  onEdit: (transaction: DashboardTransaction) => void;
  onSelect: (id: number, selected: boolean) => void;
  onSelectAll: (selected: boolean) => void;
  onStatusUnavailable: () => void;
};

export function TransactionsManagementGrid({
  activeType,
  emptyLabel,
  isDeleting,
  pagination,
  selectedIds,
  transactions,
  onBulkDelete,
  onDelete,
  onEdit,
  onSelect,
  onSelectAll,
  onStatusUnavailable,
}: TransactionsManagementGridProps) {
  const [bulkMenuOpen, setBulkMenuOpen] = useState(false);
  const selectedCount = selectedIds.size;
  const selectableIds = transactions
    .map((transaction) => transaction.resourceId)
    .filter((id): id is number => typeof id === "number");
  const selectedPageCount = selectableIds.filter((id) => selectedIds.has(id)).length;
  const allSelected = selectableIds.length > 0 && selectableIds.every((id) => selectedIds.has(id));
  const checkboxRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (checkboxRef.current) {
      checkboxRef.current.indeterminate = selectedPageCount > 0 && !allSelected;
    }
  }, [allSelected, selectedPageCount]);

  return (
    <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
            Gestão de {activeType === "entry" ? "entradas" : "saídas"}
          </h2>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Selecione itens para executar ações em massa ou edite um registro por vez.
          </p>
        </div>

        {selectedCount > 0 && (
          <div className="relative w-full sm:w-fit">
            <button
              className="btn-entrar btn-entrar--sm w-full sm:w-auto"
              disabled={isDeleting}
              onClick={() => setBulkMenuOpen((currentValue) => !currentValue)}
              type="button"
            >
              <span>Ações em massa ({selectedCount})</span>
            </button>
            {bulkMenuOpen && (
              <div className="absolute right-0 z-20 mt-2 w-full min-w-56 rounded-lg border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-slate-950 sm:w-64">
                <button
                  className="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm font-semibold text-red-600 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10"
                  disabled={isDeleting}
                  onClick={() => {
                    setBulkMenuOpen(false);
                    onBulkDelete();
                  }}
                  type="button"
                >
                  Excluir selecionados
                </button>
                <button
                  className="mt-1 flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                  onClick={() => {
                    setBulkMenuOpen(false);
                    onStatusUnavailable();
                  }}
                  title="Funcionalidade em desenvolvimento."
                  type="button"
                >
                  Alterar status
                </button>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Mobile: Cards */}
      <div className="space-y-3 md:hidden">
        {transactions.map((transaction) => {
          const rowId = transaction.resourceId;
          const isSelected = typeof rowId === "number" && selectedIds.has(rowId);

          return (
            <div
              key={transaction.id}
              className={`rounded-lg border p-4 shadow-sm ${isSelected
                  ? "border-blue-300 bg-blue-50 dark:border-blue-500/50 dark:bg-blue-500/10"
                  : "border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                }`}
            >
              <div className="mb-3 flex items-start justify-between">
                <div className="flex-1">
                  <h3 className="font-semibold text-slate-900 dark:text-white">{transaction.description}</h3>
                  <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{transaction.category}</p>
                </div>
                {typeof rowId === "number" && (
                  <input
                    aria-label={`Selecionar ${transaction.description}`}
                    checked={isSelected}
                    className="mt-1 h-5 w-5 rounded border-slate-300 text-blue-700 focus:ring-blue-700/20"
                    disabled={isDeleting}
                    onChange={(event) => onSelect(rowId, event.target.checked)}
                    type="checkbox"
                  />
                )}
              </div>

              <div className="space-y-2 text-sm">
                {activeType === "expense" && (
                  <div className="flex justify-between">
                    <span className="text-slate-500 dark:text-slate-400">Método</span>
                    <span className="font-medium text-slate-900 dark:text-white">{transaction.paymentMethod}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-slate-500 dark:text-slate-400">Local</span>
                  <span className="font-medium text-slate-900 dark:text-white">{transaction.location || "-"}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-500 dark:text-slate-400">Data</span>
                  <span className="font-medium text-slate-900 dark:text-white">{transaction.date || "-"}</span>
                </div>
                <div className="flex justify-between border-t border-slate-100 pt-2 dark:border-slate-800">
                  <span className="font-semibold text-slate-700 dark:text-slate-300">Valor</span>
                  <span className={`text-lg font-bold ${activeType === "entry" ? "text-emerald-600" : "text-red-600"}`}>
                    {currency(transaction.amount)}
                  </span>
                </div>
              </div>

              <div className="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                <button
                  className="rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200 dark:hover:bg-blue-500/20"
                  onClick={() => onEdit(transaction)}
                  type="button"
                >
                  Editar
                </button>
                <button
                  className="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                  onClick={onStatusUnavailable}
                  title="Funcionalidade em desenvolvimento."
                  type="button"
                >
                  Status
                </button>
                <button
                  className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20"
                  disabled={isDeleting}
                  onClick={() => onDelete(transaction)}
                  type="button"
                >
                  Excluir
                </button>
              </div>
            </div>
          );
        })}
        {transactions.length === 0 && (
          <div className="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
            {emptyLabel}
          </div>
        )}
      </div>

      {/* Desktop: Table */}
      <div className="hidden overflow-x-auto md:block">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500 dark:border-slate-800 dark:text-slate-400">
            <tr>
              <th className="w-12 py-3 pr-4">
                <input
                  aria-label="Selecionar todos os itens desta página"
                  checked={allSelected}
                  className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-700/20"
                  disabled={selectableIds.length === 0}
                  onChange={(event) => onSelectAll(event.target.checked)}
                  ref={checkboxRef}
                  type="checkbox"
                />
              </th>
              <th className="py-3 pr-4 font-semibold">Descrição</th>
              <th className="py-3 pr-4 font-semibold">Categoria</th>
              {activeType === "expense" && <th className="py-3 pr-4 font-semibold">Método</th>}
              <th className="py-3 pr-4 font-semibold">Local</th>
              <th className="py-3 pr-4 font-semibold">Data</th>
              <th className="py-3 text-right font-semibold">Valor</th>
              <th className="py-3 pl-4 text-right font-semibold">Ações</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
            {transactions.map((transaction) => {
              const rowId = transaction.resourceId;
              const isSelected = typeof rowId === "number" && selectedIds.has(rowId);

              return (
                <tr className={isSelected ? "bg-blue-50/70 dark:bg-blue-500/10" : undefined} key={transaction.id}>
                  <td className="py-3 pr-4">
                    <input
                      aria-label={`Selecionar ${transaction.description}`}
                      checked={isSelected}
                      className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-700/20"
                      disabled={typeof rowId !== "number" || isDeleting}
                      onChange={(event) => {
                        if (typeof rowId === "number") {
                          onSelect(rowId, event.target.checked);
                        }
                      }}
                      type="checkbox"
                    />
                  </td>
                  <td className="py-3 pr-4 font-medium text-slate-900 dark:text-white">{transaction.description}</td>
                  <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.category}</td>
                  {activeType === "expense" && <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.paymentMethod}</td>}
                  <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.location || "-"}</td>
                  <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">{transaction.date || "-"}</td>
                  <td className={`py-3 text-right font-semibold ${activeType === "entry" ? "text-emerald-600" : "text-red-600"}`}>
                    {currency(transaction.amount)}
                  </td>
                  <td className="py-3 pl-4">
                    <div className="grid grid-cols-3 gap-2">
                      <button
                        className="rounded-md border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:text-blue-200 dark:hover:bg-blue-500/10"
                        onClick={() => onEdit(transaction)}
                        type="button"
                      >
                        Editar
                      </button>
                      <button
                        className="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 transition hover:-translate-y-0.5 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400/20 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        onClick={onStatusUnavailable}
                        title="Funcionalidade em desenvolvimento."
                        type="button"
                      >
                        Status
                      </button>
                      <button
                        className="rounded-md border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:-translate-y-0.5 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10"
                        disabled={isDeleting}
                        onClick={() => onDelete(transaction)}
                        type="button"
                      >
                        Excluir
                      </button>
                    </div>
                  </td>
                </tr>
              );
            })}
            {transactions.length === 0 && (
              <tr>
                <td className="py-8 text-center text-slate-500 dark:text-slate-400" colSpan={activeType === "expense" ? 8 : 7}>
                  {emptyLabel}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      <TransactionsPagination {...pagination} />
    </section>
  );
}

type TransactionsPaginationProps = {
  currentPage: number;
  endItem: number;
  onPageChange: (page: number) => void;
  onPageSizeChange: (pageSize: number) => void;
  pageSize: number;
  startItem: number;
  totalItems: number;
  totalPages: number;
};

function TransactionsPagination({
  currentPage,
  endItem,
  onPageChange,
  onPageSizeChange,
  pageSize,
  startItem,
  totalItems,
  totalPages,
}: TransactionsPaginationProps) {
  const canGoPrevious = currentPage > 1;
  const canGoNext = currentPage < totalPages;

  return (
    <div className="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:text-slate-300">
      <div>
        {totalItems > 0 ? (
          <span>
            Exibindo {startItem}-{endItem} de {totalItems}
          </span>
        ) : (
          <span>Nenhum item para paginar</span>
        )}
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <label className="flex items-center gap-2">
          <span>Por página</span>
          <Select
            className="py-1.5"
            onChange={(event) => onPageSizeChange(Number(event.target.value))}
            value={pageSize}
          >
            {[5, 10, 20, 50].map((option) => (
              <option key={option} value={option}>
                {option}
              </option>
            ))}
          </Select>
        </label>

        <div className="flex items-center gap-2">
          <button
            className="rounded-md border border-slate-200 px-3 py-2 font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            disabled={!canGoPrevious}
            onClick={() => onPageChange(currentPage - 1)}
            type="button"
          >
            Anterior
          </button>
          <span className="min-w-24 text-center font-semibold text-slate-700 dark:text-slate-200">
            {currentPage} / {totalPages}
          </span>
          <button
            className="rounded-md border border-slate-200 px-3 py-2 font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            disabled={!canGoNext}
            onClick={() => onPageChange(currentPage + 1)}
            type="button"
          >
            Próxima
          </button>
        </div>
      </div>
    </div>
  );
}
