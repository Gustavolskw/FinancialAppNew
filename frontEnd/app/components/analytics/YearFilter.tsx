import { Select } from "../shared/Select";

type YearFilterProps = {
  availableYears: number[];
  disabled?: boolean;
  onChange: (year: number) => void;
  value: number;
};

export function YearFilter({ availableYears, disabled = false, onChange, value }: YearFilterProps) {
  return (
    <label className="flex w-full flex-col gap-1.5 text-sm font-semibold text-slate-700 sm:w-40 dark:text-slate-200">
      <span>Ano</span>
      <Select
        disabled={disabled}
        onChange={(e) => onChange(Number(e.target.value))}
        value={value}
      >
        {availableYears.map((y) => (
          <option key={y} value={y}>{y}</option>
        ))}
      </Select>
    </label>
  );
}
