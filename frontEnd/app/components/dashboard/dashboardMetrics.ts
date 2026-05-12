import type { DashboardData } from "../../Infrastructure/Api/dashboard";
import type { TransactionType } from "../../Infrastructure/Api/movements";

export type AmountStat = {
  label: string;
  total: number;
};

export type PeriodStat = {
  balance: number;
  entryTotal: number;
  expenseTotal: number;
  label: string;
  sortValue: number;
};

const monthNames = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

export function currency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    currency: "BRL",
    style: "currency",
  }).format(value);
}

export function sumByType(transactions: DashboardData["transactions"], type: TransactionType): number {
  return transactions
    .filter((transaction) => transaction.type === type)
    .reduce((total, transaction) => total + transaction.amount, 0);
}

export function periodStats(transactions: DashboardData["transactions"]): PeriodStat[] {
  const stats = transactions.reduce<Map<string, PeriodStat>>((map, transaction) => {
    const key = `${transaction.year}-${transaction.month}`;
    const current = map.get(key) ?? {
      balance: 0,
      entryTotal: 0,
      expenseTotal: 0,
      label: periodLabel(transaction.month, transaction.year),
      sortValue: transaction.year * 100 + transaction.month,
    };

    if (transaction.type === "entry") {
      current.entryTotal += transaction.amount;
    } else {
      current.expenseTotal += transaction.amount;
    }

    current.balance = current.entryTotal - current.expenseTotal;
    map.set(key, current);

    return map;
  }, new Map());

  return Array.from(stats.values()).sort((left, right) => left.sortValue - right.sortValue);
}

export function amountStats(transactions: DashboardData["transactions"], key: "category" | "paymentMethod", type: TransactionType): AmountStat[] {
  const stats = transactions
    .filter((transaction) => transaction.type === type)
    .reduce<Map<string, number>>((map, transaction) => {
      const label = transaction[key] || "Não informado";
      map.set(label, (map.get(label) ?? 0) + transaction.amount);

      return map;
    }, new Map());

  return Array.from(stats.entries())
    .map(([label, total]) => ({ label, total }))
    .sort((left, right) => right.total - left.total);
}

export function hasPositiveValue(values: number[]): boolean {
  return values.some((value) => value > 0);
}

export function hasAmountStats(stats: AmountStat[]): boolean {
  return stats.some((item) => item.total > 0);
}

function periodLabel(month: number, year: number): string {
  if (month < 1 || month > 12 || year < 1) {
    return "Sem competência";
  }

  return `${monthNames[month - 1]}/${year}`;
}
