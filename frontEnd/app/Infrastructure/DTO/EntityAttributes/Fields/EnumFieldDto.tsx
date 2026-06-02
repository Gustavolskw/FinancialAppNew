import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId, findOptionValue, optionDomValue } from "./Field";
import type { FieldComponentProps, FieldOption } from "./FieldsInterface";

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

type EnumFieldDtoProps = FieldComponentProps<number | null> & {
  options: Array<FieldOption<number>>;
};

export function EnumFieldDto({
  name,
  label,
  value,
  onChange,
  required = false,
  error,
  disabled,
  readOnly,
  placeholder,
  helpText,
  className,
  inputClassName,
  autoFocus,
  onBlur,
  options,
}: EnumFieldDtoProps) {
  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <div className="relative">
        <select
          autoFocus={autoFocus}
          className={classNames(baseInputClassName, "cursor-pointer appearance-none pr-10", inputClassName)}
          disabled={disabled || readOnly}
          id={fieldId(name)}
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => {
            const optionValue = findOptionValue(options, event.target.value);
            onChange(typeof optionValue === "number" ? optionValue : null);
          }}
          value={value ?? ""}
        >
          <option value="">{placeholder ?? "Selecione"}</option>
          {options.map((option) => (
            <option disabled={option.disabled} key={option.value} value={optionDomValue(option.value)}>
              {option.label}
            </option>
          ))}
        </select>
        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 dark:text-slate-500">
          <ChevronDownIcon className="h-5 w-5 sm:h-4 sm:w-4" />
        </div>
      </div>
    </FieldShell>
  );
}
