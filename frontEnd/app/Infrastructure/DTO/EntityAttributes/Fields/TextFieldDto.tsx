import { FieldTypeEnum, getFieldSizeValidation } from "../FieldTypeEnum";
import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId } from "./Field";
import type { FieldComponentProps } from "./FieldsInterface";

type TextFieldDtoProps = FieldComponentProps<string> & {
  rows?: number;
  fieldType?: FieldTypeEnum.TEXTFIELD | FieldTypeEnum.EMAILFIELD | FieldTypeEnum.LOCATIONFIELD;
};

export function TextFieldDto({
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
  rows = 3,
  fieldType = FieldTypeEnum.TEXTFIELD,
}: TextFieldDtoProps) {
  const isSingleLine = fieldType === FieldTypeEnum.EMAILFIELD || fieldType === FieldTypeEnum.LOCATIONFIELD;

  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      {isSingleLine ? (
        <input
          autoFocus={autoFocus}
          className={classNames(baseInputClassName, inputClassName)}
          disabled={disabled}
          id={fieldId(name)}
          maxLength={getFieldSizeValidation(fieldType)}
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => onChange(event.target.value)}
          placeholder={placeholder}
          readOnly={readOnly}
          inputMode={fieldType === FieldTypeEnum.EMAILFIELD ? "email" : undefined}
          type="text"
          value={value ?? ""}
        />
      ) : (
        <textarea
          autoFocus={autoFocus}
          className={classNames(baseInputClassName, "min-h-24 resize-y", inputClassName)}
          disabled={disabled}
          id={fieldId(name)}
          maxLength={getFieldSizeValidation(fieldType)}
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => onChange(event.target.value)}
          placeholder={placeholder}
          readOnly={readOnly}
          rows={rows}
          value={value ?? ""}
        />
      )}
    </FieldShell>
  );
}
