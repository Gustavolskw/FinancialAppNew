import { useEffect, useMemo, useState } from "react";

import { currency } from "../dashboard/dashboardMetrics";
import type { AuxiliaryCatalogItem, AuxiliaryCatalogType } from "../../Infrastructure/Api/catalogs";
import type { CatalogUsageStat } from "./AuxiliaryCatalogCharts";

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

type OriginFilter = "all" | "default" | "custom";

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
  const [nameFilter, setNameFilter] = useState("");
  const [originFilter, setOriginFilter] = useState<OriginFilter>("all");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const statsById = useMemo(() => new Map(stats.map((stat) => [stat.id, stat])), [stats]);
  const normalizedNameFilter = nameFilter.trim().toLowerCase();
  const filteredItems = useMemo(
    () => items.filter((item) => {
      const matchesName = normalizedNameFilter === "" || item.name.toLowerCase().includes(normalizedNameFilter);
      const matchesOrigin = originFilter === "all"
        || (originFilter === "default" && item.isDefault)
        || (originFilter === "custom" && !item.isDefault);

      return matchesName && matchesOrigin;
    }),
    [items, normalizedNameFilter, originFilter],
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
  }, [activeType, items.length, nameFilter, originFilter, pageSize]);

  return (
    <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
          <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
            Gestão de {labels[activeType]}s
          </h2>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Edite o cadastro auxiliar e acompanhe o uso em transações.
          </p>
        </div>

        <div className="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_180px_140px] xl:w-[620px]">
          <label className="flex flex-col gap-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
            Buscar
            <input
              className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium normal-case tracking-normal text-slate-900 outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
              onChange={(event) => setNameFilter(event.target.value)}
              placeholder="Nome do item"
              type="search"
              value={nameFilter}
            />
          </label>

          <label className="flex flex-col gap-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
            Origem
            <select
              className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium normal-case tracking-normal text-slate-900 outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
              onChange={(event) => setOriginFilter(event.target.value as OriginFilter)}
              value={originFilter}
            >
              <option value="all">Todos</option>
              <option value="default">Padrão</option>
              <option value="custom">Personalizados</option>
            </select>
          </label>

          <label className="flex flex-col gap-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
            Por página
            <select
              className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium normal-case tracking-normal text-slate-900 outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
              onChange={(event) => setPageSize(Number(event.target.value))}
              value={pageSize}
            >
              {pageSizeOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>
          </label>
        </div>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full min-w-[760px] text-left text-sm">
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
        <span>
          Mostrando {visibleStartItem}-{visibleEndItem} de {filteredItems.length} item(ns)
        </span>

        <div className="flex items-center gap-2">
          <button
            className="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            disabled={safeCurrentPage <= 1}
            onClick={() => setCurrentPage((page) => Math.max(1, page - 1))}
            type="button"
          >
            Anterior
          </button>
          <span className="min-w-20 text-center text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
            {safeCurrentPage} / {totalPages}
          </span>
          <button
            className="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            disabled={safeCurrentPage >= totalPages}
            onClick={() => setCurrentPage((page) => Math.min(totalPages, page + 1))}
            type="button"
          >
            Próxima
          </button>
        </div>
      </div>
    </section>
  );
}
