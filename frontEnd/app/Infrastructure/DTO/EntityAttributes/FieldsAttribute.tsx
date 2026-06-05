import { FieldTypeEnum } from "./FieldTypeEnum";
import {
  BasicFieldDto,
  CurrencyFieldDto,
  DateFieldDto,
  EnumFieldDto,
  IdFieldDto,
  NameFieldDto,
  PasswordFieldDto,
  RelationalAttributeDto,
  StatusFieldDto,
  TextFieldDto,
  validateAdditional,
  validateRequired,
} from "./Fields";
import type { AdditionalFieldValidation, FieldDefinition, FieldOption } from "./Fields";
import type { MessageBag } from "./Fields/FieldsInterface";
import type { FieldsAttributeInterface } from "./FieldsAttributeInterface";

type RenderFieldProps = {
  field: FieldDefinition;
  label?: string;
  value?: unknown;
  onChange: (name: string, value: unknown) => void;
  error?: string;
  disabled?: boolean;
  readOnly?: boolean;
  placeholder?: string;
  helpText?: string;
  className?: string;
  inputClassName?: string;
  options?: FieldOption[];
};

export class FieldsAttribute implements FieldsAttributeInterface {
  private fields = new Map<string, FieldDefinition>();
  private idFieldName: string | null = null;
  private nameFieldName: string | null = null;
  private statusFieldName: string | null = null;

  private put(field: FieldDefinition): this {
    this.fields.set(field.name, field);
    return this;
  }

  private buildField(
    name: string,
    fieldType: FieldTypeEnum,
    entityGetter: string,
    required: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): FieldDefinition {
    return {
      name,
      fieldType,
      entityGetter,
      required,
      additionalFieldValidation,
    };
  }

  private getFieldOfType(name: string, fieldType: FieldTypeEnum): FieldDefinition | null {
    const field = this.getField(name);
    return field?.fieldType === fieldType ? field : null;
  }

