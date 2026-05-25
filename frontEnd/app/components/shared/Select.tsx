import type { ReactNode } from "react";

function ChevronDownIcon({ className }: { className?: string }) {
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
      <path d="m6 9 6 6 6-6" />
    </svg>
  );
}

export type SelectProps = {
  children: ReactNode;
  className?: string;
  disabled?: boolean;
  onChange: (event: React.ChangeEvent<HTMLSelectElement>) => void;
  value: string | number;
};

export function Select({ children, className = "", disabled, onChange, value }: SelectProps) {
  return (
    <div className="relative">
      <select
        className={`w-full appearance-none rounded-md border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-300 dark:focus:ring-blue-300/20 ${className}`}
        disabled={disabled}
        onChange={onChange}
        value={value}
      >
        {children}
      </select>
      <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 dark:text-slate-500">
        <ChevronDownIcon className="h-4 w-4" />
      </div>
    </div>
  );
}
