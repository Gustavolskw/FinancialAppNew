import { useEffect, useMemo, useState, type FormEvent, type ReactNode } from "react";

import { classNames, FieldToastMessageBag } from "./Fields";
import { FieldRenderer } from "./FieldsAttribute";
import type { FieldDefinition, FieldOption, MessageBag } from "./Fields/FieldsInterface";

type FieldName<TValues> = Extract<keyof TValues, string>;

type FieldTextMap<TValues> = Partial<Record<FieldName<TValues>, string>>;
type FieldOptionsMap<TValues> = Partial<Record<FieldName<TValues>, FieldOption[]>>;

type FieldClassNameResolver<TValues> = (field: FieldDefinition, fieldName: FieldName<TValues>) => string | undefined;

export type FieldsFormProps<TValues extends Record<string, unknown>> = {
  fields: FieldDefinition[];
  values: TValues;
  messages?: MessageBag<FieldName<TValues>>;
  labels?: FieldTextMap<TValues>;
  placeholders?: FieldTextMap<TValues>;
  helpTexts?: FieldTextMap<TValues>;
  fieldOptions?: FieldOptionsMap<TValues>;
  onFieldChange: (name: FieldName<TValues>, value: unknown) => void;
  onSubmit: (event: FormEvent<HTMLFormElement>) => void;
  children?: ReactNode;
  className?: string;
  fieldsFrameClassName?: string;
  fieldClassName?: string;
  fieldInputClassName?: string;
  messageBagTitle?: string;
  messageBagClassName?: string;
  showMessageToast?: boolean;
  disabled?: boolean;
  readOnly?: boolean;
  getFieldClassName?: FieldClassNameResolver<TValues>;
  getFieldInputClassName?: FieldClassNameResolver<TValues>;
};

export function FieldsForm<TValues extends Record<string, unknown>>({
  fields,
  values,
  messages = {},
  labels = {},
  placeholders = {},
  helpTexts = {},
  fieldOptions = {},
  onFieldChange,
  onSubmit,
  children,
  className,
  fieldsFrameClassName,
  fieldClassName,
  fieldInputClassName,
  messageBagTitle,
  messageBagClassName,
  showMessageToast = true,
  disabled = false,
  readOnly = false,
  getFieldClassName,
  getFieldInputClassName,
}: FieldsFormProps<TValues>) {
  const hasMessages = useMemo(() => Object.values(messages).some(Boolean), [messages]);
  const [toastVisible, setToastVisible] = useState(hasMessages);

  useEffect(() => {
    setToastVisible(hasMessages);
  }, [hasMessages, messages]);

  return (
    <form className={className} noValidate onSubmit={onSubmit}>
      {showMessageToast && toastVisible && (
        <FieldToastMessageBag
          className={messageBagClassName}
          labels={labels as Record<string, string>}
          messages={messages}
          onDismiss={() => setToastVisible(false)}
          title={messageBagTitle}
        />
      )}

      <div className={fieldsFrameClassName}>
        {fields.map((field) => {
          const fieldName = field.name as FieldName<TValues>;

          return (
            <FieldRenderer
              className={classNames(fieldClassName, getFieldClassName?.(field, fieldName))}
              disabled={disabled}
              error={messages[fieldName]}
              field={field}
              helpText={helpTexts[fieldName]}
              inputClassName={classNames(fieldInputClassName, getFieldInputClassName?.(field, fieldName))}
              key={field.name}
              label={labels[fieldName]}
              onChange={(name, value) => onFieldChange(name as FieldName<TValues>, value)}
              options={fieldOptions[fieldName]}
              placeholder={placeholders[fieldName]}
              readOnly={readOnly}
              value={values[fieldName]}
            />
          );
        })}
      </div>

      {children}
    </form>
  );
}
