import type { ChartOptions } from "chart.js";
import { Bar } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import { currency } from "../dashboard/dashboardMetrics";
import type { GroupedItem } from "../../Infrastructure/Api/analytics";

type Props = {
  items: GroupedItem[];
  isLoading: boolean;
};

const PALETTE = ["#2563eb", "#dc2626", "#059669", "#f59e0b", "#7c3aed", "#0f766e", "#64748b", "#db2777"];

const options: ChartOptions<"bar"> = {
  indexAxis: "y",
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { callbacks: { label: (ctx) => currency(Number(ctx.raw ?? 0)) } },
  },
  scales: {
    x: { ticks: { callback: (v) => currency(Number(v)) } },
  },
};

export function ExpenseTypeAmountChart({ items, isLoading }: Props) {
  const top = items.slice(0, 8);

  const data = {
    labels: top.map((i) => i.label),
    datasets: [{
      backgroundColor: PALETTE,
      borderRadius: 4,
      data: top.map((i) => i.total),
    }],
  };

  return (
    <ChartCard
      emptyLabel={isLoading ? "Carregando..." : "Nenhuma despesa encontrada."}
      hasData={top.length > 0}
      subtitle="Valor gasto por tipo de saída (R$)"
      title="Gastos por Tipo de Saída"
    >
      <Bar data={data} options={options} />
    </ChartCard>
  );
}
