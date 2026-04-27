<?php

namespace App\Infrastructure\DTO\EntityAttributes;

enum FieldTypeEnum
{
    case IDFIELD;
    case NAMEFIELD;
    case TEXTFIELD;
    case EMAILFIELD;
    case LOCATIONFIELD;
    case PASSWORDFIELD;
    case RELATIONALFIELD;
    case OPTIONSFIELD;
    case VALUEFIELD;
    case NUMERICFIELD;
    case ENUMFIELD;
    case DATEFIELD;
    case DATETIMEFIELD;
    case STATUSFIELD;

    public function getFieldType(): string
    {
        return match ($this) {
            FieldTypeEnum::IDFIELD,
            FieldTypeEnum::NUMERICFIELD,
            FieldTypeEnum::ENUMFIELD,
            FieldTypeEnum::RELATIONALFIELD => "int",

            FieldTypeEnum::NAMEFIELD,
            FieldTypeEnum::TEXTFIELD,
            FieldTypeEnum::EMAILFIELD,
            FieldTypeEnum::LOCATIONFIELD,
            FieldTypeEnum::PASSWORDFIELD => "string",

            FieldTypeEnum::OPTIONSFIELD => "array",
            FieldTypeEnum::VALUEFIELD => "float",
            FieldTypeEnum::DATEFIELD => "DateTime",
            FieldTypeEnum::DATETIMEFIELD => "DateTimeImmutable",
            FieldTypeEnum::STATUSFIELD => "bool",
        };
    }

    public function getFieldSizeValidation(): int
    {
        return match ($this) {
            FieldTypeEnum::IDFIELD,
            FieldTypeEnum::RELATIONALFIELD => 10,

            FieldTypeEnum::EMAILFIELD,
            FieldTypeEnum::NAMEFIELD => 100,

            FieldTypeEnum::TEXTFIELD,
            FieldTypeEnum::VALUEFIELD,
            FieldTypeEnum::DATEFIELD,
            FieldTypeEnum::DATETIMEFIELD,
            FieldTypeEnum::PASSWORDFIELD => 255,

            FieldTypeEnum::LOCATIONFIELD,
            FieldTypeEnum::OPTIONSFIELD => 50,
            FieldTypeEnum::NUMERICFIELD,
            FieldTypeEnum::ENUMFIELD,
            FieldTypeEnum::STATUSFIELD => 5,
        };
    }
}
