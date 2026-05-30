import { useEffect, useMemo, useState } from "react";

import { currency } from "../dashboard/dashboardMetrics";
import type { AuxiliaryCatalogItem, AuxiliaryCatalogType } from "../../Infrastructure/Api/catalogs";
import type { CatalogUsageStat } from "./AuxiliaryCatalogCharts";
import { AuxiliaryItemsFilters, emptyAuxiliaryFilters, type AuxiliaryFilterValues } from "./AuxiliaryItemsFilters";
import { Select } from "../shared/Select";

type AuxiliaryItemsGridProps = {
  activeType: AuxiliaryCatalogType;
  canDeleteItem: (item: AuxiliaryCatalogItem) => boolean;
  canEditItem: (item: AuxiliaryCatalogItem) => boolean;
  emptyLabel: string;
  isMutating: boolean;
  items: AuxiliaryCatalogItem[];
  stats: CatalogUsageStat[];
  onDelete: (item: AuxiliaryCatalogItem) => void;
  onEdit: (item: AuxiliaryCatalogItem) => void;
};

const labels: Record<AuxiliaryCatalogType, string> = {
  entryType: "tipo de entrada",
  expenseType: "tipo de despesa",
  paymentMethod: "método de pagamento",
};

const pageSizeOptions = [10, 20, 50];

