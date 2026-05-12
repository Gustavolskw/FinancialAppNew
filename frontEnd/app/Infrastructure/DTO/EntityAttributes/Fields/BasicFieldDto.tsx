import { FieldTypeEnum } from "../FieldTypeEnum";
import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId, findOptionValue, optionDomValue } from "./Field";
import type { FieldComponentProps, FieldOption, FieldOptionValue } from "./FieldsInterface";

type BasicValue = string | number | Array<string | number> | null;

type BasicFieldDtoProps = FieldComponentProps<BasicValue> & {
  fieldType?: FieldTypeEnum.NUMERICFIELD | FieldTypeEnum.VALUEFIELD | FieldTypeEnum.OPTIONSFIELD;
  options?: FieldOption[];
  multiple?: boolean;
  min?: number;
  max?: number;
  step?: number | string;
};

export function BasicFieldDto({
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
  fieldType = FieldTypeEnum.NUMERICFIELD,
  options = [],
  multiple = false,
  min,
  max,
  step,
}: BasicFieldDtoProps) {
  if (fieldType === FieldTypeEnum.OPTIONSFIELD) {
    const selectedValues = Array.isArray(value) ? value.map(String) : value === null || value === undefined ? [] : [String(value)];

    return (
      <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
        <select
          autoFocus={autoFocus}
          className={classNames(baseInputClassName, multiple && "min-h-28", inputClassName)}
          disabled={disabled}
          id={fieldId(name)}
          multiple={multiple}
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => {
            if (multiple) {
              const values = Array.from(event.target.selectedOptions)
                .map((option) => findOptionValue(options, option.value))
                .filter((optionValue): optionValue is string | number => typeof optionValue === "string" || typeof optionValue === "number");

              onChange(values);
              return;
            }

            onChange(findOptionValue(options, event.target.value) as BasicValue);
          }}
          value={multiple ? selectedValues : selectedValues[0] ?? ""}
        >
          {!multiple && <option value="">{placeholder ?? "Selecione"}</option>}
          {options.map((option) => (
            <option disabled={option.disabled} key={optionDomValue(option.value)} value={optionDomValue(option.value)}>
              {option.label}
            </option>
          ))}
        </select>
      </FieldShell>
    );
  }

  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <input
        autoFocus={autoFocus}
        className={classNames(baseInputClassName, inputClassName)}
        disabled={disabled}
        id={fieldId(name)}
        max={max}
        min={min}
        name={name}
        {...fieldControlProps(name, error, helpText)}
        onBlur={onBlur}
        onChange={(event) => onChange(event.target.value === "" ? null : Number(event.target.value))}
        placeholder={placeholder}
        readOnly={readOnly}
        step={step ?? (fieldType === FieldTypeEnum.VALUEFIELD ? "0.01" : "1")}
        type="number"
        value={value === null || value === undefined || Array.isArray(value) ? "" : value}
      />
    </FieldShell>
  );
}

export type { FieldOptionValue };
