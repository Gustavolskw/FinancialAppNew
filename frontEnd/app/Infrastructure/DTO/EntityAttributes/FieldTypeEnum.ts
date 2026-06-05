export enum FieldTypeEnum {
  IDFIELD = "IDFIELD",
  NAMEFIELD = "NAMEFIELD",
  TEXTFIELD = "TEXTFIELD",
  EMAILFIELD = "EMAILFIELD",
  LOCATIONFIELD = "LOCATIONFIELD",
  PASSWORDFIELD = "PASSWORDFIELD",
  RELATIONALFIELD = "RELATIONALFIELD",
  OPTIONSFIELD = "OPTIONSFIELD",
  VALUEFIELD = "VALUEFIELD",
  NUMERICFIELD = "NUMERICFIELD",
  ENUMFIELD = "ENUMFIELD",
  DATEFIELD = "DATEFIELD",
  DATETIMEFIELD = "DATETIMEFIELD",
  STATUSFIELD = "STATUSFIELD",
  CURRENCYFIELD = "CURRENCYFIELD",
}

export function getFieldType(fieldType: FieldTypeEnum): string {
  switch (fieldType) {
    case FieldTypeEnum.IDFIELD:
    case FieldTypeEnum.NUMERICFIELD:
    case FieldTypeEnum.ENUMFIELD:
    case FieldTypeEnum.RELATIONALFIELD:
      return "int";
    case FieldTypeEnum.NAMEFIELD:
    case FieldTypeEnum.TEXTFIELD:
    case FieldTypeEnum.EMAILFIELD:
    case FieldTypeEnum.LOCATIONFIELD:
    case FieldTypeEnum.PASSWORDFIELD:
      return "string";
    case FieldTypeEnum.OPTIONSFIELD:
      return "array";
    case FieldTypeEnum.VALUEFIELD:
    case FieldTypeEnum.CURRENCYFIELD:
      return "float";
    case FieldTypeEnum.DATEFIELD:
      return "DateTime";
    case FieldTypeEnum.DATETIMEFIELD:
      return "DateTimeImmutable";
    case FieldTypeEnum.STATUSFIELD:
      return "bool";
  }
}

export function getFieldSizeValidation(fieldType: FieldTypeEnum): number {
  switch (fieldType) {
    case FieldTypeEnum.IDFIELD:
    case FieldTypeEnum.RELATIONALFIELD:
      return 10;
    case FieldTypeEnum.EMAILFIELD:
    case FieldTypeEnum.NAMEFIELD:
      return 100;
    case FieldTypeEnum.TEXTFIELD:
    case FieldTypeEnum.VALUEFIELD:
    case FieldTypeEnum.CURRENCYFIELD:
    case FieldTypeEnum.DATEFIELD:
    case FieldTypeEnum.DATETIMEFIELD:
    case FieldTypeEnum.PASSWORDFIELD:
      return 255;
    case FieldTypeEnum.LOCATIONFIELD:
    case FieldTypeEnum.OPTIONSFIELD:
      return 50;
    case FieldTypeEnum.NUMERICFIELD:
    case FieldTypeEnum.ENUMFIELD:
    case FieldTypeEnum.STATUSFIELD:
      return 5;
  }
}
