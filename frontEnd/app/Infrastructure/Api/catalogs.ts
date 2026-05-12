import { apiRequest } from "./client";
import { getAuthToken } from "../Auth/session";

export type AuxiliaryCatalogType = "entryType" | "expenseType" | "paymentMethod";

export type AuxiliaryCatalogItem = {
  id: number;
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

type ApiListResponse = Record<string, unknown>;

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
  const config = catalogConfigs[type];
  const response = await apiRequest<ApiListResponse>(config.path, {
    token: requireAuthToken(),
  });
  const list = response.data?.[config.listKey];

  if (!Array.isArray(list)) {
    return [];
  }

  return list
    .filter((item): item is Record<string, unknown> => typeof item === "object" && item !== null)
    .filter((item) => typeof item.id === "number")
    .map((item) => ({
      id: item.id as number,
      name: typeof item.name === "string" ? item.name : `#${item.id}`,
    }));
}

export async function createCatalogItem(type: AuxiliaryCatalogType, payload: CatalogPayload): Promise<AuxiliaryCatalogItem | null> {
  const config = catalogConfigs[type];
  const response = await apiRequest<Record<string, AuxiliaryCatalogItem>>(config.path, {
    body: payload,
    method: "POST",
    token: requireAuthToken(),
  });

  return response.data?.[config.singularKey] ?? null;
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

  return response.data?.[config.singularKey] ?? null;
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
