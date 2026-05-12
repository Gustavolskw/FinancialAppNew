import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";

export type TransactionFilterValues = {
  categoryId: string;
  date: string;
  installments: string;
  location: string;
  maxAmount: string;
  minAmount: string;
  paymentMethodId: string;
  search: string;
};

export const emptyTransactionFilters: TransactionFilterValues = {
  categoryId: "",
  date: "",
  installments: "",
  location: "",
  maxAmount: "",
  minAmount: "",
  paymentMethodId: "",
  search: "",
};

export function applyTransactionFilters(
  transactions: DashboardTransaction[],
  filters: TransactionFilterValues,
): DashboardTransaction[] {
  const search = normalizeText(filters.search);
  const location = normalizeText(filters.location);
  const minAmount = numberFilter(filters.minAmount);
  const maxAmount = numberFilter(filters.maxAmount);

  return transactions.filter((transaction) => {
    if (search && !transactionSearchText(transaction).includes(search)) {
      return false;
    }

    if (filters.categoryId && String(transaction.categoryId ?? "") !== filters.categoryId) {
      return false;
    }

    if (filters.paymentMethodId && String(transaction.paymentMethodId ?? "") !== filters.paymentMethodId) {
      return false;
    }

    if (location && !normalizeText(transaction.location).includes(location)) {
      return false;
    }

    if (filters.date && transactionDateInputValue(transaction.date) !== filters.date) {
      return false;
    }

    if (filters.installments && String(transaction.installments ?? "") !== filters.installments) {
      return false;
    }

    if (minAmount !== null && transaction.amount < minAmount) {
      return false;
    }

    if (maxAmount !== null && transaction.amount > maxAmount) {
      return false;
    }

    return true;
  });
}

function transactionSearchText(transaction: DashboardTransaction): string {
  return normalizeText([
    transaction.description,
    transaction.category,
    transaction.paymentMethod,
    transaction.location,
    transaction.date,
    String(transaction.amount),
  ].join(" "));
}

function normalizeText(value: string): string {
  return value
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim();
}

function numberFilter(value: string): number | null {
  if (value.trim() === "") {
    return null;
  }

  const normalized = value.replace(/\./g, "").replace(",", ".");
  const parsed = Number(normalized);

  return Number.isFinite(parsed) ? parsed : null;
}

function transactionDateInputValue(date: string): string {
  if (!date) {
    return "";
  }

  const brDateMatch = /^(\d{2})\/(\d{2})\/(\d{4})/.exec(date);
  if (brDateMatch) {
    const [, day, month, year] = brDateMatch;

    return `${year}-${month}-${day}`;
  }

  return date.slice(0, 10);
}
