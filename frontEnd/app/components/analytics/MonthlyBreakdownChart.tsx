import type { ChartOptions } from "chart.js";
import { Line } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import { currency } from "../dashboard/dashboardMetrics";
import type { MonthlyBreakdownItem } from "../../Infrastructure/Api/analytics";

type Props = {
  breakdown: MonthlyBreakdownItem[];
  isLoading: boolean;
};

const MONTH_LABELS = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

const options: ChartOptions<"line"> = {
  maintainAspectRatio: false,
  interaction: { intersect: false, mode: "index" },
  plugins: {
    legend: { position: "bottom" },
    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${currency(Number(ctx.raw ?? 0))}` } },
  },
  scales: {
    y: { ticks: { callback: (v) => currency(Number(v)) } },
  },
};

export function MonthlyBreakdownChart({ breakdown, isLoading }: Props) {
  const hasData = breakdown.some((m) => m.entries > 0 || m.expenses > 0);

  const data = {
    labels: MONTH_LABELS,
    datasets: [
      {
        label: "Entradas",
        data: breakdown.map((m) => m.entries),
        borderColor: "#059669",
        backgroundColor: "#05966920",
        tension: 0.3,
        fill: true,
      },
      {
        label: "Saídas",
        data: breakdown.map((m) => m.expenses),
        borderColor: "#dc2626",
        backgroundColor: "#dc262620",
        tension: 0.3,
        fill: true,
      },
      {
        label: "Saldo",
        data: breakdown.map((m) => m.balance),
        borderColor: "#2563eb",
        backgroundColor: "transparent",
        borderDash: [5, 5],
        tension: 0.3,
      },
    ],
  };

  return (
    <ChartCard
      emptyLabel={isLoading ? "Carregando..." : "Nenhum dado mensal encontrado."}
      hasData={hasData}
      heightClassName="h-80"
      subtitle="Evolução mensal de entradas, saídas e saldo"
      title="Breakdown Mensal"
    >
      <Line data={data} options={options} />
    </ChartCard>
  );
}