export function AuxiliaryItemsGrid({
  activeType,
  canDeleteItem,
  canEditItem,
  emptyLabel,
  isMutating,
  items,
  stats,
  onDelete,
  onEdit,
}: AuxiliaryItemsGridProps) {
  const [filters, setFilters] = useState<AuxiliaryFilterValues>(emptyAuxiliaryFilters);
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const statsById = useMemo(() => new Map(stats.map((stat) => [stat.id, stat])), [stats]);
  const normalizedNameFilter = filters.name.trim().toLowerCase();
  const filteredItems = useMemo(
    () => items.filter((item) => {
      const matchesName = normalizedNameFilter === "" || item.name.toLowerCase().includes(normalizedNameFilter);
      const matchesOrigin = filters.origin === ""
        || (filters.origin === "default" && item.isDefault)
        || (filters.origin === "custom" && !item.isDefault);

      return matchesName && matchesOrigin;
    }),
    [items, normalizedNameFilter, filters.origin],
  );
  const totalPages = Math.max(1, Math.ceil(filteredItems.length / pageSize));
  const safeCurrentPage = Math.min(currentPage, totalPages);
  const pageStartIndex = (safeCurrentPage - 1) * pageSize;
  const paginatedItems = filteredItems.slice(pageStartIndex, pageStartIndex + pageSize);
  const hasActionColumn = filteredItems.some((item) => canEditItem(item) || canDeleteItem(item));
  const visibleStartItem = filteredItems.length === 0 ? 0 : pageStartIndex + 1;
  const visibleEndItem = Math.min(pageStartIndex + pageSize, filteredItems.length);
  const emptyFilteredLabel = items.length === 0
    ? emptyLabel
    : "Nenhum item encontrado com os filtros atuais.";

  useEffect(() => {
    setCurrentPage(1);
  }, [activeType, items.length, filters, pageSize]);

  function handleFilterChange(newFilters: AuxiliaryFilterValues) {
    setFilters(newFilters);
    setCurrentPage(1);
  }

  function handleFilterReset() {
    setFilters(emptyAuxiliaryFilters);
    setCurrentPage(1);
  }

  return (
    <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4">
        <div className="mb-4">
          <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
            Gestão de {labels[activeType]}s
          </h2>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Edite o cadastro auxiliar e acompanhe o uso em transações.
          </p>
        </div>

        <AuxiliaryItemsFilters
          filteredCount={filteredItems.length}
          filters={filters}
          onChange={handleFilterChange}
          onReset={handleFilterReset}
          totalCount={items.length}
        />
      </div>

      {/* Mobile: Cards */}
      <div className="space-y-3 md:hidden">
        {paginatedItems.map((item) => {
          const usage = statsById.get(item.id);
          const transactionCount = usage?.transactionCount ?? 0;
          const totalAmount = usage?.totalAmount ?? 0;
          const canEdit = canEditItem(item);
          const canDelete = canDeleteItem(item);

          return (
            <div
              key={item.id}
              className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
              <div className="mb-3 flex items-start justify-between">
                <div className="flex-1">
                  <h3 className="font-semibold text-slate-900 dark:text-white">{item.name}</h3>
                  {item.isDefault && (
                    <span className="mt-1 inline-block rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-blue-700 dark:bg-blue-500/10 dark:text-blue-200">
                      Padrão
                    </span>
                  )}
                </div>
              </div>

              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-slate-500 dark:text-slate-400">Uso em transações</span>
                  <span className="font-medium text-slate-900 dark:text-white">
                    {transactionCount} transação(ões)
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-500 dark:text-slate-400">Valor movimentado</span>
                  <span className="font-semibold text-slate-900 dark:text-white">{currency(totalAmount)}</span>
                </div>
              </div>

              {(canEdit || canDelete) && (
                <div className="mt-4 flex gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                  {canEdit && (
                    <button
                      className="flex-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200 dark:hover:bg-blue-500/20"
                      onClick={() => onEdit(item)}
                      type="button"
                    >
                      Editar
                    </button>
                  )}
                  {canDelete && (
                    <button
                      className="flex-1 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20"
                      disabled={isMutating}
                      onClick={() => onDelete(item)}
                      type="button"
                    >
                      Excluir
                    </button>
                  )}
                </div>
              )}
            </div>
          );
        })}
        {paginatedItems.length === 0 && (
          <div className="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
            {emptyFilteredLabel}
          </div>
        )}
      </div>

      {/* Desktop: Table */}
      <div className="hidden overflow-x-auto md:block">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500 dark:border-slate-800 dark:text-slate-400">
            <tr>
              <th className="py-3 pr-4 font-semibold">Nome</th>
              <th className="py-3 pr-4 font-semibold">Uso em transações</th>
              <th className="py-3 pr-4 font-semibold">Valor movimentado</th>
              {hasActionColumn && <th className="py-3 text-right font-semibold">Ações</th>}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
            {paginatedItems.map((item) => {
              const usage = statsById.get(item.id);
              const transactionCount = usage?.transactionCount ?? 0;
              const totalAmount = usage?.totalAmount ?? 0;
              const canEdit = canEditItem(item);
              const canDelete = canDeleteItem(item);

              return (
                <tr key={item.id}>
                  <td className="py-3 pr-4">
                    <div className="flex flex-col gap-1">
                      <span className="font-medium text-slate-900 dark:text-white">{item.name}</span>
                      {item.isDefault && (
                        <span className="w-fit rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-blue-700 dark:bg-blue-500/10 dark:text-blue-200">
                          Padrão
                        </span>
                      )}
                    </div>
                  </td>
                  <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">
                    {transactionCount} transação(ões)
                  </td>
                  <td className="py-3 pr-4 font-semibold text-slate-900 dark:text-white">
                    {currency(totalAmount)}
                  </td>
                  {hasActionColumn && (
                    <td className="py-3">
                      <div className="flex justify-end gap-2">
                        {canEdit && (
                          <button
                            className="rounded-md border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:text-blue-200 dark:hover:bg-blue-500/10"
                            onClick={() => onEdit(item)}
                            type="button"
                          >
                            Editar
                          </button>
                        )}
                        {canDelete && (
                          <button
                            className="rounded-md border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:-translate-y-0.5 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10"
                            disabled={isMutating}
                            onClick={() => onDelete(item)}
                            type="button"
                          >
                            Excluir
                          </button>
                        )}
                      </div>
                    </td>
                  )}
                </tr>
              );
            })}
            {paginatedItems.length === 0 && (
              <tr>
                <td className="py-8 text-center text-slate-500 dark:text-slate-400" colSpan={hasActionColumn ? 4 : 3}>
                  {emptyFilteredLabel}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      <div className="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:text-slate-300">
        <div>
          {filteredItems.length > 0 ? (
            <span>
              Exibindo {visibleStartItem}-{visibleEndItem} de {filteredItems.length}
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
              onChange={(event) => setPageSize(Number(event.target.value))}
              value={pageSize}
            >
              {pageSizeOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </Select>
          </label>

          <div className="flex items-center gap-2">
            <button
              className="rounded-md border border-slate-200 px-3 py-2 font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
              disabled={safeCurrentPage <= 1}
              onClick={() => setCurrentPage((page) => Math.max(1, page - 1))}
              type="button"
            >
              Anterior
            </button>
            <span className="min-w-24 text-center font-semibold text-slate-700 dark:text-slate-200">
              {safeCurrentPage} / {totalPages}
            </span>
            <button
              className="rounded-md border border-slate-200 px-3 py-2 font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
              disabled={safeCurrentPage >= totalPages}
              onClick={() => setCurrentPage((page) => Math.min(totalPages, page + 1))}
              type="button"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>
    </section>
  );
}
