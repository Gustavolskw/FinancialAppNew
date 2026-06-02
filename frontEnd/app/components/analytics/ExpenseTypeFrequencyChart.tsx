import type { ChartOptions } from "chart.js";
import { Doughnut } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import type { GroupedItem } from "../../Infrastructure/Api/analytics";

type Props = {
  items: GroupedItem[];
  isLoading: boolean;
};

const PALETTE = ["#2563eb", "#dc2626", "#059669", "#f59e0b", "#7c3aed", "#0f766e", "#64748b", "#db2777"];

const options: ChartOptions<"doughnut"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} ocorrências` } },
  },
};

export function ExpenseTypeFrequencyChart({ items, isLoading }: Props) {
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
      emptyLabel={isLoading ? "Carregando..." : "Nenhuma despesa encontrada."}
      hasData={top.length > 0}
      subtitle="Tipos de saída mais frequentes (quantidade)"
      title="Frequência por Tipo de Saída"
    >
      <Doughnut data={data} options={options} />
    </ChartCard>
  );
}
