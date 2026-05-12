import type { ReactNode } from "react";

type ChartCardProps = {
  title: string;
  subtitle: string;
  hasData: boolean;
  emptyLabel: string;
  heightClassName?: string;
  children: ReactNode;
};

export function ChartCard({
  title,
  subtitle,
  hasData,
  emptyLabel,
  heightClassName = "h-72",
  children,
}: ChartCardProps) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4">
        <h2 className="text-lg font-semibold text-slate-950 dark:text-white">{title}</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">{subtitle}</p>
      </div>
      <div className={heightClassName}>
        {hasData ? children : <ChartEmptyState label={emptyLabel} />}
      </div>
    </div>
  );
}

export function ChartEmptyState({ label }: { label: string }) {
  return (
    <div className="flex h-full min-h-40 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
      {label}
    </div>
  );
}
