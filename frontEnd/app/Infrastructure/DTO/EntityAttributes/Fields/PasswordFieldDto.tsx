import { FieldTypeEnum, getFieldSizeValidation } from "../FieldTypeEnum";
import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId } from "./Field";
import type { FieldComponentProps } from "./FieldsInterface";

export function PasswordFieldDto({
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
}: FieldComponentProps<string>) {
  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <input
        autoComplete="current-password"
        autoFocus={autoFocus}
        className={classNames(baseInputClassName, inputClassName)}
        disabled={disabled}
        id={fieldId(name)}
        maxLength={getFieldSizeValidation(FieldTypeEnum.PASSWORDFIELD)}
        name={name}
        {...fieldControlProps(name, error, helpText)}
        onBlur={onBlur}
        onChange={(event) => onChange(event.target.value)}
        placeholder={placeholder}
        readOnly={readOnly}
        type="password"
        value={value ?? ""}
      />
    </FieldShell>
  );
}
