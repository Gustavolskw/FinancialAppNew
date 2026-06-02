import type { ChartOptions } from "chart.js";
import { Doughnut } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import type { GroupedItem } from "../../Infrastructure/Api/analytics";

type Props = {
  items: GroupedItem[];
  isLoading: boolean;
};

const PALETTE = ["#f59e0b", "#2563eb", "#059669", "#dc2626", "#7c3aed", "#0f766e", "#db2777", "#64748b"];

const options: ChartOptions<"doughnut"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} ocorrências` } },
  },
};

export function PaymentMethodFrequencyChart({ items, isLoading }: Props) {
  const top = items.slice(0, 8);

  const data = {
    labels: top.map((i) => i.label),
    datasets: [{
      backgroundColor: PALETTE,
      borderWidth: 0,
      data: top.map((i) => i.count),
    }],
  };

  return (
    <ChartCard
      emptyLabel={isLoading ? "Carregando..." : "Nenhuma forma de pagamento encontrada."}
      hasData={top.length > 0}
      subtitle="Formas de pagamento mais utilizadas (quantidade)"
      title="Frequência por Forma de Pagamento"
    >
      <Doughnut data={data} options={options} />
    </ChartCard>
  );
}
