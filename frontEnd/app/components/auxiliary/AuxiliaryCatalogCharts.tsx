import type { ChartOptions } from "chart.js";
import { Bar, Doughnut } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import { currency } from "../dashboard/dashboardMetrics";
import type { AuxiliaryCatalogItem, AuxiliaryCatalogType } from "../../Infrastructure/Api/catalogs";
import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";

export type CatalogUsageStat = {
  id: number;
  label: string;
  totalAmount: number;
  transactionCount: number;
};

type AuxiliaryCatalogChartsProps = {
  activeType: AuxiliaryCatalogType;
  isLoading: boolean;
  stats: CatalogUsageStat[];
};

const titles: Record<AuxiliaryCatalogType, string> = {
  entryType: "tipos de entrada",
  expenseType: "tipos de despesa",
  paymentMethod: "métodos de pagamento",
};

const chartPalette = ["#2563eb", "#059669", "#dc2626", "#f59e0b", "#7c3aed", "#0f766e", "#64748b", "#db2777"];

const countOptions: ChartOptions<"bar"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label(context) {
          return `${Number(context.raw ?? 0)} transação(ões)`;
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0,
      },
    },
  },
};

const amountOptions: ChartOptions<"doughnut"> = {
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

export function buildCatalogUsageStats(
  type: AuxiliaryCatalogType,
  items: AuxiliaryCatalogItem[],
  transactions: DashboardTransaction[],
): CatalogUsageStat[] {
  return items
    .map((item) => {
      const matchingTransactions = transactions.filter((transaction) => {
        if (type === "entryType") {
          return transaction.type === "entry" && transaction.categoryId === item.id;
        }

        if (type === "expenseType") {
          return transaction.type === "expense" && transaction.categoryId === item.id;
        }

        return transaction.type === "expense" && transaction.paymentMethodId === item.id;
      });

      return {
        id: item.id,
        label: item.name,
        totalAmount: matchingTransactions.reduce((total, transaction) => total + transaction.amount, 0),
        transactionCount: matchingTransactions.length,
      };
    })
    .sort((left, right) => right.transactionCount - left.transactionCount || right.totalAmount - left.totalAmount);
}

export function AuxiliaryCatalogCharts({ activeType, isLoading, stats }: AuxiliaryCatalogChartsProps) {
  const chartStats = stats.filter((stat) => stat.transactionCount > 0 || stat.totalAmount > 0).slice(0, 8);
  const countData = {
    labels: chartStats.map((stat) => stat.label),
    datasets: [
      {
        backgroundColor: "#2563eb",
        borderRadius: 8,
        data: chartStats.map((stat) => stat.transactionCount),
      },
    ],
  };
  const amountData = {
    labels: chartStats.map((stat) => stat.label),
    datasets: [
      {
        backgroundColor: chartPalette,
        borderWidth: 0,
        data: chartStats.map((stat) => stat.totalAmount),
      },
    ],
  };
  const hasData = chartStats.length > 0;

  return (
    <section className="grid gap-4 lg:grid-cols-2">
      <ChartCard
        emptyLabel={isLoading ? "Carregando uso dos itens..." : `Nenhum uso encontrado para ${titles[activeType]}.`}
        hasData={hasData}
        subtitle="Quantidade de transações vinculadas a cada item"
        title={`Uso de ${titles[activeType]}`}
      >
        <Bar data={countData} options={countOptions} />
      </ChartCard>

      <ChartCard
        emptyLabel={isLoading ? "Carregando valores por item..." : `Nenhum valor encontrado para ${titles[activeType]}.`}
        hasData={hasData}
        subtitle="Soma de valores das transações vinculadas"
        title={`Valores por ${titles[activeType]}`}
      >
        <Doughnut data={amountData} options={amountOptions} />
      </ChartCard>
    </section>
  );
}
