import { apiRequest, type ApiResponse } from "./client";
import { getAuthToken } from "../Auth/session";

export type AuxiliaryCatalogType = "entryType" | "expenseType" | "paymentMethod";

export type AuxiliaryCatalogItem = {
  id: number;
  isDefault: boolean;
  name: string;
};

export type CatalogPayload = {
  name: string;
};

type CatalogConfig = {
  listKey: string;
  path: string;
  singularKey: string;
};

type ApiListResponse = Record<string, unknown> & {
  pagination?: {
    nextPage?: number | null;
  };
};

const catalogPageSize = 100;

const catalogConfigs: Record<AuxiliaryCatalogType, CatalogConfig> = {
  entryType: {
    listKey: "entryTypes",
    path: "/entry-type",
    singularKey: "entryType",
  },
  expenseType: {
    listKey: "expenseTypes",
    path: "/expense-type",
    singularKey: "expenseType",
  },
  paymentMethod: {
    listKey: "paymentMethods",
    path: "/payment-method",
    singularKey: "paymentMethod",
  },
};

export async function listCatalogItems(type: AuxiliaryCatalogType): Promise<AuxiliaryCatalogItem[]> {
  const items: AuxiliaryCatalogItem[] = [];
  let page = 1;
  let nextPage: number | null | undefined = page;

  while (nextPage !== null && nextPage !== undefined) {
    const response = await requestCatalogPage(type, page);
    items.push(...catalogItemsFromResponse(type, response));
    nextPage = response.data?.pagination?.nextPage;
    page = nextPage ?? 0;
  }

  return items;
}

async function requestCatalogPage(type: AuxiliaryCatalogType, page: number): Promise<ApiResponse<ApiListResponse>> {
  const config = catalogConfigs[type];
  const searchParams = new URLSearchParams({
    page: String(page),
    perPage: String(catalogPageSize),
  });

  return apiRequest<ApiListResponse>(`${config.path}?${searchParams.toString()}`, {
    token: requireAuthToken(),
  });
}

function catalogItemsFromResponse(type: AuxiliaryCatalogType, response: ApiResponse<ApiListResponse>): AuxiliaryCatalogItem[] {
  const config = catalogConfigs[type];
  const list = response.data?.[config.listKey];

  if (!Array.isArray(list)) {
    return [];
  }

  return list
    .filter((item): item is Record<string, unknown> => typeof item === "object" && item !== null)
    .filter((item) => typeof item.id === "number")
    .map(normalizeCatalogItem);
}

function normalizeCatalogItem(item: Record<string, unknown>): AuxiliaryCatalogItem {
  return {
    id: item.id as number,
    isDefault: item.isDefault === true,
    name: typeof item.name === "string" ? item.name : `#${item.id}`,
  };
}

export async function createCatalogItem(type: AuxiliaryCatalogType, payload: CatalogPayload): Promise<AuxiliaryCatalogItem | null> {
  const config = catalogConfigs[type];
  const response = await apiRequest<Record<string, AuxiliaryCatalogItem>>(config.path, {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  const item = response.data?.[config.singularKey];

  return isCatalogRecord(item) ? normalizeCatalogItem(item) : null;
}

export async function updateCatalogItem(
  type: AuxiliaryCatalogType,
  id: number,
  payload: CatalogPayload,
): Promise<AuxiliaryCatalogItem | null> {
  const config = catalogConfigs[type];
  const response = await apiRequest<Record<string, AuxiliaryCatalogItem>>(config.path, {
    body: { id, ...payload },
    method: "PATCH",
    token: requireAuthToken(),
  });

  const item = response.data?.[config.singularKey];

  return isCatalogRecord(item) ? normalizeCatalogItem(item) : null;
}

export async function deleteCatalogItem(type: AuxiliaryCatalogType, id: number): Promise<void> {
  const config = catalogConfigs[type];

  await apiRequest(`${config.path}/${id}`, {
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

function isCatalogRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object"
    && value !== null
    && typeof (value as Record<string, unknown>).id === "number";
}
