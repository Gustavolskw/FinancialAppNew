import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
  type ChartOptions,
} from "chart.js";
import { useEffect, useState } from "react";
import { Bar, Doughnut } from "react-chartjs-2";
import { useNavigate } from "react-router";

import type { Route } from "./+types/dashboard";
import { ProtectedRouteFallback } from "../components/auth/ProtectedRouteFallback";
import { ChartCard } from "../components/dashboard/ChartCard";
import { DashboardKpiGrid } from "../components/dashboard/DashboardKpiGrid";
import { DashboardStatusBanner } from "../components/dashboard/DashboardStatusBanner";
import { TransactionsTable } from "../components/dashboard/TransactionsTable";
import { amountStats, currency, hasAmountStats, hasPositiveValue, periodStats, sumByType } from "../components/dashboard/dashboardMetrics";
import { currentMonthFilter, MonthFilter, monthFilterLabel, type MonthFilterValue } from "../components/filters/MonthFilter";
import { AuthenticatedAppShell } from "../components/navigation/AuthenticatedAppShell";
import { MovementModal } from "../components/transactions/MovementModal";
import { ApiRequestError } from "../Infrastructure/Api/client";
import {
  loadDashboardData,
  type DashboardData,
} from "../Infrastructure/Api/dashboard";
import type { TransactionType } from "../Infrastructure/Api/movements";
import { clearAuthSession } from "../Infrastructure/Auth/session";
import { useRequireAuth } from "../Infrastructure/Auth/useRequireAuth";

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

type LoadStatus = "loading" | "ready" | "error";
type ModalType = TransactionType | null;

const emptyDashboard: DashboardData = {
  entryTypes: [],
  expenseTypes: [],
  paymentMethods: [],
  transactions: [],
  wallet: null,
};

const chartPalette = ["#2563eb", "#059669", "#f59e0b", "#dc2626", "#7c3aed", "#0f766e", "#64748b", "#db2777"];

function apiErrorMessage(error: unknown, fallback: string): string {
  return error instanceof ApiRequestError ? error.message : fallback;
}

const groupedCurrencyBarOptions: ChartOptions<"bar"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
    tooltip: {
      callbacks: {
        label(context) {
          const label = context.dataset.label ? `${context.dataset.label}: ` : "";

          return `${label}${currency(Number(context.raw ?? 0))}`;
        },
      },
    },
  },
  scales: {
    y: {
      ticks: {
        callback(value) {
          return currency(Number(value));
        },
      },
    },
  },
};

const singleCurrencyBarOptions: ChartOptions<"bar"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label(context) {
          return currency(Number(context.raw ?? 0));
        },
      },
    },
  },
  scales: {
    y: {
      ticks: {
        callback(value) {
          return currency(Number(value));
        },
      },
    },
  },
};

const doughnutCurrencyOptions: ChartOptions<"doughnut"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
    tooltip: {
      callbacks: {
        label(context) {
          return `${context.label}: ${currency(Number(context.raw ?? 0))}`;
        },
      },
    },
  },
};

export function meta({ }: Route.MetaArgs) {
  return [
    { title: "Carteira | AppFinanças" },
    { name: "description", content: "Dashboard de carteira com transações, entradas, despesas e métodos de pagamento." },
  ];
}

