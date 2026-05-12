import type { FieldOption } from "../../Infrastructure/DTO/EntityAttributes";
import type { TransactionType } from "../../Infrastructure/Api/movements";
import type { TransactionFilterValues } from "./transactionFiltering";

type TransactionsGridFiltersProps = {
  activeType: TransactionType;
  categoryOptions: FieldOption[];
  filteredCount: number;
  filters: TransactionFilterValues;
  isLoading: boolean;
  onChange: (filters: TransactionFilterValues) => void;
  onReset: () => void;
  paymentMethodOptions: FieldOption[];
  totalCount: number;
};

export function TransactionsGridFilters({
  activeType,
  categoryOptions,
  filteredCount,
  filters,
  isLoading,
  onChange,
  onReset,
  paymentMethodOptions,
  totalCount,
}: TransactionsGridFiltersProps) {
  function updateFilter(name: keyof TransactionFilterValues, value: string) {
    onChange({
      ...filters,
      [name]: value,
    });
  }

  return (
    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="text-base font-semibold text-slate-950 dark:text-white">Filtros da listagem</h2>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            {filteredCount} de {totalCount} item(ns) no mês selecionado
          </p>
        </div>
        <button
          className="w-fit rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
          disabled={isLoading}
          onClick={onReset}
          type="button"
        >
          Limpar filtros
        </button>
      </div>

      <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <FilterTextInput
          disabled={isLoading}
          label="Nome ou descrição"
          onChange={(value) => updateFilter("search", value)}
          placeholder="Buscar por nome, descrição, tipo..."
          value={filters.search}
        />
        <FilterSelect
          disabled={isLoading}
          label={activeType === "entry" ? "Tipo de entrada" : "Tipo de despesa"}
          onChange={(value) => updateFilter("categoryId", value)}
          options={categoryOptions}
          placeholder="Todos os tipos"
          value={filters.categoryId}
        />
        {activeType === "expense" && (
          <FilterSelect
            disabled={isLoading}
            label="Método de pagamento"
            onChange={(value) => updateFilter("paymentMethodId", value)}
            options={paymentMethodOptions}
            placeholder="Todos os métodos"
            value={filters.paymentMethodId}
          />
        )}
        <FilterTextInput
          disabled={isLoading}
          label="Local"
          onChange={(value) => updateFilter("location", value)}
          placeholder="Ex.: Mercado, Empresa"
          value={filters.location}
        />
        <FilterTextInput
          disabled={isLoading}
          label="Data"
          onChange={(value) => updateFilter("date", value)}
          type="date"
          value={filters.date}
        />
        <FilterTextInput
          disabled={isLoading}
          label="Valor mínimo"
          onChange={(value) => updateFilter("minAmount", value)}
          placeholder="0,00"
          type="number"
          value={filters.minAmount}
        />
        <FilterTextInput
          disabled={isLoading}
          label="Valor máximo"
          onChange={(value) => updateFilter("maxAmount", value)}
          placeholder="0,00"
          type="number"
          value={filters.maxAmount}
        />
        {activeType === "expense" && (
          <FilterTextInput
            disabled={isLoading}
            label="Parcelas"
            min="1"
            onChange={(value) => updateFilter("installments", value)}
            placeholder="1"
            type="number"
            value={filters.installments}
          />
        )}
      </div>
    </div>
  );
}

type FilterTextInputProps = {
  disabled: boolean;
  label: string;
  min?: string;
  onChange: (value: string) => void;
  placeholder?: string;
  type?: "date" | "number" | "text";
  value: string;
};

function FilterTextInput({
  disabled,
  label,
  min,
  onChange,
  placeholder,
  type = "text",
  value,
}: FilterTextInputProps) {
  return (
    <label className="flex flex-col gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
      <span>{label}</span>
      <input
        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
        disabled={disabled}
        min={min}
        onChange={(event) => onChange(event.target.value)}
        placeholder={placeholder}
        step={type === "number" ? "0.01" : undefined}
        type={type}
        value={value}
      />
    </label>
  );
}

type FilterSelectProps = {
  disabled: boolean;
  label: string;
  onChange: (value: string) => void;
  options: FieldOption[];
  placeholder: string;
  value: string;
};

function FilterSelect({ disabled, label, onChange, options, placeholder, value }: FilterSelectProps) {
  return (
    <label className="flex flex-col gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
      <span>{label}</span>
      <select
        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
        disabled={disabled}
        onChange={(event) => onChange(event.target.value)}
        value={value}
      >
        <option value="">{placeholder}</option>
        {options.map((option) => (
          <option key={String(option.value)} value={String(option.value)}>
            {option.label}
          </option>
        ))}
      </select>
    </label>
  );
}
