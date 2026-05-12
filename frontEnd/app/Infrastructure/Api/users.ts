import { apiRequest } from "./client";
import { getAuthToken } from "../Auth/session";
import type { AuthUser } from "../Auth/session";

type UserResponseData = {
  user?: Partial<AuthUser>;
};

export async function getUserById(id: number): Promise<Partial<AuthUser> | null> {
  const response = await apiRequest<UserResponseData>(`/user/${id}`, {
    token: requireAuthToken(),
  });

  return response.data?.user ?? null;
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
