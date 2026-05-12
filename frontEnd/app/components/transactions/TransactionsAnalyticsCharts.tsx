import type { ChartOptions } from "chart.js";
import { Bar, Doughnut } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import { amountStats, currency, hasAmountStats, sumByType } from "../dashboard/dashboardMetrics";
import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";

type TransactionsAnalyticsChartsProps = {
  isLoading: boolean;
  transactions: DashboardTransaction[];
};

const chartPalette = ["#2563eb", "#dc2626", "#059669", "#f59e0b", "#7c3aed", "#0f766e", "#64748b", "#db2777"];

const barOptions: ChartOptions<"bar"> = {
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

const doughnutOptions: ChartOptions<"doughnut"> = {
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

export function TransactionsAnalyticsCharts({ isLoading, transactions }: TransactionsAnalyticsChartsProps) {
  const entriesTotal = sumByType(transactions, "entry");
  const expensesTotal = sumByType(transactions, "expense");
  const expenseTypeStats = amountStats(transactions, "category", "expense").slice(0, 8);
  const totalsData = {
    labels: ["Entradas", "Saídas"],
    datasets: [
      {
        backgroundColor: ["#059669", "#dc2626"],
        borderRadius: 8,
        data: [entriesTotal, expensesTotal],
      },
    ],
  };
  const expenseTypeData = {
    labels: expenseTypeStats.map((item) => item.label),
    datasets: [
      {
        backgroundColor: chartPalette,
        borderWidth: 0,
        data: expenseTypeStats.map((item) => item.total),
      },
    ],
  };

  return (
    <section className="grid gap-4 lg:grid-cols-2">
      <ChartCard
        emptyLabel={isLoading ? "Carregando valores de entrada e saída..." : "Nenhuma entrada ou saída encontrada."}
        hasData={entriesTotal > 0 || expensesTotal > 0}
        subtitle="Comparativo direto entre todos os valores retornados pela API"
        title="Entradas x saídas"
      >
        <Bar data={totalsData} options={barOptions} />
      </ChartCard>

      <ChartCard
        emptyLabel={isLoading ? "Carregando despesas por tipo..." : "Nenhuma despesa encontrada para agrupar por tipo."}
        hasData={hasAmountStats(expenseTypeStats)}
        subtitle="Agrupamento por ExpenseType para análise rápida de gastos"
        title="Saídas por tipo de despesa"
      >
        <Doughnut data={expenseTypeData} options={doughnutOptions} />
      </ChartCard>
    </section>
  );
}
