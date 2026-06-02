import { useEffect, type ReactNode } from "react";

function CloseIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      fill="none"
      stroke="currentColor"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth="2"
      viewBox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M18 6 6 18M6 6l12 12" />
    </svg>
  );
}

type AppModalProps = {
  children: ReactNode;
  title: string;
  description?: string;
  titleId?: string;
  onClose: () => void;
};

export function AppModal({ children, title, description, titleId = "app-modal-title", onClose }: AppModalProps) {
  useEffect(() => {
    const originalOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    function handleKeyDown(event: KeyboardEvent) {
      if (event.key === "Escape") {
        onClose();
      }
    }

    document.addEventListener("keydown", handleKeyDown);

    return () => {
      document.body.style.overflow = originalOverflow;
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [onClose]);

  return (
    <div className="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/60 px-3 py-4 backdrop-blur-sm sm:px-4 sm:py-6" role="presentation">
      <section
        aria-labelledby={titleId}
        className="flex max-h-[90vh] w-full max-w-lg flex-col rounded-lg border border-slate-200 bg-white shadow-2xl sm:max-h-[92vh] dark:border-slate-800 dark:bg-slate-900"
        role="dialog"
      >
        <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 p-4 sm:p-5 dark:border-slate-800">
          <div className="flex-1">
            <h2 className="text-lg font-semibold text-slate-950 sm:text-xl dark:text-white" id={titleId}>
              {title}
            </h2>
            {description && (
              <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {description}
              </p>
            )}
          </div>
          <button
            aria-label="Fechar modal"
            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-red-500 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 sm:h-9 sm:w-9 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300"
            onClick={onClose}
            type="button"
          >
            <CloseIcon className="h-5 w-5 sm:h-4 sm:w-4" />
          </button>
        </div>

        <div className="overflow-y-auto p-4 sm:p-5">
          {children}
        </div>
      </section>
    </div>
  );
}
