import type { ChartOptions } from "chart.js";
import { Doughnut } from "react-chartjs-2";

import { ChartCard } from "../dashboard/ChartCard";
import type { GroupedItem } from "../../Infrastructure/Api/analytics";

type Props = {
  items: GroupedItem[];
  isLoading: boolean;
};

const PALETTE = ["#059669", "#2563eb", "#f59e0b", "#7c3aed", "#0f766e", "#db2777", "#64748b", "#dc2626"];

const options: ChartOptions<"doughnut"> = {
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} ocorrências` } },
  },
};

export function EntryTypeFrequencyChart({ items, isLoading }: Props) {
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
      emptyLabel={isLoading ? "Carregando..." : "Nenhuma entrada encontrada."}
      hasData={top.length > 0}
      subtitle="Tipos de entrada mais utilizados (quantidade)"
      title="Frequência por Tipo de Entrada"
    >
      <Doughnut data={data} options={options} />
    </ChartCard>
  );
}
