export type MonthFilterValue = {
  month: number;
  year: number;
};

type MonthFilterProps = {
  disabled?: boolean;
  label?: string;
  onChange: (value: MonthFilterValue) => void;
  value: MonthFilterValue;
};

function padMonth(month: number): string {
  return String(month).padStart(2, "0");
}

export function currentMonthFilter(date = new Date()): MonthFilterValue {
  return {
    month: date.getMonth() + 1,
    year: date.getFullYear(),
  };
}

export function monthInputValue(value: MonthFilterValue): string {
  return `${value.year}-${padMonth(value.month)}`;
}

export function monthFilterLabel(value: MonthFilterValue): string {
  return new Intl.DateTimeFormat("pt-BR", {
    month: "long",
    year: "numeric",
  }).format(new Date(value.year, value.month - 1, 1));
}

export function parseMonthInputValue(value: string): MonthFilterValue | null {
  const [year, month] = value.split("-").map(Number);

  if (!Number.isInteger(year) || !Number.isInteger(month) || month < 1 || month > 12) {
    return null;
  }

  return { month, year };
}

export function MonthFilter({ disabled = false, label = "Competência", onChange, value }: MonthFilterProps) {
  return (
    <label className="flex w-full flex-col gap-1.5 text-sm font-semibold text-slate-700 sm:w-56 dark:text-slate-200">
      <span>{label}</span>
      <input
        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
        disabled={disabled}
        onChange={(event) => {
          const nextValue = parseMonthInputValue(event.target.value);

          if (nextValue) {
            onChange(nextValue);
          }
        }}
        type="month"
        value={monthInputValue(value)}
      />
    </label>
  );
}
