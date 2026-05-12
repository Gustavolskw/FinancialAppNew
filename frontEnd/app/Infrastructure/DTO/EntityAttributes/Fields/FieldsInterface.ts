import type { FieldTypeEnum } from "../FieldTypeEnum";

export type FieldValue = string | number | boolean | Date | string[] | number[] | null;

export type FieldOptionValue = string | number | boolean;

export type FieldOption<TValue extends FieldOptionValue = FieldOptionValue> = {
  value: TValue;
  label: string;
  disabled?: boolean;
};

export type AdditionalFieldValidation<TValue = unknown> = (
  value: TValue,
  field: FieldDefinition,
) => string | null | undefined;

export type FieldDefinition<TValue = unknown> = {
  name: string;
  fieldType: FieldTypeEnum;
  entityGetter: string;
  tableName?: string;
  enumClass?: string;
  relationalEntityClass?: string;
  required: boolean;
  options?: FieldOption[];
  additionalFieldValidation?: AdditionalFieldValidation<TValue>;
};

export type FieldChangeHandler<TValue = unknown> = (value: TValue) => void;

export type MessageBag<TFieldName extends string = string> = Partial<Record<TFieldName, string>>;

export type FieldComponentProps<TValue = unknown> = {
  name: string;
  label?: string;
  value?: TValue;
  onChange: FieldChangeHandler<TValue>;
  required?: boolean;
  error?: string;
  disabled?: boolean;
  readOnly?: boolean;
  placeholder?: string;
  helpText?: string;
  className?: string;
  inputClassName?: string;
  autoFocus?: boolean;
  field?: FieldDefinition<TValue>;
  onBlur?: () => void;
};
