export type ApiResponse<TData> = {
  message: string;
  statusCode: number;
  data?: TData;
};

type ApiRequestOptions = {
  body?: unknown;
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  token?: string | null;
};

export class ApiRequestError extends Error {
  constructor(
    message: string,
    public readonly statusCode: number,
    public readonly response?: ApiResponse<unknown>,
  ) {
    super(message);
  }
}

const defaultBaseUrl = "http://localhost:9500";

export function apiBaseUrl(): string {
  return (import.meta.env.VITE_API_BASE_URL ?? defaultBaseUrl).replace(/\/$/, "");
}

export async function apiRequest<TData>(
  path: string,
  options: ApiRequestOptions = {},
): Promise<ApiResponse<TData>> {
  const headers = new Headers({
    Accept: "application/json",
  });

  if (options.body !== undefined) {
    headers.set("Content-Type", "application/json");
  }

  if (options.token) {
    headers.set("Authorization", `Bearer ${options.token}`);
  }

  const response = await fetch(`${apiBaseUrl()}${path}`, {
    body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
    headers,
    method: options.method ?? "GET",
  });

  const rawBody = await response.text();
  const parsedBody = parseResponseBody<TData>(rawBody);

  if (!response.ok) {
    throw new ApiRequestError(
      parsedBody?.message ?? "Não foi possível concluir a requisição",
      parsedBody?.statusCode ?? response.status,
      parsedBody as ApiResponse<unknown> | undefined,
    );
  }

  if (parsedBody === undefined) {
    return {
      message: "Sucesso!",
      statusCode: response.status,
    } as ApiResponse<TData>;
  }

  return parsedBody;
}

function parseResponseBody<TData>(rawBody: string): ApiResponse<TData> | undefined {
  if (!rawBody) {
    return undefined;
  }

  try {
    return JSON.parse(rawBody) as ApiResponse<TData>;
  } catch {
    return {
      message: "Resposta inválida da API",
      statusCode: 500,
    };
  }
}
