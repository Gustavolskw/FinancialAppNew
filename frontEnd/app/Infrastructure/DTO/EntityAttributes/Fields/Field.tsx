import type { ReactNode } from "react";

import type { FieldComponentProps, FieldDefinition, FieldOption, FieldOptionValue, MessageBag } from "./FieldsInterface";

export function classNames(...values: Array<string | false | null | undefined>): string {
  return values.filter(Boolean).join(" ");
}

export function hasFilledValue(value: unknown): boolean {
  if (value === null || value === undefined) {
    return false;
  }

  if (typeof value === "string" && value.trim() === "") {
    return false;
  }

  return !Array.isArray(value) || value.length > 0;
}

export function fieldId(name: string): string {
  return `field-${name}`;
}

export function fieldLabel(name: string, label?: string): string {
  return label ?? name;
}

export function fieldErrorId(name: string): string {
  return `${fieldId(name)}-error`;
}

export function fieldHelpId(name: string): string {
  return `${fieldId(name)}-help`;
}

export function fieldDescriptionId(name: string, error?: string, helpText?: string): string | undefined {
  if (error) {
    return fieldErrorId(name);
  }

  if (helpText) {
    return fieldHelpId(name);
  }

  return undefined;
}

export function fieldControlProps(name: string, error?: string, helpText?: string) {
  return {
    "aria-describedby": fieldDescriptionId(name, error, helpText),
    "aria-invalid": error ? true : undefined,
  };
}

export function validateRequired(value: unknown, field: FieldDefinition): string | null {
  if (!field.required || hasFilledValue(value)) {
    return null;
  }

  return `Campo ${field.name} é obrigatório`;
}

export function validateAdditional(value: unknown, field: FieldDefinition): string | null {
  return field.additionalFieldValidation?.(value, field) ?? null;
}

export function optionDomValue(value: FieldOptionValue): string {
  return String(value);
}

export function findOptionValue(options: FieldOption[] | undefined, value: string): FieldOptionValue | null {
  return options?.find((option) => optionDomValue(option.value) === value)?.value ?? null;
}

type FieldShellProps = Pick<
  FieldComponentProps,
  "name" | "label" | "required" | "error" | "helpText" | "className"
> & {
  children: ReactNode;
};

export function FieldShell({
  name,
  label,
  required = false,
  error,
  helpText,
  className,
  children,
}: FieldShellProps) {
  return (
    <div className={classNames("flex w-full flex-col gap-1.5", className)} data-field={name}>
      <label className="text-sm font-medium text-gray-800 dark:text-gray-100" htmlFor={fieldId(name)}>
        {fieldLabel(name, label)}
        {required && <span className="ml-1 text-red-600 dark:text-red-400">*</span>}
      </label>
      <div>{children}</div>
      {error ? (
        <p className="text-xs font-medium text-red-600 dark:text-red-400" id={fieldErrorId(name)}>
          {error}
        </p>
      ) : helpText ? (
        <p className="text-xs text-gray-500 dark:text-gray-400" id={fieldHelpId(name)}>
          {helpText}
        </p>
      ) : null}
    </div>
  );
}

type FieldMessageBagProps = {
  messages: MessageBag;
  labels?: Record<string, string>;
  title?: string;
  className?: string;
  onDismiss?: () => void;
  dismissLabel?: string;
};

function messageEntries(messages: MessageBag): Array<[string, string]> {
  return Object.entries(messages).filter((entry): entry is [string, string] => Boolean(entry[1]));
}

export function FieldMessageBag({
  messages,
  labels = {},
  title = "Revise os campos destacados",
  className,
}: FieldMessageBagProps) {
  const entries = messageEntries(messages);

  if (entries.length === 0) {
    return null;
  }

  return (
    <div
      aria-live="polite"
      className={classNames("rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200", className)}
      role="alert"
    >
      <p className="font-semibold">{title}</p>
      <ul className="mt-2 space-y-1">
        {entries.map(([fieldName, message]) => (
          <li key={fieldName}>
            <a className="underline-offset-4 hover:underline" href={`#${fieldId(fieldName)}`}>
              {labels[fieldName] ?? fieldName}: {message}
            </a>
          </li>
        ))}
      </ul>
    </div>
  );
}

export function FieldToastMessageBag({
  messages,
  labels = {},
  title = "Revise os campos destacados",
  className,
  onDismiss,
  dismissLabel = "Fechar",
}: FieldMessageBagProps) {
  const entries = messageEntries(messages);

  if (entries.length === 0) {
    return null;
  }

  return (
    <div className={classNames("pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4", className)}>
      <div
        aria-live="polite"
        className="pointer-events-auto w-full max-w-md rounded-lg border border-red-200 bg-white p-4 text-sm text-red-700 shadow-2xl shadow-red-950/10 dark:border-red-900/60 dark:bg-slate-950 dark:text-red-200"
        role="alert"
      >
        <div className="flex items-start gap-3">
          <div className="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700 dark:bg-red-950 dark:text-red-200">
            !
          </div>
          <div className="min-w-0 flex-1">
            <p className="font-semibold">{title}</p>
            <ul className="mt-2 space-y-1">
              {entries.map(([fieldName, message]) => (
                <li key={fieldName}>
                  <a className="underline-offset-4 hover:underline" href={`#${fieldId(fieldName)}`}>
                    {labels[fieldName] ?? fieldName}: {message}
                  </a>
                </li>
              ))}
            </ul>
          </div>
          {onDismiss && (
            <button
              aria-label={dismissLabel}
              className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-600 dark:text-red-200 dark:hover:bg-red-950"
              onClick={onDismiss}
              type="button"
            >
              <span aria-hidden="true">X</span>
            </button>
          )}
        </div>
      </div>
    </div>
  );
}

export const baseInputClassName =
  "w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-base text-gray-950 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 sm:px-3 sm:py-2 sm:text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-gray-50 dark:placeholder:text-gray-500 dark:focus:border-emerald-400 dark:focus:ring-emerald-400/20";
