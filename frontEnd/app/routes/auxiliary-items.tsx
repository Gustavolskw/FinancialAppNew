import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from "chart.js";
import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router";

import type { Route } from "./+types/auxiliary-items";
import { AuxiliaryCatalogCharts, buildCatalogUsageStats } from "../components/auxiliary/AuxiliaryCatalogCharts";
import { AuxiliaryCatalogTabs } from "../components/auxiliary/AuxiliaryCatalogTabs";
import { AuxiliaryItemModal, catalogFieldText } from "../components/auxiliary/AuxiliaryItemModal";
import { AuxiliaryItemsGrid } from "../components/auxiliary/AuxiliaryItemsGrid";
import { ProtectedRouteFallback } from "../components/auth/ProtectedRouteFallback";
import { DashboardStatusBanner } from "../components/dashboard/DashboardStatusBanner";
import { FormStatusMessage } from "../components/feedback/FormStatusMessage";
import { AuthenticatedAppShell } from "../components/navigation/AuthenticatedAppShell";
import { ApiRequestError } from "../Infrastructure/Api/client";
import {
  deleteCatalogItem,
  listCatalogItems,
  type AuxiliaryCatalogItem,
  type AuxiliaryCatalogType,
} from "../Infrastructure/Api/catalogs";
import { loadDashboardData, type DashboardData } from "../Infrastructure/Api/dashboard";
import { getUserById, isAdminRole } from "../Infrastructure/Api/users";
import { clearAuthSession, readAuthSession } from "../Infrastructure/Auth/session";
import { useRequireAuth } from "../Infrastructure/Auth/useRequireAuth";

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

type LoadStatus = "loading" | "ready" | "error";
type CatalogItemsByType = Record<AuxiliaryCatalogType, AuxiliaryCatalogItem[]>;
type ModalState = {
  item: AuxiliaryCatalogItem | null;
  type: AuxiliaryCatalogType;
} | null;

const emptyDashboard: DashboardData = {
  entryTypes: [],
  expenseTypes: [],
  paymentMethods: [],
  transactions: [],
  wallet: null,
};

const emptyCatalogs: CatalogItemsByType = {
  entryType: [],
  expenseType: [],
  paymentMethod: [],
};

const catalogLabels: Record<AuxiliaryCatalogType, string> = {
  entryType: "tipos de entrada",
  expenseType: "tipos de despesa",
  paymentMethod: "métodos de pagamento",
};

function apiErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof ApiRequestError) {
    return error.message;
  }

  if (error instanceof Error) {
    return error.message;
  }

  return fallback;
}

function canManageCatalogItem(item: AuxiliaryCatalogItem, isAdmin: boolean): boolean {
  return isAdmin || !item.isDefault;
}

export function meta({ }: Route.MetaArgs) {
  return [
    { title: "Itens auxiliares | AppFinanças" },
    { name: "description", content: "Gestão de tipos de entrada, tipos de despesa e métodos de pagamento." },
  ];
}

