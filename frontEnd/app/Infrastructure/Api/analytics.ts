import { apiRequest } from "./client";
import { getAuthToken } from "../Auth/session";

export type AnalyticsTotals = {
  entries: number;
  expenses: number;
  balance: number;
};

export type MonthlyBreakdownItem = {
  month: number;
  entries: number;
  expenses: number;
  balance: number;
};

export type GroupedItem = {
  label: string;
  id: number | null;
  count: number;
  total: number;
};

export type AnnualAnalyticsData = {
  availableYears: number[];
  year: number;
  totals: AnalyticsTotals;
  monthlyBreakdown: MonthlyBreakdownItem[];
  expensesByType: GroupedItem[];
  entriesByType: GroupedItem[];
  expensesByPaymentMethod: GroupedItem[];
};

export async function loadAnnualAnalytics(walletId: number, year: number): Promise<AnnualAnalyticsData> {
  const token = getAuthToken();

  if (!token) {
    throw new Error("Sessão expirada");
  }

  const params = new URLSearchParams({
    walletId: String(walletId),
    year: String(year),
  });

  const response = await apiRequest<AnnualAnalyticsData>(`/analytics/annual?${params.toString()}`, { token });

  if (!response.data) {
    throw new Error("Dados de analytics não disponíveis");
  }

  return response.data;
}
