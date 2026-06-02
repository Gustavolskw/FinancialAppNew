import type { ChartOptions } from "chart.js";
import { Bar } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import { currency } from "../dashboard/dashboardMetrics";
import type { GroupedItem } from "../../Infrastructure/Api/analytics";

type Props = {
  items: GroupedItem[];
  isLoading: boolean;
};

const PALETTE = ["#f59e0b", "#2563eb", "#059669", "#dc2626", "#7c3aed", "#0f766e", "#db2777", "#64748b"];

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

export function PaymentMethodAmountChart({ items, isLoading }: Props) {
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
      emptyLabel={isLoading ? "Carregando..." : "Nenhuma forma de pagamento encontrada."}
      hasData={top.length > 0}
      subtitle="Valor por forma de pagamento (R$)"
      title="Valores por Forma de Pagamento"
    >
      <Bar data={data} options={options} />
    </ChartCard>
  );
}
