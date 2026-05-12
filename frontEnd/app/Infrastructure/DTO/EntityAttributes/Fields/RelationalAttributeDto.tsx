import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId, findOptionValue, optionDomValue } from "./Field";
import type { FieldComponentProps, FieldOption } from "./FieldsInterface";

type RelationalAttributeDtoProps = FieldComponentProps<number | null> & {
  options?: Array<FieldOption<number>>;
};

export function RelationalAttributeDto({
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
}: RelationalAttributeDtoProps) {
  if (!options || options.length === 0) {
    return (
      <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
        <input
          autoFocus={autoFocus}
          className={classNames(baseInputClassName, inputClassName)}
          disabled={disabled}
          id={fieldId(name)}
          inputMode="numeric"
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => onChange(event.target.value === "" ? null : Number(event.target.value))}
          placeholder={placeholder}
          readOnly={readOnly}
          type="number"
          value={value ?? ""}
        />
      </FieldShell>
    );
  }

  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <select
        autoFocus={autoFocus}
        className={classNames(baseInputClassName, inputClassName)}
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
    </FieldShell>
  );
}
