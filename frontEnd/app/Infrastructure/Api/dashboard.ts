import { apiRequest } from "./client";
import type { EntryApi, ExpenseApi, NamedApiResource, TransactionApi, TransactionType } from "./movements";
import { getAuthToken, readAuthSession } from "../Auth/session";
import type { FieldOption } from "../DTO/EntityAttributes";

type ApiListResponse = Record<string, unknown>;

export type WalletApi = {
  id?: number;
  title?: string;
  description?: string | null;
};

export type DashboardTransaction = {
  id: string;
  resourceId: number | null;
  transactionId: number | null;
  type: TransactionType;
  description: string;
  amount: number;
  date: string;
  location: string;
  month: number;
  year: number;
  category: string;
  categoryId: number | null;
  paymentMethod: string;
  paymentMethodId: number | null;
  installments?: number | null;
};

export type DashboardData = {
  entryTypes: FieldOption[];
  expenseTypes: FieldOption[];
  paymentMethods: FieldOption[];
  transactions: DashboardTransaction[];
  wallet: WalletApi | null;
};

export type CreateCatalogPayload = {
  name: string;
};

export type DashboardMonthFilter = {
  month: number;
  year: number;
};

export async function loadDashboardData(monthFilter?: DashboardMonthFilter): Promise<DashboardData> {
  const token = requireAuthToken();
  const userId = readAuthSession()?.user.id;

  if (typeof userId !== "number") {
    throw new Error("Sessão inválida");
  }

  const [walletsResponse, entryTypesResponse, expenseTypesResponse, paymentMethodsResponse] = await Promise.all([
    apiRequest<ApiListResponse>(`/wallet/user/${userId}?perPage=50`, { token }),
    apiRequest<ApiListResponse>("/entry-type", { token }),
    apiRequest<ApiListResponse>("/expense-type", { token }),
    apiRequest<ApiListResponse>("/payment-method", { token }),
  ]);

  const wallets = responseList<WalletApi>(walletsResponse.data, "wallets");
  const wallet = wallets[0] ?? null;
  const entryTypes = responseList<NamedApiResource>(entryTypesResponse.data, "entryTypes");
  const expenseTypes = responseList<NamedApiResource>(expenseTypesResponse.data, "expenseTypes");
  const paymentMethods = responseList<NamedApiResource>(paymentMethodsResponse.data, "paymentMethods");
  const walletId = wallet?.id;
  const [entriesResponse, expensesResponse] = walletId
    ? await Promise.all([
        apiRequest<ApiListResponse>(movementListPath("/entry/wallet", walletId, monthFilter), { token }),
        apiRequest<ApiListResponse>(movementListPath("/expense/wallet", walletId, monthFilter), { token }),
      ])
    : [undefined, undefined];

  const entries = entriesResponse ? responseList<EntryApi>(entriesResponse.data, "entries") : [];
  const expenses = expensesResponse ? responseList<ExpenseApi>(expensesResponse.data, "expenses") : [];
  const entryOptions = optionsFromResources(entryTypes);
  const expenseOptions = optionsFromResources(expenseTypes);
  const paymentOptions = optionsFromResources(paymentMethods);
  const transactions = [
    ...entries.map((entry) => normalizeEntry(entry, entryOptions)),
    ...expenses.map((expense) => normalizeExpense(expense, expenseOptions, paymentOptions)),
  ].sort(compareTransactionsByDateDesc);
  const dashboardData = {
    entryTypes: entryOptions,
    expenseTypes: expenseOptions,
    paymentMethods: paymentOptions,
    transactions,
    wallet,
  };

  return dashboardData;
}

function movementListPath(basePath: string, walletId: number, monthFilter?: DashboardMonthFilter): string {
  const params = new URLSearchParams({
    perPage: "250",
  });

  if (monthFilter) {
    params.set("month", String(monthFilter.month));
    params.set("year", String(monthFilter.year));
  }

  return `${basePath}/${walletId}?${params.toString()}`;
}

