import { apiRequest } from "./client";
import { getAuthToken } from "../Auth/session";

export type NamedApiResource = {
  id?: number;
  name?: string;
  title?: string;
};

export type TransactionApi = {
  id?: number;
  amount?: number | string | null;
  date?: string | null;
  description?: string | null;
  location?: string | null;
  month?: number | null;
  year?: number | null;
};

export type EntryApi = {
  id?: number;
  entryType?: NamedApiResource | number | null;
  entryTypeId?: number | null;
  transaction?: TransactionApi | null;
};

export type ExpenseApi = {
  id?: number;
  expenseType?: NamedApiResource | number | null;
  expenseTypeId?: number | null;
  installments?: number | null;
  paymentMethod?: NamedApiResource | number | null;
  paymentMethodId?: number | null;
  transaction?: TransactionApi | null;
};

export type TransactionType = "entry" | "expense";

export type CreateEntryPayload = {
  amount: string;
  date: string;
  description: string;
  entryTypeId: number;
  location: string;
  month: number;
  walletId: number;
  year: number;
};

export type CreateExpensePayload = {
  amount: string;
  date: string;
  description: string;
  expenseTypeId: number;
  installments: number;
  location: string;
  month: number;
  paymentMethodId: number;
  walletId: number;
  year: number;
};

export type UpdateEntryPayload = CreateEntryPayload & {
  id: number;
};

export type UpdateExpensePayload = CreateExpensePayload & {
  id: number;
};

export async function createEntry(payload: CreateEntryPayload): Promise<EntryApi | null> {
  const response = await apiRequest<Record<string, EntryApi>>("/entry", {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  return response.data?.entry ?? null;
}

export async function updateEntry(payload: UpdateEntryPayload): Promise<EntryApi | null> {
  const response = await apiRequest<Record<string, EntryApi>>("/entry", {
    body: payload,
    method: "PATCH",
    token: requireAuthToken(),
  });

  return response.data?.entry ?? null;
}

export async function createExpense(payload: CreateExpensePayload): Promise<ExpenseApi | null> {
  const response = await apiRequest<Record<string, ExpenseApi>>("/expense", {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  return response.data?.expense ?? null;
}

export async function updateExpense(payload: UpdateExpensePayload): Promise<ExpenseApi | null> {
  const response = await apiRequest<Record<string, ExpenseApi>>("/expense", {
    body: payload,
    method: "PATCH",
    token: requireAuthToken(),
  });

  return response.data?.expense ?? null;
}

export async function deleteEntry(id: number): Promise<void> {
  await apiRequest(`/entry/${id}`, {
    method: "DELETE",
    token: requireAuthToken(),
  });
}

export async function deleteExpense(id: number): Promise<void> {
  await apiRequest(`/expense/${id}`, {
    method: "DELETE",
    token: requireAuthToken(),
  });
}

function requireAuthToken(): string {
  const token = getAuthToken();

  if (!token) {
    throw new Error("Sessão expirada");
  }

  return token;
}
