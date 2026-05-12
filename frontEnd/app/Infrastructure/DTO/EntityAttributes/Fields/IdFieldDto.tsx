import { FieldTypeEnum, getFieldSizeValidation } from "../FieldTypeEnum";
import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId } from "./Field";
import type { FieldComponentProps } from "./FieldsInterface";

export function IdFieldDto({
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
}: FieldComponentProps<number | null>) {
  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <input
        autoFocus={autoFocus}
        className={classNames(baseInputClassName, inputClassName)}
        disabled={disabled}
        id={fieldId(name)}
        inputMode="numeric"
        maxLength={getFieldSizeValidation(FieldTypeEnum.IDFIELD)}
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
