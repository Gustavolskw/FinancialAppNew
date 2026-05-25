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

import type { Route } from "./+types/transactions";
import { ProtectedRouteFallback } from "../components/auth/ProtectedRouteFallback";
import { DashboardKpiGrid } from "../components/dashboard/DashboardKpiGrid";
import { DashboardStatusBanner } from "../components/dashboard/DashboardStatusBanner";
import { currency, sumByType } from "../components/dashboard/dashboardMetrics";
import { FormStatusMessage } from "../components/feedback/FormStatusMessage";
import { currentMonthFilter, MonthFilter, monthFilterLabel, type MonthFilterValue } from "../components/filters/MonthFilter";
import { AuthenticatedAppShell } from "../components/navigation/AuthenticatedAppShell";
import { MovementModal } from "../components/transactions/MovementModal";
import { TransactionsAnalyticsCharts } from "../components/transactions/TransactionsAnalyticsCharts";
import { TransactionsGridFilters } from "../components/transactions/TransactionsGridFilters";
import { TransactionsManagementGrid } from "../components/transactions/TransactionsManagementGrid";
import { TransactionTypeTabs } from "../components/transactions/TransactionTypeTabs";
import { applyTransactionFilters, emptyTransactionFilters, type TransactionFilterValues } from "../components/transactions/transactionFiltering";
import { ApiRequestError } from "../Infrastructure/Api/client";
import { loadDashboardData, type DashboardData, type DashboardTransaction } from "../Infrastructure/Api/dashboard";
import { deleteEntry, deleteExpense, type TransactionType } from "../Infrastructure/Api/movements";
import { clearAuthSession } from "../Infrastructure/Auth/session";
import { useRequireAuth } from "../Infrastructure/Auth/useRequireAuth";

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

type LoadStatus = "loading" | "ready" | "error";
type ModalState = {
  mode: "create" | "edit";
  transaction: DashboardTransaction | null;
  type: TransactionType;
} | null;

