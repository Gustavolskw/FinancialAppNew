import { FieldTypeEnum } from "../FieldTypeEnum";
import { baseInputClassName, classNames, fieldControlProps, FieldShell, fieldId } from "./Field";
import type { FieldComponentProps } from "./FieldsInterface";

type DateFieldDtoProps = FieldComponentProps<string | Date | null> & {
  fieldType?: FieldTypeEnum.DATEFIELD | FieldTypeEnum.DATETIMEFIELD;
};

function inputDateValue(value: string | Date | null | undefined, fieldType: FieldTypeEnum.DATEFIELD | FieldTypeEnum.DATETIMEFIELD): string {
  if (!value) {
    return "";
  }

  if (value instanceof Date) {
    return fieldType === FieldTypeEnum.DATETIMEFIELD
      ? value.toISOString().slice(0, 16)
      : value.toISOString().slice(0, 10);
  }

  return fieldType === FieldTypeEnum.DATETIMEFIELD ? value.slice(0, 16) : value.slice(0, 10);
}

function padDatePart(value: number): string {
  return String(value).padStart(2, "0");
}

function currentInputDateValue(fieldType: FieldTypeEnum.DATEFIELD | FieldTypeEnum.DATETIMEFIELD): string {
  const now = new Date();
  const date = `${now.getFullYear()}-${padDatePart(now.getMonth() + 1)}-${padDatePart(now.getDate())}`;

  if (fieldType === FieldTypeEnum.DATEFIELD) {
    return date;
  }

  return `${date}T${padDatePart(now.getHours())}:${padDatePart(now.getMinutes())}`;
}

export function DateFieldDto({
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
  fieldType = FieldTypeEnum.DATEFIELD,
}: DateFieldDtoProps) {
  const canFillCurrentDate = !disabled && !readOnly;

  return (
    <FieldShell className={className} error={error} helpText={helpText} label={label} name={name} required={required}>
      <div className="flex gap-2">
        <input
          autoFocus={autoFocus}
          className={classNames(baseInputClassName, "min-w-0 flex-1", inputClassName)}
          disabled={disabled}
          id={fieldId(name)}
          name={name}
          {...fieldControlProps(name, error, helpText)}
          onBlur={onBlur}
          onChange={(event) => onChange(event.target.value === "" ? null : event.target.value)}
          placeholder={placeholder}
          readOnly={readOnly}
          type={fieldType === FieldTypeEnum.DATETIMEFIELD ? "datetime-local" : "date"}
          value={inputDateValue(value, fieldType)}
        />
        <button
          className="shrink-0 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-700/20 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200 dark:hover:bg-blue-500/20"
          disabled={!canFillCurrentDate}
          onClick={() => onChange(currentInputDateValue(fieldType))}
          type="button"
        >
          Hoje
        </button>
      </div>
    </FieldShell>
  );
}
