import { apiRequest } from "./client";
import { getAuthToken } from "../Auth/session";
import type { AuthSession, AuthUser } from "../Auth/session";

type LoginResponseData = {
  auth?: Partial<AuthSession> & {
    user?: Partial<AuthUser>;
  };
};

type RegisterResponseData = {
  user?: Partial<AuthUser>;
};

export type LoginPayload = {
  email: string;
  password: string;
};

export type RegisterPayload = {
  name: string;
  email: string;
  password: string;
};

export async function login(payload: LoginPayload): Promise<AuthSession> {
  const response = await apiRequest<LoginResponseData>("/login", {
    body: payload,
    method: "POST",
  });

  return normalizeAuthSession(response.data?.auth);
}

export async function register(payload: RegisterPayload): Promise<Partial<AuthUser> | null> {
  const response = await apiRequest<RegisterResponseData>("/user", {
    body: payload,
    method: "POST",
  });

  return response.data?.user ?? null;
}

export async function logoff(): Promise<void> {
  await apiRequest("/logoff", {
    method: "POST",
    token: getAuthToken(),
  });
}

function normalizeAuthSession(auth: LoginResponseData["auth"]): AuthSession {
  if (
    !auth?.token
    || !auth.tokenType
    || typeof auth.expiresIn !== "number"
    || !auth.expiresAt
    || typeof auth.user?.id !== "number"
  ) {
    throw new Error("Resposta de autenticação inválida");
  }

  return {
    expiresAt: auth.expiresAt,
    expiresIn: auth.expiresIn,
    token: auth.token,
    tokenType: auth.tokenType,
    user: {
      email: auth.user.email ?? null,
      id: auth.user.id,
      name: auth.user.name ?? null,
      role: auth.user.role ?? null,
      status: auth.user.status ?? null,
    },
  };
}
