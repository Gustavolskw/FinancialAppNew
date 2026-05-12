import { classNames, fieldControlProps, FieldShell, fieldId } from "./Field";
import type { FieldComponentProps } from "./FieldsInterface";

export function StatusFieldDto({
  name,
  label,
  value,
  onChange,
  required = false,
  error,
  disabled,
  readOnly,
  helpText,
  className,
  inputClassName,
  autoFocus,
  onBlur,
}: FieldComponentProps<boolean>) {
  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <label
        className={classNames(
          "inline-flex min-h-10 w-fit items-center gap-3 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100",
          disabled || readOnly ? "cursor-not-allowed opacity-70" : "cursor-pointer",
          inputClassName,
        )}
        htmlFor={fieldId(name)}
      >
        <input
          autoFocus={autoFocus}
          checked={Boolean(value)}
          className="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-950"
          disabled={disabled || readOnly}
          id={fieldId(name)}
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => onChange(event.target.checked)}
          type="checkbox"
        />
        <span>{value ? "Ativo" : "Inativo"}</span>
      </label>
    </FieldShell>
  );
}
