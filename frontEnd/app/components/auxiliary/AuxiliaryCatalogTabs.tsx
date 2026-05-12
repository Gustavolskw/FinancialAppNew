import type { AuxiliaryCatalogType } from "../../Infrastructure/Api/catalogs";

type AuxiliaryCatalogTabsProps = {
  activeType: AuxiliaryCatalogType;
  counts: Record<AuxiliaryCatalogType, number>;
  onChange: (type: AuxiliaryCatalogType) => void;
};

const tabs: Array<{ label: string; type: AuxiliaryCatalogType }> = [
  { label: "Tipos de entrada", type: "entryType" },
  { label: "Métodos de pagamento", type: "paymentMethod" },
  { label: "Tipos de despesa", type: "expenseType" },
];

export function AuxiliaryCatalogTabs({ activeType, counts, onChange }: AuxiliaryCatalogTabsProps) {
  return (
    <div className="grid rounded-lg border border-slate-200 bg-white p-1 shadow-sm sm:grid-cols-3 dark:border-slate-800 dark:bg-slate-900">
      {tabs.map((tab) => {
        const isActive = activeType === tab.type;

        return (
          <button
            aria-pressed={isActive}
            className={`rounded-md px-4 py-2 text-sm font-semibold transition ${
              isActive
                ? "bg-blue-700 text-white shadow-sm shadow-blue-900/20"
                : "text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
            }`}
            key={tab.type}
            onClick={() => onChange(tab.type)}
            type="button"
          >
            {tab.label}
            <span className={`ml-2 rounded-full px-2 py-0.5 text-xs ${isActive ? "bg-white/15 text-white" : "bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300"}`}>
              {counts[tab.type]}
            </span>
          </button>
        );
      })}
    </div>
  );
}