export default function Dashboard() {
  const navigate = useNavigate();
  const authStatus = useRequireAuth();
  const [dashboard, setDashboard] = useState<DashboardData | null>(null);
  const [loadStatus, setLoadStatus] = useState<LoadStatus>("loading");
  const [loadMessage, setLoadMessage] = useState("Carregando dados da carteira...");
  const [modalType, setModalType] = useState<ModalType>(null);
  const [monthFilter, setMonthFilter] = useState<MonthFilterValue>(() => {
    try {
      const stored = sessionStorage.getItem("dashboard:monthFilter");
      if (stored) {
        const parsed = JSON.parse(stored) as MonthFilterValue;
        if (parsed.month >= 1 && parsed.month <= 12 && parsed.year > 0) {
          return parsed;
        }
      }
    } catch { /* ignore */ }
    return currentMonthFilter();
  });

  useEffect(() => {
    sessionStorage.setItem("dashboard:monthFilter", JSON.stringify(monthFilter));
  }, [monthFilter.month, monthFilter.year]);

  const dashboardData = dashboard ?? emptyDashboard;
  const isLoadingDashboard = loadStatus === "loading";
  const selectedPeriodLabel = monthFilterLabel(monthFilter);
  const entriesTotal = sumByType(dashboardData.transactions, "entry");
  const expensesTotal = sumByType(dashboardData.transactions, "expense");
  const balance = entriesTotal - expensesTotal;
  const periods = periodStats(dashboardData.transactions);
  const entryTypeStats = amountStats(dashboardData.transactions, "category", "entry");
  const expenseTypeStats = amountStats(dashboardData.transactions, "category", "expense");
  const paymentStats = amountStats(dashboardData.transactions, "paymentMethod", "expense");
  const walletTitle = dashboardData.wallet?.title ?? (isLoadingDashboard ? "Carregando carteira" : "Carteira não encontrada");
  const emptyPeriodLabel = isLoadingDashboard
    ? "Carregando movimentações..."
    : "Nenhuma competência encontrada para montar entradas, despesas e saldo.";
  const emptyEntryTypeLabel = isLoadingDashboard
    ? "Carregando tipos de entrada..."
    : "Nenhuma entrada encontrada para agrupar por tipo.";
  const emptyExpenseTypeLabel = isLoadingDashboard
    ? "Carregando tipos de despesa..."
    : "Nenhuma despesa encontrada para agrupar por categoria.";
  const emptyPaymentLabel = isLoadingDashboard
    ? "Carregando métodos de pagamento..."
    : "Nenhuma despesa com método de pagamento encontrada.";
  const emptyTransactionsLabel = isLoadingDashboard
    ? "Carregando movimentações..."
    : "Nenhuma movimentação encontrada para esta carteira.";
  const hasPeriodData = hasPositiveValue(periods.flatMap((period) => [period.entryTotal, period.expenseTotal, Math.abs(period.balance)]));
  const hasEntryTypeData = hasAmountStats(entryTypeStats);
  const hasExpenseTypeData = hasAmountStats(expenseTypeStats);
  const hasPaymentData = hasAmountStats(paymentStats);
  const canCreateEntry = loadStatus === "ready" && dashboardData.wallet !== null && dashboardData.entryTypes.length > 0;
  const canCreateExpense = loadStatus === "ready"
    && dashboardData.wallet !== null
    && dashboardData.expenseTypes.length > 0
    && dashboardData.paymentMethods.length > 0;

  const periodData = {
    labels: periods.map((period) => period.label),
    datasets: [
      {
        backgroundColor: "#059669",
        borderRadius: 6,
        data: periods.map((period) => period.entryTotal),
        label: "Entradas",
      },
      {
        backgroundColor: "#dc2626",
        borderRadius: 6,
        data: periods.map((period) => period.expenseTotal),
        label: "Despesas",
      },
      {
        backgroundColor: "#2563eb",
        borderRadius: 6,
        data: periods.map((period) => period.balance),
        label: "Saldo",
      },
    ],
  };

  const entryTypeData = {
    labels: entryTypeStats.slice(0, 8).map((item) => item.label),
    datasets: [
      {
        backgroundColor: chartPalette,
        borderWidth: 0,
        data: entryTypeStats.slice(0, 8).map((item) => item.total),
      },
    ],
  };

  const expenseTypeData = {
    labels: expenseTypeStats.slice(0, 8).map((item) => item.label),
    datasets: [
      {
        backgroundColor: chartPalette,
        borderWidth: 0,
        data: expenseTypeStats.slice(0, 8).map((item) => item.total),
      },
    ],
  };

  const paymentData = {
    labels: paymentStats.map((item) => item.label),
    datasets: [
      {
        backgroundColor: "#2563eb",
        borderRadius: 6,
        data: paymentStats.map((item) => item.total),
        label: "Despesas",
      },
    ],
  };

  async function refreshDashboard() {
    setLoadStatus("loading");
    setLoadMessage(`Carregando dados de ${selectedPeriodLabel}...`);

    try {
      const data = await loadDashboardData(monthFilter);
      setDashboard(data);
      setLoadStatus("ready");
      setLoadMessage(data.wallet ? `Dados de ${selectedPeriodLabel} carregados.` : "Nenhuma carteira encontrada.");
    } catch (error) {
      if (error instanceof ApiRequestError && error.statusCode === 401) {
        clearAuthSession();
        navigate("/", { replace: true });
        return;
      }

      setDashboard(null);
      setLoadStatus("error");
      setLoadMessage(apiErrorMessage(error, "Não foi possível carregar os dados do dashboard."));
    }
  }

  useEffect(() => {
    if (authStatus !== "authenticated") {
      return;
    }

    void refreshDashboard();
  }, [authStatus, monthFilter.month, monthFilter.year]);

  useEffect(() => {
    const handleSessionUpdate = () => {
      void refreshDashboard();
    };

    window.addEventListener("session-updated", handleSessionUpdate);

    return () => {
      window.removeEventListener("session-updated", handleSessionUpdate);
    };
  }, [monthFilter.month, monthFilter.year]);

  function openModal(type: TransactionType) {
    setModalType(type);
  }

  if (authStatus !== "authenticated") {
    return <ProtectedRouteFallback message="Validando sessão..." />;
  }

  return (
    <AuthenticatedAppShell>
      <header className="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800">
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">
            {walletTitle}
          </p>
          <h1 className="mt-2 text-3xl font-semibold tracking-normal text-slate-950 dark:text-white">
            Visão geral da carteira
          </h1>
          <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Dados de carteira, entradas, despesas, tipos e métodos de pagamento.
          </p>
        </div>
        <div className="flex flex-col gap-3 sm:items-end">
          <MonthFilter disabled={isLoadingDashboard} onChange={setMonthFilter} value={monthFilter} />
          <div className="grid w-full grid-cols-2 gap-3 sm:flex sm:w-auto">
            <button className="btn-entrar btn-entrar--sm disabled:cursor-not-allowed disabled:opacity-60" disabled={!canCreateEntry} onClick={() => openModal("entry")} type="button">
              <span>Nova entrada</span>
            </button>
            <button className="btn-entrar btn-entrar--sm btn-entrar--outlined disabled:cursor-not-allowed disabled:opacity-60" disabled={!canCreateExpense} onClick={() => openModal("expense")} type="button">
              <span>Nova despesa</span>
            </button>
          </div>
        </div>
      </header>

      <DashboardStatusBanner
        message={loadMessage}
        onRefresh={() => void refreshDashboard()}
        status={loadStatus}
      />

      <DashboardKpiGrid
        balance={balance}
        entriesTotal={entriesTotal}
        expensesTotal={expensesTotal}
        formatCurrency={currency}
        isLoading={isLoadingDashboard}
      />

      <section className="grid gap-4 xl:grid-cols-[minmax(0,1.25fr)_minmax(360px,0.75fr)]">
        <ChartCard
          emptyLabel={emptyPeriodLabel}
          hasData={hasPeriodData}
          subtitle="Entradas, despesas e saldo agrupados por mês e ano"
          title="Resumo por competência"
        >
          <Bar data={periodData} options={groupedCurrencyBarOptions} />
        </ChartCard>

        <ChartCard
          emptyLabel={emptyEntryTypeLabel}
          hasData={hasEntryTypeData}
          subtitle="Distribuição por tipo de entrada"
          title="Entradas por tipo"
        >
          <Doughnut data={entryTypeData} options={doughnutCurrencyOptions} />
        </ChartCard>
      </section>

      <section className="grid gap-4 xl:grid-cols-[minmax(360px,0.8fr)_minmax(0,1.2fr)]">
        <div className="grid gap-4">
          <ChartCard
            emptyLabel={emptyExpenseTypeLabel}
            hasData={hasExpenseTypeData}
            heightClassName="h-64"
            subtitle="Distribuição por categoria de despesa"
            title="Despesas por categoria"
          >
            <Doughnut data={expenseTypeData} options={doughnutCurrencyOptions} />
          </ChartCard>

          <ChartCard
            emptyLabel={emptyPaymentLabel}
            hasData={hasPaymentData}
            heightClassName="h-64"
            subtitle="Distribuição por método de pagamento"
            title="Despesas por pagamento"
          >
            <Bar data={paymentData} options={singleCurrencyBarOptions} />
          </ChartCard>
        </div>

        <TransactionsTable
          emptyLabel={emptyTransactionsLabel}
          formatCurrency={currency}
          transactions={dashboardData.transactions}
        />
      </section>

      <MovementModal
        entryTypes={dashboardData.entryTypes}
        expenseTypes={dashboardData.expenseTypes}
        onClose={() => setModalType(null)}
        onSaved={refreshDashboard}
        paymentMethods={dashboardData.paymentMethods}
        type={modalType}
        walletId={dashboardData.wallet?.id ?? null}
      />
    </AuthenticatedAppShell>
  );
}
