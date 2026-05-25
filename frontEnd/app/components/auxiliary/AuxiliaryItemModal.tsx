import { useEffect, useMemo, useState, type FormEvent } from "react";

import { AppModal } from "../modals/AppModal";
import { ApiRequestError } from "../../Infrastructure/Api/client";
import {
  createCatalogItem,
  updateCatalogItem,
  type AuxiliaryCatalogItem,
  type AuxiliaryCatalogType,
} from "../../Infrastructure/Api/catalogs";
import {
  FieldTypeEnum,
  FieldsAttribute,
  FieldsForm,
  validateFieldValues,
  type FieldDefinition,
  type MessageBag,
} from "../../Infrastructure/DTO/EntityAttributes";

type AuxiliaryItemValues = {
  name: string;
};

type AuxiliaryItemModalProps = {
  item?: AuxiliaryCatalogItem | null;
  onClose: () => void;
  onSaved?: () => Promise<void> | void;
  type: AuxiliaryCatalogType | null;
};

const catalogTitles: Record<AuxiliaryCatalogType, string> = {
  entryType: "tipo de entrada",
  expenseType: "tipo de despesa",
  paymentMethod: "método de pagamento",
};

export const catalogFieldText: Record<
  AuxiliaryCatalogType,
  {
    buttonLabel: string;
    helpText: string;
    label: string;
    placeholder: string;
    submitLabel: string;
  }
> = {
  entryType: {
    buttonLabel: "Adicionar tipo de entrada",
    helpText: "Use um nome claro para identificar a origem da entrada.",
    label: "Nome do tipo de entrada",
    placeholder: "Ex.: Salário, Bônus, Reembolso",
    submitLabel: "Salvar tipo de entrada",
  },
  expenseType: {
    buttonLabel: "Adicionar tipo de despesa",
    helpText: "Use um nome que represente a categoria do gasto.",
    label: "Nome do tipo de despesa",
    placeholder: "Ex.: Mercado, Transporte, Saúde",
    submitLabel: "Salvar tipo de despesa",
  },
  paymentMethod: {
    buttonLabel: "Adicionar método de pagamento",
    helpText: "Use o nome do meio usado para pagar despesas.",
    label: "Nome do método de pagamento",
    placeholder: "Ex.: PIX, Crédito, Débito",
    submitLabel: "Salvar método de pagamento",
  },
};

function buildFields(): FieldDefinition[] {
  return new FieldsAttribute()
    .setNameField("name", "getName", FieldTypeEnum.NAMEFIELD, true, (value) => {
      if (typeof value !== "string" || value.trim().length < 2) {
        return "Informe ao menos 2 caracteres";
      }

      return null;
    })
    .getFields();
}

function apiErrorMessage(error: unknown, fallback: string): string {
  return error instanceof ApiRequestError ? error.message : fallback;
}

export function AuxiliaryItemModal({ item, onClose, onSaved, type }: AuxiliaryItemModalProps) {
  const fields = useMemo(buildFields, []);
  const [values, setValues] = useState<AuxiliaryItemValues>({ name: "" });
  const [messages, setMessages] = useState<MessageBag<keyof AuxiliaryItemValues>>({});
  const [submitMessage, setSubmitMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const isEditing = Boolean(item);

  useEffect(() => {
    if (!type) {
      return;
    }

    setValues({ name: item?.name ?? "" });
    setMessages({});
    setSubmitMessage(null);
    setIsSubmitting(false);
  }, [item, type]);

  if (!type) {
    return null;
  }

  const activeType = type;
  const activeText = catalogFieldText[activeType];
  const labels: Record<keyof AuxiliaryItemValues, string> = {
    name: activeText.label,
  };
  const placeholders: Record<keyof AuxiliaryItemValues, string> = {
    name: activeText.placeholder,
  };
  const helpTexts: Record<keyof AuxiliaryItemValues, string> = {
    name: activeText.helpText,
  };

  function handleFieldChange(name: string, value: unknown) {
    setValues((currentValues) => ({
      ...currentValues,
      [name]: typeof value === "string" ? value : "",
    }));
    setMessages((currentMessages) => ({
      ...currentMessages,
      [name]: undefined,
    }));
    setSubmitMessage(null);
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const nextMessages = validateFieldValues(fields, values);
    setMessages(nextMessages);

    if (Object.values(nextMessages).some(Boolean)) {
      return;
    }

    setIsSubmitting(true);
    setSubmitMessage(null);

    try {
      const payload = { name: values.name.trim() };

      if (isEditing) {
        await updateCatalogItem(activeType, item?.id as number, payload);
      } else {
        await createCatalogItem(activeType, payload);
      }

      await onSaved?.();
      onClose();
    } catch (error) {
      setSubmitMessage(apiErrorMessage(error, "Não foi possível salvar o item auxiliar."));
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <AppModal
      description={isEditing ? `Edite os dados de ${catalogTitles[type]}` : `Adicione um novo ${catalogTitles[type]}`}
      onClose={onClose}
      title={isEditing ? `Editar ${catalogTitles[type]}` : `Adicionar ${catalogTitles[type]}`}
      titleId="auxiliary-item-modal-title"
    >
      {submitMessage && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
          {submitMessage}
        </div>
      )}

      <FieldsForm
        className="space-y-5"
        fieldInputClassName="border-slate-300 focus:border-blue-700 focus:ring-blue-700/20 dark:border-slate-700 dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
        fields={fields}
        fieldsFrameClassName="grid gap-4"
        labels={labels}
        helpTexts={helpTexts}
        messages={messages}
        onFieldChange={handleFieldChange}
        onSubmit={handleSubmit}
        placeholders={placeholders}
        values={values}
      >
        <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <button className="btn-entrar btn-entrar--outlined" disabled={isSubmitting} onClick={onClose} type="button">
            <span>Cancelar</span>
          </button>
          <button className="btn-entrar disabled:cursor-not-allowed disabled:opacity-60" disabled={isSubmitting} type="submit">
            <span>{isSubmitting ? "Salvando..." : isEditing ? "Salvar alterações" : activeText.submitLabel}</span>
          </button>
        </div>
      </FieldsForm>
    </AppModal>
  );
}
