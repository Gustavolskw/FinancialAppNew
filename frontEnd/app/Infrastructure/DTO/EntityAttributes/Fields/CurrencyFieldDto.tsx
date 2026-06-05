import { NumericFormat, type NumberFormatValues } from "react-number-format";

import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId } from "./Field";
import type { FieldComponentProps } from "./FieldsInterface";

const CURRENCY_MAX = 9_999_999.99;

type CurrencyFieldDtoProps = FieldComponentProps<number | null>;

export function CurrencyFieldDto({
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
}: CurrencyFieldDtoProps) {
  function handleValueChange(values: NumberFormatValues) {
    const floatValue = values.floatValue;
    onChange(floatValue === undefined ? null : floatValue);
  }

  function isAllowed(values: NumberFormatValues): boolean {
    const { floatValue } = values;

    if (floatValue === undefined) {
      return true;
    }

    return floatValue <= CURRENCY_MAX;
  }

  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <NumericFormat
        allowNegative={false}
        autoFocus={autoFocus}
        className={classNames(baseInputClassName, "appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none", inputClassName)}
        decimalScale={2}
        decimalSeparator=","
        disabled={disabled}
        fixedDecimalScale
        id={fieldId(name)}
        isAllowed={isAllowed}
        name={name}
        onBlur={onBlur}
        onValueChange={handleValueChange}
        placeholder={placeholder ?? "R$ 0,00"}
        prefix="R$ "
        readOnly={readOnly}
        thousandSeparator="."
        value={value ?? 0}
        {...fieldControlProps(name, error, helpText)}
      />
    </FieldShell>
  );
}
