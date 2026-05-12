import { currency } from "../dashboard/dashboardMetrics";
import type { AuxiliaryCatalogItem, AuxiliaryCatalogType } from "../../Infrastructure/Api/catalogs";
import type { CatalogUsageStat } from "./AuxiliaryCatalogCharts";

type AuxiliaryItemsGridProps = {
  activeType: AuxiliaryCatalogType;
  canDelete: boolean;
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

export function AuxiliaryItemsGrid({
  activeType,
  canDelete,
  emptyLabel,
  isMutating,
  items,
  stats,
  onDelete,
  onEdit,
}: AuxiliaryItemsGridProps) {
  const statsById = new Map(stats.map((stat) => [stat.id, stat]));

  return (
    <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4">
        <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
          Gestão de {labels[activeType]}s
        </h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">
          Edite o cadastro auxiliar e acompanhe o uso em transações.
        </p>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full min-w-[760px] text-left text-sm">
          <thead className="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500 dark:border-slate-800 dark:text-slate-400">
            <tr>
              <th className="py-3 pr-4 font-semibold">Nome</th>
              <th className="py-3 pr-4 font-semibold">Uso em transações</th>
              <th className="py-3 pr-4 font-semibold">Valor movimentado</th>
              <th className="py-3 text-right font-semibold">Ações</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
            {items.map((item) => {
              const usage = statsById.get(item.id);
              const transactionCount = usage?.transactionCount ?? 0;
              const totalAmount = usage?.totalAmount ?? 0;

              return (
                <tr key={item.id}>
                  <td className="py-3 pr-4 font-medium text-slate-900 dark:text-white">{item.name}</td>
                  <td className="py-3 pr-4 text-slate-600 dark:text-slate-300">
                    {transactionCount} transação(ões)
                  </td>
                  <td className="py-3 pr-4 font-semibold text-slate-900 dark:text-white">
                    {currency(totalAmount)}
                  </td>
                  <td className="py-3">
                    <div className="flex justify-end gap-2">
                      <button
                        className="rounded-md border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:text-blue-200 dark:hover:bg-blue-500/10"
                        onClick={() => onEdit(item)}
                        type="button"
                      >
                        Editar
                      </button>
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
                </tr>
              );
            })}
            {items.length === 0 && (
              <tr>
                <td className="py-8 text-center text-slate-500 dark:text-slate-400" colSpan={4}>
                  {emptyLabel}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </section>
  );
}