export async function createEntryType(payload: CreateCatalogPayload): Promise<NamedApiResource | null> {
  const response = await apiRequest<Record<string, NamedApiResource>>("/entry-type", {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  return response.data?.entryType ?? null;
}

export async function createExpenseType(payload: CreateCatalogPayload): Promise<NamedApiResource | null> {
  const response = await apiRequest<Record<string, NamedApiResource>>("/expense-type", {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  return response.data?.expenseType ?? null;
}

export async function createPaymentMethod(payload: CreateCatalogPayload): Promise<NamedApiResource | null> {
  const response = await apiRequest<Record<string, NamedApiResource>>("/payment-method", {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  return response.data?.paymentMethod ?? null;
}

function requireAuthToken(): string {
  const token = getAuthToken();

  if (!token) {
    throw new Error("Sessão expirada");
  }

  return token;
}

function responseList<TItem>(data: ApiListResponse | undefined, key: string): TItem[] {
  const list = data?.[key];

  return Array.isArray(list) ? (list as TItem[]) : [];
}

function optionsFromResources(resources: NamedApiResource[]): FieldOption[] {
  return resources
    .filter((resource) => typeof resource.id === "number")
    .map((resource) => ({
      value: resource.id as number,
      label: resource.name ?? resource.title ?? `#${resource.id}`,
    }));
}

function amountNumber(value: number | string | null | undefined): number {
  if (typeof value === "number") {
    return value;
  }

  if (typeof value !== "string") {
    return 0;
  }

  const trimmed = value.trim();
  const hasComma = trimmed.includes(",");
  const hasDot = trimmed.includes(".");
  let normalized = trimmed;

  if (hasComma && hasDot) {
    const decimalSeparator = trimmed.lastIndexOf(",") > trimmed.lastIndexOf(".") ? "," : ".";
    const thousandsSeparator = decimalSeparator === "," ? "." : ",";

    normalized = trimmed
      .replaceAll(thousandsSeparator, "")
      .replace(decimalSeparator, ".");
  } else if (hasComma) {
    normalized = trimmed.replace(/\./g, "").replace(",", ".");
  } else if (hasDot && /^\d{1,3}(\.\d{3})+$/.test(trimmed)) {
    normalized = trimmed.replace(/\./g, "");
  }

  const parsed = Number(normalized);

  return Number.isFinite(parsed) ? parsed : 0;
}

function parseBackendDate(date: string | null | undefined): Date | null {
  if (!date) {
    return null;
  }

  const brDateMatch = /^(\d{2})\/(\d{2})\/(\d{4})/.exec(date);
  if (brDateMatch) {
    const [, day, month, year] = brDateMatch;
    return new Date(Number(year), Number(month) - 1, Number(day));
  }

  const parsed = new Date(date);

  return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function transactionMonth(transaction: TransactionApi | null | undefined): number {
  if (typeof transaction?.month === "number" && transaction.month >= 1 && transaction.month <= 12) {
    return transaction.month;
  }

  const parsedDate = parseBackendDate(transaction?.date);

  return parsedDate === null ? 0 : parsedDate.getMonth() + 1;
}

function transactionYear(transaction: TransactionApi | null | undefined): number {
  if (typeof transaction?.year === "number") {
    return transaction.year;
  }

  return parseBackendDate(transaction?.date)?.getFullYear() ?? 0;
}

function relationLabel(
  relation: NamedApiResource | number | null | undefined,
  relationId: number | null | undefined,
  options: FieldOption[],
  fallback: string,
): string {
  if (typeof relation === "object" && relation !== null) {
    return relation.name ?? relation.title ?? fallback;
  }

  const option = options.find((item) => item.value === relationId || item.value === relation);

  return option?.label ?? fallback;
}

function relationId(relation: NamedApiResource | number | null | undefined, relationIdValue: number | null | undefined): number | null {
  if (typeof relationIdValue === "number") {
    return relationIdValue;
  }

  if (typeof relation === "number") {
    return relation;
  }

  return typeof relation?.id === "number" ? relation.id : null;
}

function normalizeEntry(entry: EntryApi, entryTypes: FieldOption[]): DashboardTransaction {
  const transaction = entry.transaction;
  const categoryId = relationId(entry.entryType, entry.entryTypeId);
  const resourceId = typeof entry.id === "number" ? entry.id : null;
  const transactionId = typeof transaction?.id === "number" ? transaction.id : null;

  return {
    amount: amountNumber(transaction?.amount),
    category: relationLabel(entry.entryType, entry.entryTypeId, entryTypes, "Não informado"),
    categoryId,
    date: transaction?.date ?? "",
    description: transaction?.description ?? "Sem descrição",
    id: `entry-${resourceId ?? transactionId ?? "sem-id"}`,
    location: transaction?.location ?? "",
    month: transactionMonth(transaction),
    paymentMethod: "-",
    paymentMethodId: null,
    resourceId,
    transactionId,
    type: "entry",
    year: transactionYear(transaction),
  };
}

function normalizeExpense(
  expense: ExpenseApi,
  expenseTypes: FieldOption[],
  paymentMethods: FieldOption[],
): DashboardTransaction {
  const transaction = expense.transaction;
  const categoryId = relationId(expense.expenseType, expense.expenseTypeId);
  const paymentMethodId = relationId(expense.paymentMethod, expense.paymentMethodId);
  const resourceId = typeof expense.id === "number" ? expense.id : null;
  const transactionId = typeof transaction?.id === "number" ? transaction.id : null;

  return {
    amount: amountNumber(transaction?.amount),
    category: relationLabel(expense.expenseType, expense.expenseTypeId, expenseTypes, "Não informado"),
    categoryId,
    date: transaction?.date ?? "",
    description: transaction?.description ?? "Sem descrição",
    id: `expense-${resourceId ?? transactionId ?? "sem-id"}`,
    installments: expense.installments,
    location: transaction?.location ?? "",
    month: transactionMonth(transaction),
    paymentMethod: relationLabel(expense.paymentMethod, expense.paymentMethodId, paymentMethods, "Não informado"),
    paymentMethodId,
    resourceId,
    transactionId,
    type: "expense",
    year: transactionYear(transaction),
  };
}

function compareTransactionsByDateDesc(left: DashboardTransaction, right: DashboardTransaction): number {
  return (parseBackendDate(right.date)?.getTime() ?? 0) - (parseBackendDate(left.date)?.getTime() ?? 0);
}
