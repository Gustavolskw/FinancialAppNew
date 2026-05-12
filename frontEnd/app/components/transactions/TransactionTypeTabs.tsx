import type { TransactionType } from "../../Infrastructure/Api/movements";

type TransactionTypeTabsProps = {
  activeType: TransactionType;
  entryCount: number;
  expenseCount: number;
  onChange: (type: TransactionType) => void;
};

export function TransactionTypeTabs({ activeType, entryCount, expenseCount, onChange }: TransactionTypeTabsProps) {
  const tabs = [
    { count: entryCount, label: "Entradas", type: "entry" as const },
    { count: expenseCount, label: "Saídas", type: "expense" as const },
  ];

  return (
    <div className="inline-grid rounded-lg border border-slate-200 bg-white p-1 shadow-sm sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-900">
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
              {tab.count}
            </span>
          </button>
        );
      })}
    </div>
  );
}
