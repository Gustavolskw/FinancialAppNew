type ProtectedRouteFallbackProps = {
  message?: string;
};

export function ProtectedRouteFallback({ message = "Validando sessão..." }: ProtectedRouteFallbackProps) {
  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4 text-slate-700 dark:bg-slate-950 dark:text-slate-200">
      <div className="rounded-lg border border-slate-200 bg-white px-5 py-4 text-sm font-semibold shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {message}
      </div>
    </div>
  );
}