export default function AuxiliaryItems() {
  const navigate = useNavigate();
  const authStatus = useRequireAuth();
  const [activeType, setActiveType] = useState<AuxiliaryCatalogType>("entryType");
  const [catalogs, setCatalogs] = useState<CatalogItemsByType>(emptyCatalogs);
  const [dashboard, setDashboard] = useState<DashboardData | null>(null);
  const [loadStatus, setLoadStatus] = useState<LoadStatus>("loading");
  const [loadMessage, setLoadMessage] = useState("Carregando itens auxiliares...");
  const [modalState, setModalState] = useState<ModalState>(null);
  const [actionMessage, setActionMessage] = useState<string | null>(null);
  const [actionMessageType, setActionMessageType] = useState<"success" | "error">("success");
  const [isMutating, setIsMutating] = useState(false);
  const [isAdmin, setIsAdmin] = useState(false);

  const dashboardData = dashboard ?? emptyDashboard;
  const activeItems = catalogs[activeType];
  const usageStats = useMemo(
    () => buildCatalogUsageStats(activeType, activeItems, dashboardData.transactions),
    [activeItems, activeType, dashboardData.transactions],
  );
  const usedItemsCount = usageStats.filter((stat) => stat.transactionCount > 0).length;
  const unusedItemsCount = Math.max(activeItems.length - usedItemsCount, 0);
  const totalUsageCount = usageStats.reduce((total, stat) => total + stat.transactionCount, 0);
  const totalMovementAmount = usageStats.reduce((total, stat) => total + stat.totalAmount, 0);
  const counts = {
    entryType: catalogs.entryType.length,
    expenseType: catalogs.expenseType.length,
    paymentMethod: catalogs.paymentMethod.length,
  };
  const emptyGridLabel = loadStatus === "loading"
    ? "Carregando itens da API..."
    : `Nenhum item encontrado para ${catalogLabels[activeType]}.`;

  async function refreshData() {
    setLoadStatus("loading");
    setLoadMessage("Carregando itens auxiliares...");

    try {
      const userId = readAuthSession()?.user.id;

      if (typeof userId !== "number") {
        throw new Error("Sessão inválida");
      }

      const [dashboardDataResponse, entryTypes, expenseTypes, paymentMethods, currentUser] = await Promise.all([
        loadDashboardData(),
        listCatalogItems("entryType"),
        listCatalogItems("expenseType"),
        listCatalogItems("paymentMethod"),
        getUserById(userId),
      ]);

      setIsAdmin(isAdminRole(currentUser?.role));
      setDashboard(dashboardDataResponse);
      setCatalogs({
        entryType: entryTypes,
        expenseType: expenseTypes,
        paymentMethod: paymentMethods,
      });
      setLoadStatus("ready");
      setLoadMessage("Itens auxiliares carregados.");
    } catch (error) {
      if (error instanceof ApiRequestError && error.statusCode === 401) {
        clearAuthSession();
        navigate("/", { replace: true });
        return;
      }

      setDashboard(null);
      setCatalogs(emptyCatalogs);
      setIsAdmin(false);
      setLoadStatus("error");
      setLoadMessage(apiErrorMessage(error, "Não foi possível carregar os itens auxiliares."));
    }
  }

  useEffect(() => {
    if (authStatus !== "authenticated") {
      return;
    }

    void refreshData();
  }, [authStatus]);

  if (authStatus !== "authenticated") {
    return <ProtectedRouteFallback />;
  }

  function changeActiveType(type: AuxiliaryCatalogType) {
    setActiveType(type);
    setActionMessage(null);
  }

  function openCreateModal() {
    setModalState({ item: null, type: activeType });
  }

  function openEditModal(item: AuxiliaryCatalogItem) {
    if (!canManageCatalogItem(item, isAdmin)) {
      setActionMessageType("error");
      setActionMessage("Somente administradores podem editar itens auxiliares padrão.");
      return;
    }

    setModalState({ item, type: activeType });
  }

  async function deleteItem(item: AuxiliaryCatalogItem) {
    if (!canManageCatalogItem(item, isAdmin)) {
      setActionMessageType("error");
      setActionMessage("Somente administradores podem excluir itens auxiliares padrão.");
      return;
    }

    const confirmed = window.confirm(`Excluir "${item.name}"?`);

    if (!confirmed) {
      return;
    }

    setIsMutating(true);
    setActionMessage(null);

    try {
      await deleteCatalogItem(activeType, item.id);
      setActionMessageType("success");
      setActionMessage("Item excluído com sucesso.");
      await refreshData();
    } catch (error) {
      setActionMessageType("error");
      setActionMessage(apiErrorMessage(error, "Não foi possível excluir o item. Verifique se ele está vinculado a transações."));
    } finally {
      setIsMutating(false);
    }
  }

  return (
    <AuthenticatedAppShell>
      <header className="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800">
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">
            Cadastros auxiliares
          </p>
          <h1 className="mt-2 text-3xl font-semibold tracking-normal text-slate-950 dark:text-white">
            Gestão de itens auxiliares
          </h1>
          <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Gerencie tipos de entrada, métodos de pagamento e tipos de despesa usados nas transações da carteira.
          </p>
        </div>

        <button className="btn-entrar btn-entrar--sm w-full sm:w-auto" disabled={loadStatus !== "ready"} onClick={openCreateModal} type="button">
          <span>{catalogFieldText[activeType].buttonLabel}</span>
        </button>
      </header>

      <DashboardStatusBanner
        message={loadMessage}
        onRefresh={() => void refreshData()}
        status={loadStatus}
      />

      <FormStatusMessage message={actionMessage} type={actionMessageType} />

      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <AuxiliaryKpi label="Itens cadastrados" value={activeItems.length} />
        <AuxiliaryKpi label="Itens usados" value={usedItemsCount} />
        <AuxiliaryKpi label="Itens sem uso" value={unusedItemsCount} />
        <AuxiliaryKpi label="Transações vinculadas" value={totalUsageCount} subValue={new Intl.NumberFormat("pt-BR", { currency: "BRL", style: "currency" }).format(totalMovementAmount)} />
      </section>

      <AuxiliaryCatalogCharts
        activeType={activeType}
        isLoading={loadStatus === "loading"}
        stats={usageStats}
      />

      <AuxiliaryCatalogTabs activeType={activeType} counts={counts} onChange={changeActiveType} />

      <AuxiliaryItemsGrid
        activeType={activeType}
        canDeleteItem={(item) => canManageCatalogItem(item, isAdmin)}
        canEditItem={(item) => canManageCatalogItem(item, isAdmin)}
        emptyLabel={emptyGridLabel}
        isMutating={isMutating}
        items={activeItems}
        onDelete={(item) => void deleteItem(item)}
        onEdit={openEditModal}
        stats={usageStats}
      />

      <AuxiliaryItemModal
        item={modalState?.item}
        onClose={() => setModalState(null)}
        onSaved={async () => {
          setActionMessageType("success");
          setActionMessage(modalState?.item ? "Item atualizado com sucesso." : "Item adicionado com sucesso.");
          await refreshData();
        }}
        type={modalState?.type ?? null}
      />
    </AuthenticatedAppShell>
  );
}

type AuxiliaryKpiProps = {
  label: string;
  subValue?: string;
  value: number;
};

function AuxiliaryKpi({ label, subValue, value }: AuxiliaryKpiProps) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{label}</p>
      <p className="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{value}</p>
      {subValue && <p className="mt-1 text-sm font-semibold text-blue-700 dark:text-blue-300">{subValue}</p>}
    </div>
  );
}
