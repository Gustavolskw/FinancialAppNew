import { useEffect, useState } from "react";
import type { ReactElement } from "react";
import { Link, useLocation, useNavigate } from "react-router";

import { logoff } from "../../Infrastructure/Api/auth";
import { clearAuthSession, readAuthSession } from "../../Infrastructure/Auth/session";
import type { AuthSession } from "../../Infrastructure/Auth/session";

type NavItem = {
  icon: ReactElement;
  label: string;
  to: string;
};

const navItems: NavItem[] = [
  {
    icon: <DashboardIcon />,
    label: "Dashboard",
    to: "/principal",
  },
  {
    icon: <TransactionsIcon />,
    label: "Transações",
    to: "/transacoes",
  },
  {
    icon: <AuxiliaryIcon />,
    label: "Auxiliares",
    to: "/auxiliares",
  },
];

export function AppSidebar() {
  const [collapsed, setCollapsed] = useState(true);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [session, setSession] = useState<AuthSession | null>(null);
  const location = useLocation();
  const navigate = useNavigate();
  const userName = session?.user.name ?? session?.user.email ?? "Usuário";
  const userEmail = session?.user.email ?? "Sessão ativa";

  useEffect(() => {
    setSession(readAuthSession());
  }, []);

  async function handleLogout() {
    setIsLoggingOut(true);

    try {
      await logoff();
    } catch {
      // Logoff é stateless; o cliente deve descartar a sessão local mesmo se a confirmação falhar.
    } finally {
      clearAuthSession();
      setIsLoggingOut(false);
      navigate("/", { replace: true });
    }
  }

  return (
    <aside
      className={`sticky top-0 z-30 flex h-screen shrink-0 flex-col border-r border-slate-200 bg-white text-slate-900 shadow-sm transition-[width] duration-200 dark:border-slate-800 dark:bg-slate-950 dark:text-white ${
        collapsed ? "w-20" : "w-72"
      }`}
    >
      <div className="flex min-h-16 items-center gap-3 border-b border-slate-200 px-4 dark:border-slate-800">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-700 text-white shadow-sm shadow-blue-900/20">
          <WalletIcon />
        </div>
        {!collapsed && (
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-slate-950 dark:text-white">AppFinanças</p>
            <p className="truncate text-xs text-slate-500 dark:text-slate-400">Controle financeiro</p>
          </div>
        )}
      </div>

      <div className="border-b border-slate-200 px-3 py-4 dark:border-slate-800">
        <SidebarAction
          collapsed={collapsed}
          icon={collapsed ? <ExpandIcon /> : <CollapseIcon />}
          label={collapsed ? "Expandir menu" : "Colapsar menu"}
          onClick={() => setCollapsed((currentValue) => !currentValue)}
        />
      </div>

      <nav className="flex-1 space-y-2 px-3 py-4" aria-label="Navegação principal">
        {navItems.map((item) => (
          <SidebarLink
            active={location.pathname === item.to}
            collapsed={collapsed}
            icon={item.icon}
            key={item.to}
            label={item.label}
            to={item.to}
          />
        ))}
      </nav>

      <div className="border-t border-slate-200 p-3 dark:border-slate-800">
        <div className={`mb-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-900 ${collapsed ? "flex justify-center" : ""}`}>
          <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-200">
            {userName.slice(0, 1).toUpperCase()}
          </div>
          {!collapsed && (
            <div className="mt-3 min-w-0">
              <p className="truncate text-sm font-semibold text-slate-950 dark:text-white">{userName}</p>
              <p className="truncate text-xs text-slate-500 dark:text-slate-400">{userEmail}</p>
            </div>
          )}
        </div>

        <SidebarAction
          collapsed={collapsed}
          disabled={isLoggingOut}
          icon={<LogoutIcon />}
          label={isLoggingOut ? "Saindo..." : "Sair"}
          onClick={handleLogout}
          variant="danger"
        />
      </div>
    </aside>
  );
}

type SidebarLinkProps = {
  active: boolean;
  collapsed: boolean;
  icon: ReactElement;
  label: string;
  to: string;
};

function SidebarLink({ active, collapsed, icon, label, to }: SidebarLinkProps) {
  return (
    <Link
      aria-label={label}
      className={`group relative flex h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold transition ${
        active
          ? "bg-blue-700 text-white shadow-sm shadow-blue-900/20"
          : "text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white"
      } ${collapsed ? "justify-center" : ""}`}
      title={collapsed ? label : undefined}
      to={to}
    >
      <span className="flex h-5 w-5 shrink-0 items-center justify-center">{icon}</span>
      {!collapsed && <span>{label}</span>}
      {collapsed && <Tooltip>{label}</Tooltip>}
    </Link>
  );
}

type SidebarActionProps = {
  collapsed: boolean;
  disabled?: boolean;
  icon: ReactElement;
  label: string;
  onClick: () => void | Promise<void>;
  variant?: "default" | "danger";
};

function SidebarAction({ collapsed, disabled = false, icon, label, onClick, variant = "default" }: SidebarActionProps) {
  return (
    <button
      aria-label={label}
      className={`group relative flex h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 ${
        variant === "danger"
          ? "text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-300 dark:hover:bg-red-500/10 dark:hover:text-red-200"
          : "text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white"
      } ${collapsed ? "justify-center" : ""}`}
      disabled={disabled}
      onClick={onClick}
      title={collapsed ? label : undefined}
      type="button"
    >
      <span className="flex h-5 w-5 shrink-0 items-center justify-center">{icon}</span>
      {!collapsed && <span>{label}</span>}
      {collapsed && <Tooltip>{label}</Tooltip>}
    </button>
  );
}

function Tooltip({ children }: { children: string }) {
  return (
    <span className="pointer-events-none absolute left-[calc(100%+10px)] top-1/2 z-50 hidden -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-950 px-2.5 py-1.5 text-xs font-semibold text-white shadow-lg group-hover:block dark:bg-white dark:text-slate-950">
      {children}
    </span>
  );
}

function DashboardIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-3H4v3Z" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function TransactionsIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M5 7.5h14M5 12h14M5 16.5h9" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
      <path d="m16.5 15 2.5 2.5-2.5 2.5" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function AuxiliaryIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M5 7h6M5 12h10M5 17h4" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
      <path d="M16.5 6.5h2M17.5 5.5v2M18.5 16.5h-4a2 2 0 0 0 0 4h4a2 2 0 0 0 0-4Z" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function WalletIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M4.5 7.5A2.5 2.5 0 0 1 7 5h10.5A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19H7a2.5 2.5 0 0 1-2.5-2.5v-9Z" stroke="currentColor" strokeWidth="1.8" />
      <path d="M16 12h4M7.5 8.5h7" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
    </svg>
  );
}

function CollapseIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M15 6 9 12l6 6" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function ExpandIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="m9 6 6 6-6 6" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function LogoutIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M10 17.5H6.75A2.25 2.25 0 0 1 4.5 15.25v-6.5A2.25 2.25 0 0 1 6.75 6.5H10" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
      <path d="M14.5 8.5 18 12l-3.5 3.5M18 12H9" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}
