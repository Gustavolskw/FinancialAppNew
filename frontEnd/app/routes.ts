import { type RouteConfig, index, route } from "@react-router/dev/routes";

export default [
  index("routes/login.tsx"),
  route("cadastro", "routes/register.tsx"),
  route("principal", "routes/dashboard.tsx"),
  route("transacoes", "routes/transactions.tsx"),
  route("auxiliares", "routes/auxiliary-items.tsx"),
  route("perfil", "routes/profile.tsx"),
  route("analise-anual", "routes/annual-analytics.tsx"),
] satisfies RouteConfig;
