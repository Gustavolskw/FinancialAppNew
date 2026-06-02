import { useState } from "react";

import type { FieldOption } from "../../Infrastructure/DTO/EntityAttributes";

function PlusIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      fill="none"
      stroke="currentColor"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth="2"
      viewBox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M12 5v14M5 12h14" />
    </svg>
  );
}

function XIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      fill="none"
      stroke="currentColor"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth="2"
      viewBox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M18 6 6 18M6 6l12 12" />
    </svg>
  );
}

function ChevronDownIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      fill="none"
      stroke="currentColor"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth="2"
      viewBox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="m6 9 6 6 6-6" />
    </svg>
  );
}

export type FilterDefinition = {
  key: string;
  label: string;
  type: "text" | "select" | "date" | "number";
  placeholder?: string;
  options?: FieldOption[];
  min?: string;
};

type ActiveFilter = {
  key: string;
  definition: FilterDefinition;
};

type FilterDropdownMenuProps = {
  availableFilters: FilterDefinition[];
  onAddFilter: (definition: FilterDefinition) => void;
  onClose: () => void;
};

function FilterDropdownMenu({ availableFilters, onAddFilter, onClose }: FilterDropdownMenuProps) {
  return (
    <>
      <div className="fixed inset-0 z-40" onClick={onClose} />
      <div className="absolute right-0 top-full z-50 mt-2 w-64 rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
        <div className="p-2">
          <p className="mb-2 px-2 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
            Filtros disponíveis
          </p>
          <div className="space-y-1">
            {availableFilters.map((def) => (
              <button
                key={def.key}
                className="w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                onClick={() => onAddFilter(def)}
                type="button"
              >
                {def.label}
              </button>
            ))}
          </div>
        </div>
      </div>
    </>
  );
}

type FiltersHeaderProps = {
  disabled: boolean;
  filteredCount: number;
  hasAvailableFilters: boolean;
  onReset: () => void;
  onToggleMenu: () => void;
  showMenu: boolean;
  title: string;
  totalCount: number;
};

function FiltersHeader({
  disabled,
  filteredCount,
  hasAvailableFilters,
  onReset,
  onToggleMenu,
  showMenu,
  title,
  totalCount,
}: FiltersHeaderProps) {
  return (
    <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 className="text-base font-semibold text-slate-950 dark:text-white">{title}</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">
          {filteredCount} de {totalCount} item(ns)
        </p>
      </div>
      <div className="flex gap-2">
        <button
          className="w-fit rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
          disabled={disabled}
          onClick={onReset}
          type="button"
        >
          Limpar filtros
        </button>
        <div className="relative">
          <button
            className="flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200 dark:hover:bg-blue-500/20"
            disabled={disabled || !hasAvailableFilters}
            onClick={onToggleMenu}
            type="button"
          >
            <PlusIcon className="h-4 w-4" />
            <span>Adicionar filtro</span>
          </button>
          {showMenu && <div className="relative" />}
        </div>
      </div>
    </div>
  );
}

type FiltersGridProps = {
  activeFilters: ActiveFilter[];
  disabled: boolean;
  onFilterChange: (key: string, value: string) => void;
  onFilterRemove: (key: string) => void;
  filters: Record<string, string>;
};

function FiltersGrid({ activeFilters, disabled, onFilterChange, onFilterRemove, filters }: FiltersGridProps) {
  return (
    <div className="px-2 sm:px-0">
      <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        {activeFilters.map((active) => (
          <FilterItem
            key={active.key}
            definition={active.definition}
            disabled={disabled}
            onChange={(value) => onFilterChange(active.key, value)}
            onRemove={() => onFilterRemove(active.key)}
            value={filters[active.key] as string}
          />
        ))}
      </div>
    </div>
  );
}

type FilterItemProps = {
  definition: FilterDefinition;
  disabled: boolean;
  onChange: (value: string) => void;
  onRemove: () => void;
  value: string;
};

