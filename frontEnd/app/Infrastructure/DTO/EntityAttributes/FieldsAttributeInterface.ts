import type { FieldTypeEnum } from "./FieldTypeEnum";
import type { AdditionalFieldValidation, FieldDefinition, FieldOption } from "./Fields/FieldsInterface";

export interface FieldsAttributeInterface {
  setIdField(
    name: string,
    entityGetter?: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;
  getIdField(): FieldDefinition | null;

  setNameField(
    name: string,
    entityGetter?: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;
  getNameField(): FieldDefinition | null;

  setTextField(
    name: string,
    entityGetter: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setPassword(
    name: string,
    entityGetter?: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setOptionsField(
    name: string,
    entityGetter: string,
    options?: FieldOption[],
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setEnumField(
    name: string,
    entityGetter: string,
    enumClass: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setStatusField(
    name: string,
    entityGetter?: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setNumericField(
    name: string,
    entityGetter: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setDateField(
    name: string,
    entityGetter: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setValueField(
    name: string,
    entityGetter: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  setRelationalField(
    name: string,
    relationalEntityClass: string,
    entityGetter: string,
    fieldType?: FieldTypeEnum,
    required?: boolean,
    additionalFieldValidation?: AdditionalFieldValidation,
  ): this;

  getField(name: string): FieldDefinition | null;
  getStatusField(): FieldDefinition | null;
  getTextField(name: string, fieldType: FieldTypeEnum): FieldDefinition | null;
  getPasswordField(name: string): FieldDefinition | null;
  getOptionsField(name: string): FieldDefinition | null;
  getEnumField(name: string): FieldDefinition | null;
  getNumericField(name: string): FieldDefinition | null;
  getDateField(name: string, fieldType: FieldTypeEnum): FieldDefinition | null;
  getValueField(name: string): FieldDefinition | null;
  getRelationalField(name: string): FieldDefinition | null;
  getFields(): FieldDefinition[];
}
