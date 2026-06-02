const MONTHS = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

type MonthChipsFilterProps = {
  selected: number[];
  onChange: (months: number[]) => void;
};

export function MonthChipsFilter({ selected, onChange }: MonthChipsFilterProps) {
  function toggle(month: number) {
    if (selected.includes(month)) {
      onChange(selected.filter((m) => m !== month));
    } else {
      onChange([...selected, month].sort((a, b) => a - b));
    }
  }

  return (
    <div className="flex flex-col gap-2">
      <span className="text-sm font-semibold text-slate-700 dark:text-slate-200">Filtrar por mês</span>
      <div className="flex flex-wrap items-center gap-2">
        {MONTHS.map((label, i) => {
          const month = i + 1;
          const active = selected.includes(month);
          return (
            <button
              key={month}
              className={`rounded-full border px-3 py-1 text-xs font-medium transition ${
                active
                  ? "border-blue-600 bg-blue-600 text-white dark:border-blue-400 dark:bg-blue-500"
                  : "border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-400"
              }`}
              onClick={() => toggle(month)}
              type="button"
            >
              {label}
            </button>
          );
        })}
        {selected.length > 0 && (
          <button
            className="rounded-full border border-slate-300 bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700"
            onClick={() => onChange([])}
            type="button"
          >
            Limpar
          </button>
        )}
      </div>
    </div>
  );
}
