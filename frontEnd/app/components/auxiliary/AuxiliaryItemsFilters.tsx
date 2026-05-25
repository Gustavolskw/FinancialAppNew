import { useMemo } from "react";

import { DynamicFilters, type FilterDefinition } from "../shared/DynamicFilters";

export type AuxiliaryFilterValues = {
  name: string;
  origin: string;
};

export const emptyAuxiliaryFilters: AuxiliaryFilterValues = {
  name: "",
  origin: "",
};

type AuxiliaryItemsFiltersProps = {
  disabled?: boolean;
  filteredCount: number;
  filters: AuxiliaryFilterValues;
  onChange: (filters: AuxiliaryFilterValues) => void;
  onReset: () => void;
  totalCount: number;
};

export function AuxiliaryItemsFilters({
  disabled = false,
  filteredCount,
  filters,
  onChange,
  onReset,
  totalCount,
}: AuxiliaryItemsFiltersProps) {
  const availableFilters = useMemo<FilterDefinition[]>(() => {
    return [
      {
        key: "name",
        label: "Buscar",
        type: "text",
        placeholder: "Nome do item",
      },
      {
        key: "origin",
        label: "Origem",
        type: "select",
        placeholder: "Todos",
        options: [
          { label: "Padrão", value: "default" },
          { label: "Personalizados", value: "custom" },
        ],
      },
    ];
  }, []);

  return (
    <DynamicFilters
      availableFilters={availableFilters}
      disabled={disabled}
      filteredCount={filteredCount}
      filters={filters}
      onChange={onChange}
      onReset={onReset}
      title="Filtros de busca"
      totalCount={totalCount}
    />
  );
}