const emptyDashboard: DashboardData = {
  entryTypes: [],
  expenseTypes: [],
  paymentMethods: [],
  transactions: [],
  wallet: null,
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

export function meta({ }: Route.MetaArgs) {
  return [
    { title: "Gestão de Transações | AppFinanças" },
    { name: "description", content: "Gestão de entradas e saídas da carteira com gráficos e ações em massa." },
  ];
}

export default function Transactions() {
  const navigate = useNavigate();
  const authStatus = useRequireAuth();
  const [dashboard, setDashboard] = useState<DashboardData | null>(null);
  const [loadStatus, setLoadStatus] = useState<LoadStatus>("loading");
  const [loadMessage, setLoadMessage] = useState("Carregando transações da carteira...");
  const [activeType, setActiveType] = useState<TransactionType>("entry");
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [modalState, setModalState] = useState<ModalState>(null);
  const [actionMessage, setActionMessage] = useState<string | null>(null);
  const [actionMessageType, setActionMessageType] = useState<"success" | "error">("success");
  const [isDeleting, setIsDeleting] = useState(false);
  const [monthFilter, setMonthFilter] = useState<MonthFilterValue>(() => currentMonthFilter());
  const [transactionFilters, setTransactionFilters] = useState<TransactionFilterValues>(emptyTransactionFilters);
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);

  const dashboardData = dashboard ?? emptyDashboard;
  const isLoading = loadStatus === "loading";
  const selectedPeriodLabel = monthFilterLabel(monthFilter);
  const entryTransactions = dashboardData.transactions.filter((transaction) => transaction.type === "entry");
  const expenseTransactions = dashboardData.transactions.filter((transaction) => transaction.type === "expense");
  const visibleTransactions = activeType === "entry" ? entryTransactions : expenseTransactions;
  const filteredTransactions = useMemo(
    () => applyTransactionFilters(visibleTransactions, transactionFilters),
    [transactionFilters, visibleTransactions],
  );
  const totalPages = Math.max(1, Math.ceil(filteredTransactions.length / pageSize));
  const safeCurrentPage = Math.min(currentPage, totalPages);
  const pageStartIndex = (safeCurrentPage - 1) * pageSize;
  const paginatedTransactions = filteredTransactions.slice(pageStartIndex, pageStartIndex + pageSize);
  const entriesTotal = sumByType(dashboardData.transactions, "entry");
  const expensesTotal = sumByType(dashboardData.transactions, "expense");
  const balance = entriesTotal - expensesTotal;
  const canCreateEntry = loadStatus === "ready" && dashboardData.wallet !== null && dashboardData.entryTypes.length > 0;
  const canCreateExpense = loadStatus === "ready"
    && dashboardData.wallet !== null
    && dashboardData.expenseTypes.length > 0
    && dashboardData.paymentMethods.length > 0;
  const emptyGridLabel = isLoading
    ? "Carregando movimentações da API..."
    : visibleTransactions.length > 0 && filteredTransactions.length === 0
      ? "Nenhuma movimentação corresponde aos filtros selecionados."
      : activeType === "entry"
        ? "Nenhuma entrada encontrada para esta carteira."
        : "Nenhuma saída encontrada para esta carteira.";

  const selectedTransactions = useMemo(
    () => visibleTransactions.filter((transaction) => transaction.resourceId !== null && selectedIds.has(transaction.resourceId)),
    [selectedIds, visibleTransactions],
  );
  const categoryOptions = activeType === "entry" ? dashboardData.entryTypes : dashboardData.expenseTypes;
  const paginationStartItem = filteredTransactions.length === 0 ? 0 : pageStartIndex + 1;
  const paginationEndItem = Math.min(pageStartIndex + pageSize, filteredTransactions.length);

  async function refreshTransactions() {
    setLoadStatus("loading");
    setLoadMessage(`Carregando transações de ${selectedPeriodLabel}...`);

    try {
      const data = await loadDashboardData(monthFilter);
      setDashboard(data);
      setLoadStatus("ready");
      setLoadMessage(data.wallet ? `Transações de ${selectedPeriodLabel} carregadas.` : "Nenhuma carteira encontrada.");
    } catch (error) {
      if (error instanceof ApiRequestError && error.statusCode === 401) {
        clearAuthSession();
        navigate("/", { replace: true });
        return;
      }

      setDashboard(null);
      setLoadStatus("error");
      setLoadMessage(apiErrorMessage(error, "Não foi possível carregar as transações."));
    }
  }

  useEffect(() => {
    if (authStatus !== "authenticated") {
      return;
    }

    void refreshTransactions();
  }, [authStatus, monthFilter.month, monthFilter.year]);

  useEffect(() => {
    if (currentPage > totalPages) {
      setCurrentPage(totalPages);
    }
  }, [currentPage, totalPages]);

  if (authStatus !== "authenticated") {
    return <ProtectedRouteFallback />;
  }

  function selectTransaction(id: number, selected: boolean) {
    setSelectedIds((currentIds) => {
      const nextIds = new Set(currentIds);

      if (selected) {
        nextIds.add(id);
      } else {
        nextIds.delete(id);
      }

      return nextIds;
    });
  }

  function selectAllVisible(selected: boolean) {
    const pageIds = paginatedTransactions
      .map((transaction) => transaction.resourceId)
      .filter((id): id is number => typeof id === "number");

    setSelectedIds((currentIds) => {
      const nextIds = new Set(currentIds);

      pageIds.forEach((id) => {
        if (selected) {
          nextIds.add(id);
        } else {
          nextIds.delete(id);
        }
      });

      return nextIds;
    });
  }

  function changeActiveType(type: TransactionType) {
    setActiveType(type);
    setSelectedIds(new Set());
    setTransactionFilters(emptyTransactionFilters);
    setCurrentPage(1);
    setActionMessage(null);
  }

  function changeMonthFilter(value: MonthFilterValue) {
    setMonthFilter(value);
    setSelectedIds(new Set());
    setCurrentPage(1);
    setActionMessage(null);
  }

  function changeTransactionFilters(filters: TransactionFilterValues) {
    setTransactionFilters(filters);
    setSelectedIds(new Set());
    setCurrentPage(1);
    setActionMessage(null);
  }

  function resetTransactionFilters() {
    changeTransactionFilters(emptyTransactionFilters);
  }

  function changePageSize(nextPageSize: number) {
    setPageSize(nextPageSize);
    setCurrentPage(1);
  }

  function openCreateModal(type: TransactionType) {
    setModalState({ mode: "create", transaction: null, type });
  }

  function openEditModal(transaction: DashboardTransaction) {
    setModalState({ mode: "edit", transaction, type: transaction.type });
  }

  async function deleteTransaction(transaction: DashboardTransaction) {
    if (!transaction.resourceId) {
      setActionMessageType("error");
      setActionMessage("Esta transação não possui identificador para exclusão.");
      return;
    }

    const confirmed = window.confirm(`Excluir "${transaction.description}"?`);

    if (!confirmed) {
      return;
    }

    setIsDeleting(true);
    setActionMessage(null);

    try {
      if (transaction.type === "entry") {
        await deleteEntry(transaction.resourceId);
      } else {
        await deleteExpense(transaction.resourceId);
      }

      setSelectedIds((currentIds) => {
        const nextIds = new Set(currentIds);
        nextIds.delete(transaction.resourceId as number);
        return nextIds;
      });
      setActionMessageType("success");
      setActionMessage("Transação excluída com sucesso.");
      await refreshTransactions();
    } catch (error) {
      setActionMessageType("error");
      setActionMessage(apiErrorMessage(error, "Não foi possível excluir a transação."));
    } finally {
      setIsDeleting(false);
    }
  }

  async function bulkDeleteSelected() {
    if (selectedTransactions.length === 0) {
      return;
    }

    const confirmed = window.confirm(`Excluir ${selectedTransactions.length} item(ns) selecionado(s)?`);

    if (!confirmed) {
      return;
    }

    setIsDeleting(true);
    setActionMessage(null);

    try {
      await Promise.all(selectedTransactions.map((transaction) => {
        if (!transaction.resourceId) {
          return Promise.resolve();
        }

        return transaction.type === "entry"
          ? deleteEntry(transaction.resourceId)
          : deleteExpense(transaction.resourceId);
      }));

      setSelectedIds(new Set());
      setActionMessageType("success");
      setActionMessage("Itens selecionados excluídos com sucesso.");
      await refreshTransactions();
    } catch (error) {
      setActionMessageType("error");
      setActionMessage(apiErrorMessage(error, "Não foi possível excluir todos os itens selecionados."));
    } finally {
      setIsDeleting(false);
    }
  }

  function showStatusUnavailable() {
    setActionMessageType("error");
    setActionMessage("Entry e Expense ainda não possuem rota de status no backend. Assim que a API expuser esse contrato, esta ação pode ser conectada aqui.");
  }

  return (
    <AuthenticatedAppShell>
      <header className="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800">
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">
            {dashboardData.wallet?.title ?? (isLoading ? "Carregando carteira" : "Carteira")}
          </p>
          <h1 className="mt-2 text-3xl font-semibold tracking-normal text-slate-950 dark:text-white">
            Gestão de transações
          </h1>
          <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Controle entradas e saídas da carteira, edite registros individuais e exclua múltiplas transações selecionadas.
          </p>
        </div>

        <div className="flex flex-col gap-3 sm:items-end">
          <MonthFilter disabled={isLoading} onChange={changeMonthFilter} value={monthFilter} />
          <div className="grid w-full grid-cols-2 gap-3 sm:flex sm:w-auto">
            <button className="btn-entrar btn-entrar--sm disabled:cursor-not-allowed disabled:opacity-60" disabled={!canCreateEntry} onClick={() => openCreateModal("entry")} type="button">
              <span>Nova entrada</span>
            </button>
            <button className="btn-entrar btn-entrar--sm btn-entrar--outlined disabled:cursor-not-allowed disabled:opacity-60" disabled={!canCreateExpense} onClick={() => openCreateModal("expense")} type="button">
              <span>Nova saída</span>
            </button>
          </div>
        </div>
      </header>

      <DashboardStatusBanner
        message={loadMessage}
        onRefresh={() => void refreshTransactions()}
        status={loadStatus}
      />

      <FormStatusMessage message={actionMessage} type={actionMessageType} />

      <DashboardKpiGrid
        balance={balance}
        entriesTotal={entriesTotal}
        expensesTotal={expensesTotal}
        formatCurrency={currency}
        isLoading={isLoading}
      />

      <TransactionsAnalyticsCharts isLoading={isLoading} transactions={dashboardData.transactions} />

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <TransactionTypeTabs
          activeType={activeType}
          entryCount={entryTransactions.length}
          expenseCount={expenseTransactions.length}
          onChange={changeActiveType}
        />
        <p className="text-sm text-slate-500 dark:text-slate-400">
          {selectedIds.size > 0 ? `${selectedIds.size} item(ns) selecionado(s)` : `${filteredTransactions.length} item(ns) filtrado(s)`}
        </p>
      </div>

      <TransactionsGridFilters
        activeType={activeType}
        categoryOptions={categoryOptions}
        filteredCount={filteredTransactions.length}
        filters={transactionFilters}
        isLoading={isLoading}
        onChange={changeTransactionFilters}
        onReset={resetTransactionFilters}
        paymentMethodOptions={dashboardData.paymentMethods}
        totalCount={visibleTransactions.length}
      />

      <TransactionsManagementGrid
        activeType={activeType}
        emptyLabel={emptyGridLabel}
        isDeleting={isDeleting}
        onBulkDelete={() => void bulkDeleteSelected()}
        onDelete={(transaction) => void deleteTransaction(transaction)}
        onEdit={openEditModal}
        onSelect={selectTransaction}
        onSelectAll={selectAllVisible}
        onStatusUnavailable={showStatusUnavailable}
        pagination={{
          currentPage: safeCurrentPage,
          endItem: paginationEndItem,
          onPageChange: setCurrentPage,
          onPageSizeChange: changePageSize,
          pageSize,
          startItem: paginationStartItem,
          totalItems: filteredTransactions.length,
          totalPages,
        }}
        selectedIds={selectedIds}
        transactions={paginatedTransactions}
      />

      <MovementModal
        entryTypes={dashboardData.entryTypes}
        expenseTypes={dashboardData.expenseTypes}
        mode={modalState?.mode}
        onClose={() => setModalState(null)}
        onSaved={async () => {
          setSelectedIds(new Set());
          setActionMessageType("success");
          setActionMessage(modalState?.mode === "edit" ? "Transação atualizada com sucesso." : "Transação cadastrada com sucesso.");
          await refreshTransactions();
        }}
        paymentMethods={dashboardData.paymentMethods}
        transaction={modalState?.transaction}
        type={modalState?.type ?? null}
        walletId={dashboardData.wallet?.id ?? null}
      />
    </AuthenticatedAppShell>
  );
}