function FilterItem({ definition, disabled, onChange, onRemove, value }: FilterItemProps) {
  return (
    <div>
      <div className="flex items-end gap-2">
        <div className="flex-1 min-w-0 w-50">
          <FilterInput
            definition={definition}
            disabled={disabled}
            onChange={onChange}
            value={value}
          />
        </div>
        <button
          className="mb-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-700/20 sm:h-8 sm:w-8 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200 dark:hover:bg-red-500/20"
          onClick={onRemove}
          title={`Remover filtro ${definition.label}`}
          type="button"
        >
          <XIcon className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}

type DynamicFiltersProps<T extends Record<string, string>> = {
  availableFilters: FilterDefinition[];
  disabled?: boolean;
  filteredCount: number;
  filters: T;
  onChange: (filters: T) => void;
  onReset: () => void;
  title?: string;
  totalCount: number;
};

export function DynamicFilters<T extends Record<string, string>>({
  availableFilters,
  disabled = false,
  filteredCount,
  filters,
  onChange,
  onReset,
  title = "Filtros da listagem",
  totalCount,
}: DynamicFiltersProps<T>) {
  const [activeFilters, setActiveFilters] = useState<ActiveFilter[]>(() => {
    return availableFilters
      .filter((def) => filters[def.key as keyof T] !== "")
      .map((def) => ({ key: def.key, definition: def }));
  });
  const [showFilterMenu, setShowFilterMenu] = useState(false);

  const availableToAdd = availableFilters.filter(
    (def) => !activeFilters.some((active) => active.key === def.key),
  );

  function addFilter(definition: FilterDefinition) {
    setActiveFilters((prev) => [...prev, { key: definition.key, definition }]);
    setShowFilterMenu(false);
  }

  function removeFilter(key: string) {
    setActiveFilters((prev) => prev.filter((filter) => filter.key !== key));
    onChange({
      ...filters,
      [key]: "",
    } as T);
  }

  function updateFilter(key: string, value: string) {
    onChange({
      ...filters,
      [key]: value,
    } as T);
  }

  function handleReset() {
    setActiveFilters([]);
    onReset();
  }

  if (activeFilters.length === 0) {
    return (
      <div className="flex justify-end">
        <div className="relative">
          <button
            className="flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-700/20 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200 dark:hover:bg-blue-500/20"
            disabled={disabled}
            onClick={() => setShowFilterMenu(!showFilterMenu)}
            type="button"
          >
            <PlusIcon className="h-4 w-4" />
            <span>Adicionar filtro</span>
          </button>

          {showFilterMenu && availableToAdd.length > 0 && (
            <FilterDropdownMenu
              availableFilters={availableToAdd}
              onAddFilter={addFilter}
              onClose={() => setShowFilterMenu(false)}
            />
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="overflow-visible rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <FiltersHeader
        disabled={disabled}
        filteredCount={filteredCount}
        hasAvailableFilters={availableToAdd.length > 0}
        onReset={handleReset}
        onToggleMenu={() => setShowFilterMenu(!showFilterMenu)}
        showMenu={showFilterMenu}
        title={title}
        totalCount={totalCount}
      />
      {showFilterMenu && availableToAdd.length > 0 && (
        <div className="relative">
          <FilterDropdownMenu
            availableFilters={availableToAdd}
            onAddFilter={addFilter}
            onClose={() => setShowFilterMenu(false)}
          />
        </div>
      )}

      <FiltersGrid
        activeFilters={activeFilters}
        disabled={disabled}
        filters={filters as Record<string, string>}
        onFilterChange={updateFilter}
        onFilterRemove={removeFilter}
      />
    </div>
  );
}

type FilterInputProps = {
  definition: FilterDefinition;
  disabled: boolean;
  onChange: (value: string) => void;
  value: string;
};

function FilterInput({ definition, disabled, onChange, value }: FilterInputProps) {
  if (definition.type === "select" && definition.options) {
    return (
      <label className="flex flex-col gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
        <span>{definition.label}</span>
        <div className="relative">
          <select
            className="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-3 pl-4 pr-10 text-base text-slate-950 shadow-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 sm:py-2 sm:pl-3 sm:pr-9 sm:text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
            disabled={disabled}
            onChange={(event) => onChange(event.target.value)}
            value={value}
          >
            <option value="">{definition.placeholder ?? "Selecione..."}</option>
            {definition.options.map((option) => (
              <option key={String(option.value)} value={String(option.value)}>
                {option.label}
              </option>
            ))}
          </select>
          <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 dark:text-slate-500">
            <ChevronDownIcon className="h-5 w-5 sm:h-4 sm:w-4" />
          </div>
        </div>
      </label>
    );
  }

  return (
    <label className="flex flex-col gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
      <span>{definition.label}</span>
      <input
        className="rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 sm:px-3 sm:py-2 sm:text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
        disabled={disabled}
        min={definition.min}
        onChange={(event) => onChange(event.target.value)}
        placeholder={definition.placeholder}
        step={definition.type === "number" ? "0.01" : undefined}
        type={definition.type}
        value={value}
      />
    </label>
  );
}
