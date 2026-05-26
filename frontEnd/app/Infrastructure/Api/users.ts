import { apiRequest } from "./client";
import { getAuthToken } from "../Auth/session";
import type { AuthUser } from "../Auth/session";

type UserResponseData = {
  user?: Partial<AuthUser>;
};

export type UserUpdateData = {
  id: number;
  name?: string;
  email?: string;
  password?: string;
};

export async function getUserById(id: number): Promise<Partial<AuthUser> | null> {
  const response = await apiRequest<UserResponseData>(`/user/${id}`, {
    token: requireAuthToken(),
  });

  return response.data?.user ?? null;
}

export async function updateUser(data: UserUpdateData): Promise<Partial<AuthUser>> {
  const response = await apiRequest<UserResponseData>("/user", {
    method: "PATCH",
    token: requireAuthToken(),
    body: data,
  });

  if (!response.data?.user) {
    throw new Error("Falha ao atualizar usuário");
  }

  return response.data.user;
}

export function isAdminRole(role: unknown): boolean {
  return typeof role === "string" && role.toLowerCase() === "admin";
}

function requireAuthToken(): string {
  const token = getAuthToken();

  if (!token) {
    throw new Error("Sessão expirada");
  }

  return token;
}
