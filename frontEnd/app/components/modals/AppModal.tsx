import type { ReactNode } from "react";

type AppModalProps = {
  children: ReactNode;
  title: string;
  description?: string;
  titleId?: string;
  onClose: () => void;
};

export function AppModal({ children, title, description, titleId = "app-modal-title", onClose }: AppModalProps) {
  return (
    <div className="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm" role="presentation">
      <section
        aria-labelledby={titleId}
        className="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-lg border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        role="dialog"
      >
        <div className="mb-5 flex items-start justify-between gap-4">
          <div>
            <h2 className="text-xl font-semibold text-slate-950 dark:text-white" id={titleId}>
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
            className="flex h-9 w-9 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white"
            onClick={onClose}
            type="button"
          >
            X
          </button>
        </div>

        {children}
      </section>
    </div>
  );
}
