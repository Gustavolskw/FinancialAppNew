export type AuthUser = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
  status?: boolean | null;
};

export type AuthSession = {
  token: string;
  tokenType: string;
  expiresIn: number;
  expiresAt: string;
  user: AuthUser;
};

const authStorageKey = "appfinancas.auth";
const tokenStorageKey = "appfinancas.token";
const userStorageKey = "appfinancas.user";

function storage(): Storage | null {
  return typeof window === "undefined" ? null : window.localStorage;
}

export function saveAuthSession(session: AuthSession): void {
  const localStorage = storage();

  if (localStorage === null) {
    return;
  }

  localStorage.setItem(authStorageKey, JSON.stringify(session));
  localStorage.setItem(tokenStorageKey, session.token);
  localStorage.setItem(userStorageKey, JSON.stringify(session.user));
}

export function clearAuthSession(): void {
  const localStorage = storage();

  if (localStorage === null) {
    return;
  }

  localStorage.removeItem(authStorageKey);
  localStorage.removeItem(tokenStorageKey);
  localStorage.removeItem(userStorageKey);
}

export function readAuthSession(): AuthSession | null {
  const localStorage = storage();
  const rawSession = localStorage?.getItem(authStorageKey);

  if (!rawSession) {
    return null;
  }

  try {
    const session = JSON.parse(rawSession) as AuthSession;

    if (!session.token || !session.user || isExpired(session.expiresAt)) {
      clearAuthSession();
      return null;
    }

    return session;
  } catch {
    clearAuthSession();
    return null;
  }
}

export function getAuthToken(): string | null {
  return readAuthSession()?.token ?? storage()?.getItem(tokenStorageKey) ?? null;
}

export function isAuthenticated(): boolean {
  return readAuthSession() !== null;
}

function isExpired(expiresAt: string): boolean {
  const expirationTime = new Date(expiresAt).getTime();

  return Number.isFinite(expirationTime) && expirationTime <= Date.now();
}
