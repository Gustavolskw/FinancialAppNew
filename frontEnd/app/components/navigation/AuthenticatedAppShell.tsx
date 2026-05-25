import { createContext, useContext, useState } from "react";
import type { ReactNode } from "react";

import { AppSidebar } from "./AppSidebar";

type SidebarContextType = {
  collapsed: boolean;
  setCollapsed: (collapsed: boolean) => void;
};

const SidebarContext = createContext<SidebarContextType | null>(null);

export function useSidebarContext() {
  const context = useContext(SidebarContext);
  if (!context) {
    throw new Error("useSidebarContext must be used within AuthenticatedAppShell");
  }
  return context;
}

type AuthenticatedAppShellProps = {
  children: ReactNode;
};

export function AuthenticatedAppShell({ children }: AuthenticatedAppShellProps) {
  const [collapsed, setCollapsed] = useState(true);

  return (
    <SidebarContext.Provider value={{ collapsed, setCollapsed }}>
      <div className="min-h-screen overflow-x-hidden bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-white">
        <AppSidebar />
        <main className={`min-w-0 pb-24 transition-[margin] duration-300 lg:pb-0 ${collapsed ? "lg:ml-20" : "lg:ml-72"}`}>
          <div className="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-5 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </SidebarContext.Provider>
  );
}