  setIdField(
    name: string,
    entityGetter = "getId",
    fieldType = FieldTypeEnum.IDFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    this.idFieldName = name;
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  getIdField(): FieldDefinition | null {
    return this.idFieldName ? this.getField(this.idFieldName) : null;
  }

  setNameField(
    name: string,
    entityGetter = "getName",
    fieldType = FieldTypeEnum.NAMEFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    this.nameFieldName = name;
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  getNameField(): FieldDefinition | null {
    return this.nameFieldName ? this.getField(this.nameFieldName) : null;
  }

  setTextField(
    name: string,
    entityGetter: string,
    fieldType = FieldTypeEnum.TEXTFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  setPassword(
    name: string,
    entityGetter = "getPassword",
    fieldType = FieldTypeEnum.PASSWORDFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  setOptionsField(
    name: string,
    entityGetter: string,
    options: FieldOption[] = [],
    fieldType = FieldTypeEnum.OPTIONSFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put({
      ...this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation),
      options,
    });
  }

  setEnumField(
    name: string,
    entityGetter: string,
    enumClass: string,
    fieldType = FieldTypeEnum.ENUMFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put({
      ...this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation),
      enumClass,
    });
  }

  setNumericField(
    name: string,
    entityGetter: string,
    fieldType = FieldTypeEnum.NUMERICFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  setDateField(
    name: string,
    entityGetter: string,
    fieldType = FieldTypeEnum.DATEFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  setValueField(
    name: string,
    entityGetter: string,
    fieldType = FieldTypeEnum.VALUEFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  setCurrencyField(
    name: string,
    entityGetter: string,
    fieldType = FieldTypeEnum.CURRENCYFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  setRelationalField(
    name: string,
    relationalEntityClass: string,
    entityGetter: string,
    fieldType = FieldTypeEnum.RELATIONALFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    return this.put({
      ...this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation),
      relationalEntityClass,
    });
  }

  setStatusField(
    name: string,
    entityGetter = "isStatus",
    fieldType = FieldTypeEnum.STATUSFIELD,
    required = false,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this {
    this.statusFieldName = name;
    return this.put(this.buildField(name, fieldType, entityGetter, required, additionalFieldValidation));
  }

  getField(name: string): FieldDefinition | null {
    return this.fields.get(name) ?? null;
  }

  getStatusField(): FieldDefinition | null {
    return this.statusFieldName ? this.getField(this.statusFieldName) : null;
  }

  getTextField(name: string, fieldType: FieldTypeEnum): FieldDefinition | null {
    return this.getFieldOfType(name, fieldType);
  }

  getPasswordField(name: string): FieldDefinition | null {
    return this.getFieldOfType(name, FieldTypeEnum.PASSWORDFIELD);
  }

  getOptionsField(name: string): FieldDefinition | null {
    return this.getFieldOfType(name, FieldTypeEnum.OPTIONSFIELD);
  }

  getEnumField(name: string): FieldDefinition | null {
    return this.getFieldOfType(name, FieldTypeEnum.ENUMFIELD);
  }

  getNumericField(name: string): FieldDefinition | null {
    return this.getFieldOfType(name, FieldTypeEnum.NUMERICFIELD);
  }

  getDateField(name: string, fieldType: FieldTypeEnum): FieldDefinition | null {
    return this.getFieldOfType(name, fieldType);
  }

  getValueField(name: string): FieldDefinition | null {
    return this.getFieldOfType(name, FieldTypeEnum.VALUEFIELD);
  }

  getRelationalField(name: string): FieldDefinition | null {
    return this.getFieldOfType(name, FieldTypeEnum.RELATIONALFIELD);
  }

  getFields(): FieldDefinition[] {
    return Array.from(this.fields.values());
  }
}

export function validateFieldValue(field: FieldDefinition, value: unknown): string | null {
  return validateRequired(value, field) ?? validateAdditional(value, field);
}

export function validateFieldValues<TValues extends Record<string, unknown>>(
  fields: FieldDefinition[],
  values: TValues,
): MessageBag<Extract<keyof TValues, string>> {
  return fields.reduce<MessageBag<Extract<keyof TValues, string>>>((messageBag, field) => {
    const fieldName = field.name as Extract<keyof TValues, string>;
    const error = validateFieldValue(field, values[fieldName]);

    if (error) {
      messageBag[fieldName] = error;
    }

    return messageBag;
  }, {});
}

export function FieldRenderer({
  field,
  label,
  value,
  onChange,
  error,
  disabled,
  readOnly,
  placeholder,
  helpText,
  className,
  inputClassName,
  options,
}: RenderFieldProps) {
  const commonProps = {
    name: field.name,
    label,
    required: field.required,
    error,
    disabled,
    readOnly,
    placeholder,
    helpText,
    className,
    inputClassName,
    field,
  };
  const handleChange = (nextValue: unknown) => onChange(field.name, nextValue);

  switch (field.fieldType) {
    case FieldTypeEnum.IDFIELD:
      return <IdFieldDto {...commonProps} onChange={handleChange} value={typeof value === "number" ? value : null} />;
    case FieldTypeEnum.NAMEFIELD:
      return <NameFieldDto {...commonProps} onChange={handleChange} value={typeof value === "string" ? value : ""} />;
    case FieldTypeEnum.EMAILFIELD:
    case FieldTypeEnum.LOCATIONFIELD:
    case FieldTypeEnum.TEXTFIELD:
      return (
        <TextFieldDto
          {...commonProps}
          fieldType={field.fieldType}
          onChange={handleChange}
          value={typeof value === "string" ? value : ""}
        />
      );
    case FieldTypeEnum.PASSWORDFIELD:
      return <PasswordFieldDto {...commonProps} onChange={handleChange} value={typeof value === "string" ? value : ""} />;
    case FieldTypeEnum.OPTIONSFIELD:
      return (
        <BasicFieldDto
          {...commonProps}
          fieldType={field.fieldType}
          onChange={handleChange}
          options={options ?? field.options}
          value={Array.isArray(value) || typeof value === "string" || typeof value === "number" ? value : null}
        />
      );
    case FieldTypeEnum.NUMERICFIELD:
    case FieldTypeEnum.VALUEFIELD:
      return (
        <BasicFieldDto
          {...commonProps}
          fieldType={field.fieldType}
          onChange={handleChange}
          value={typeof value === "number" || typeof value === "string" ? value : null}
        />
      );
    case FieldTypeEnum.CURRENCYFIELD:
      return (
        <CurrencyFieldDto
          {...commonProps}
          onChange={handleChange}
          value={typeof value === "number" ? value : null}
        />
      );
    case FieldTypeEnum.DATEFIELD:
    case FieldTypeEnum.DATETIMEFIELD:
      return (
        <DateFieldDto
          {...commonProps}
          fieldType={field.fieldType}
          onChange={handleChange}
          value={typeof value === "string" || value instanceof Date ? value : null}
        />
      );
    case FieldTypeEnum.ENUMFIELD:
      return (
        <EnumFieldDto
          {...commonProps}
          onChange={handleChange}
          options={(options ?? field.options ?? []).filter((option): option is FieldOption<number> => typeof option.value === "number")}
          value={typeof value === "number" ? value : null}
        />
      );
    case FieldTypeEnum.RELATIONALFIELD:
      return (
        <RelationalAttributeDto
          {...commonProps}
          onChange={handleChange}
          options={(options ?? field.options ?? []).filter((option): option is FieldOption<number> => typeof option.value === "number")}
          value={typeof value === "number" ? value : null}
        />
      );
    case FieldTypeEnum.STATUSFIELD:
      return <StatusFieldDto {...commonProps} onChange={handleChange} value={Boolean(value)} />;
  }
}
