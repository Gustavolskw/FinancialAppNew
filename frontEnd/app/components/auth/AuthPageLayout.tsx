import type { ReactNode } from "react";

type AuthPageLayoutProps = {
  hero: ReactNode;
  children: ReactNode;
};

type AuthHeroPanelProps = {
  topLabel: string;
  topBadge: string;
  eyebrow: string;
  title: string;
  footer: string;
  children?: ReactNode;
};

type AuthFormHeaderProps = {
  icon: ReactNode;
  eyebrow: string;
  subtitle: string;
  title: string;
  description: string;
};

export function AuthPageLayout({ hero, children }: AuthPageLayoutProps) {
  return (
    <main className="min-h-screen bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-white">
      <div className="grid min-h-screen lg:grid-cols-[minmax(520px,1.05fr)_minmax(0,0.95fr)]">
        {hero}
        <section className="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8 lg:px-12">
          <div className="w-full max-w-md">{children}</div>
        </section>
      </div>
    </main>
  );
}

export function AuthHeroPanel({ topLabel, topBadge, eyebrow, title, footer, children }: AuthHeroPanelProps) {
  return (
    <section className="hidden min-h-screen bg-blue-950 text-white lg:block">
      <div className="flex h-full flex-col justify-between px-12 py-10">
        <div className="flex items-center justify-between text-sm text-blue-100">
          <span className="font-medium">{topLabel}</span>
          <span className="rounded-full border border-white/20 px-3 py-1">{topBadge}</span>
        </div>

        <div className="mx-auto w-full max-w-xl">
          <p className="text-sm font-semibold uppercase tracking-[0.16em] text-blue-200">{eyebrow}</p>
          <h2 className="mt-3 max-w-xl text-4xl font-semibold tracking-normal text-white">{title}</h2>
          {children}
        </div>

        <p className="max-w-lg text-sm leading-6 text-blue-100">{footer}</p>
      </div>
    </section>
  );
}

export function AuthFormHeader({ icon, eyebrow, subtitle, title, description }: AuthFormHeaderProps) {
  return (
    <>
      <div className="mb-8 flex items-center gap-3">
        <div className="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-700 text-white shadow-sm shadow-blue-900/20">
          {icon}
        </div>
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">
            {eyebrow}
          </p>
          <p className="text-sm text-slate-500 dark:text-slate-400">{subtitle}</p>
        </div>
      </div>

      <div className="mb-8">
        <h1 className="text-3xl font-semibold tracking-normal text-slate-950 dark:text-white sm:text-4xl">
          {title}
        </h1>
        <p className="mt-3 text-base leading-7 text-slate-600 dark:text-slate-300">
          {description}
        </p>
      </div>
    </>
  );
}
