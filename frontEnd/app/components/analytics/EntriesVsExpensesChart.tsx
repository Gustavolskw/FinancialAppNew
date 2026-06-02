import type { ChartOptions } from "chart.js";
import { Bar } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import { currency } from "../dashboard/dashboardMetrics";
import type { AnalyticsTotals } from "../../Infrastructure/Api/analytics";

type Props = {
  totals: AnalyticsTotals;
  isLoading: boolean;
};

const options: ChartOptions<"bar"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { callbacks: { label: (ctx) => currency(Number(ctx.raw ?? 0)) } },
  },
  scales: {
    y: { ticks: { callback: (v) => currency(Number(v)) } },
  },
};

export function EntriesVsExpensesChart({ totals, isLoading }: Props) {
  const data = {
    labels: ["Entradas", "Saídas"],
    datasets: [{
      backgroundColor: ["#059669", "#dc2626"],
      borderRadius: 8,
      data: [totals.entries, totals.expenses],
    }],
  };

  return (
    <ChartCard
      emptyLabel={isLoading ? "Carregando..." : "Nenhum dado encontrado."}
      hasData={totals.entries > 0 || totals.expenses > 0}
      subtitle="Total anual de entradas vs saídas"
      title="Entradas x Saídas"
    >
      <Bar data={data} options={options} />
    </ChartCard>
  );
}
