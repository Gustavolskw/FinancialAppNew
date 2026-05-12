import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId, findOptionValue, optionDomValue } from "./Field";
import type { FieldComponentProps, FieldOption } from "./FieldsInterface";

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
