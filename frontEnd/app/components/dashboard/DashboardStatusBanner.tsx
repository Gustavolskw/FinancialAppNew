type DashboardStatusBannerProps = {
  status: "loading" | "ready" | "error";
  message: string;
  onRefresh: () => void;
};

export function DashboardStatusBanner({ status, message, onRefresh }: DashboardStatusBannerProps) {
  return (
    <div
      className={`flex flex-col gap-3 rounded-lg border px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between ${
        status === "error"
          ? "border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
          : "border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200"
      }`}
    >
      <span>{message}</span>
      <button className="w-fit font-semibold underline-offset-4 hover:underline" onClick={onRefresh} type="button">
        Atualizar
      </button>
    </div>
  );
}
