import { useMemo } from "react";

import type { FieldOption } from "../../Infrastructure/DTO/EntityAttributes";
import type { TransactionType } from "../../Infrastructure/Api/movements";
import type { TransactionFilterValues } from "./transactionFiltering";
import { DynamicFilters, type FilterDefinition } from "../shared/DynamicFilters";

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
  const availableFilters = useMemo<FilterDefinition[]>(() => {
    const baseFilters: FilterDefinition[] = [
      {
        key: "search",
        label: "Nome ou descrição",
        type: "text",
        placeholder: "Buscar por nome, descrição, tipo...",
      },
      {
        key: "categoryId",
        label: activeType === "entry" ? "Tipo de entrada" : "Tipo de despesa",
        type: "select",
        placeholder: "Todos os tipos",
        options: categoryOptions,
      },
      {
        key: "location",
        label: "Local",
        type: "text",
        placeholder: "Ex.: Mercado, Empresa",
      },
      {
        key: "date",
        label: "Data",
        type: "date",
      },
      {
        key: "minAmount",
        label: "Valor mínimo",
        type: "number",
        placeholder: "0,00",
      },
      {
        key: "maxAmount",
        label: "Valor máximo",
        type: "number",
        placeholder: "0,00",
      },
    ];

    if (activeType === "expense") {
      baseFilters.splice(2, 0, {
        key: "paymentMethodId",
        label: "Método de pagamento",
        type: "select",
        placeholder: "Todos os métodos",
        options: paymentMethodOptions,
      });
      baseFilters.push({
        key: "installments",
        label: "Parcelas",
        type: "number",
        placeholder: "1",
        min: "1",
      });
    }

    return baseFilters;
  }, [activeType, categoryOptions, paymentMethodOptions]);

  return (
    <DynamicFilters
      availableFilters={availableFilters}
      disabled={isLoading}
      filteredCount={filteredCount}
      filters={filters}
      onChange={onChange}
      onReset={onReset}
      title="Filtros da listagem"
      totalCount={totalCount}
    />
  );
}
