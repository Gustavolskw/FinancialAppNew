import { useEffect, useMemo, useState, type FormEvent } from "react";

import { AppModal } from "../modals/AppModal";
import { ApiRequestError } from "../../Infrastructure/Api/client";
import type { DashboardTransaction } from "../../Infrastructure/Api/dashboard";
import {
  createEntry,
  createExpense,
  updateEntry,
  updateExpense,
  type TransactionType,
} from "../../Infrastructure/Api/movements";
import {
  FieldsAttribute,
  FieldsForm,
  FieldTypeEnum,
  validateFieldValues,
  type FieldDefinition,
  type FieldOption,
  type MessageBag,
} from "../../Infrastructure/DTO/EntityAttributes";

type MovementValues = {
  amount: number | null;
  description: string;
  location: string;
  date: string;
  category: string | number | Array<string | number> | null;
  paymentMethod: string | number | Array<string | number> | null;
  installments: number | null;
};

type MovementErrors = MessageBag<keyof MovementValues>;

type MovementModalProps = {
  type: TransactionType | null;
  mode?: "create" | "edit";
  transaction?: DashboardTransaction | null;
  walletId?: number | null;
  entryTypes: FieldOption[];
  expenseTypes: FieldOption[];
  paymentMethods: FieldOption[];
  onClose: () => void;
  onSaved?: () => Promise<void> | void;
};

const movementInitialValues: MovementValues = {
  amount: null,
  category: null,
  date: "",
  description: "",
  installments: 1,
  location: "",
  paymentMethod: null,
};

const movementLabels: Record<keyof MovementValues, string> = {
  amount: "Valor",
  category: "Categoria",
  date: "Data",
  description: "Descrição",
  installments: "Parcelas",
  location: "Local",
  paymentMethod: "Método de pagamento",
};

const movementPlaceholders: Record<keyof MovementValues, string> = {
  amount: "0,00",
  category: "Selecione",
  date: "",
  description: "Descreva a movimentação",
  installments: "1",
  location: "Ex.: Mercado, banco, cliente",
  paymentMethod: "Selecione",
};

function buildMovementFields(): FieldDefinition[] {
  return new FieldsAttribute()
    .setValueField("amount", "getAmount", FieldTypeEnum.VALUEFIELD, true, (value) => {
      if (typeof value !== "number" || value <= 0) {
        return "Informe um valor maior que zero";
      }

      return null;
    })
    .setTextField("description", "getDescription", FieldTypeEnum.TEXTFIELD, true)
    .setTextField("location", "getLocation", FieldTypeEnum.LOCATIONFIELD, true)
    .setDateField("date", "getDate", FieldTypeEnum.DATEFIELD, true)
    .setOptionsField("category", "getCategory", [], FieldTypeEnum.OPTIONSFIELD, true)
    .setOptionsField("paymentMethod", "getPaymentMethod", [], FieldTypeEnum.OPTIONSFIELD, true)
    .setNumericField("installments", "getInstallments", FieldTypeEnum.NUMERICFIELD, true, (value) => {
      if (typeof value !== "number" || value < 1) {
        return "Informe ao menos 1 parcela";
      }

      return null;
    })
    .getFields();
}

function firstOptionValue(options: FieldOption[]): number | null {
  const value = options[0]?.value;

  return typeof value === "number" ? value : null;
}

function firstValue(value: MovementValues["category"]): string | number | null {
  return Array.isArray(value) ? value[0] ?? null : value;
}

function selectedId(value: MovementValues["category"]): number | null {
  const selected = firstValue(value);

  if (typeof selected === "number") {
    return selected;
  }

  if (typeof selected === "string" && selected.trim() !== "") {
    const parsed = Number(selected);

    return Number.isFinite(parsed) ? parsed : null;
  }

  return null;
}

function backendDateParts(date: string): { backendDate: string; month: number; year: number } | null {
  const parsed = new Date(`${date}T00:00:00`);

  if (Number.isNaN(parsed.getTime())) {
    return null;
  }

  return {
    backendDate: `${date}T00:00:00-03:00`,
    month: parsed.getMonth() + 1,
    year: parsed.getFullYear(),
  };
}

function dateInputValue(date: string): string {
  if (!date) {
    return "";
  }

  const brDateMatch = /^(\d{2})\/(\d{2})\/(\d{4})/.exec(date);
  if (brDateMatch) {
    const [, day, month, year] = brDateMatch;

    return `${year}-${month}-${day}`;
  }

  return date.slice(0, 10);
}

function movementValuesFromTransaction(transaction: DashboardTransaction | null | undefined): MovementValues {
  if (!transaction) {
    return movementInitialValues;
  }

  return {
    amount: transaction.amount,
    category: transaction.categoryId,
    date: dateInputValue(transaction.date),
    description: transaction.description,
    installments: transaction.installments ?? 1,
    location: transaction.location,
    paymentMethod: transaction.paymentMethodId,
  };
}

function apiErrorMessage(error: unknown, fallback: string): string {
  return error instanceof ApiRequestError ? error.message : fallback;
}

export function MovementModal({
  type,
  mode = "create",
  transaction,
  walletId,
  entryTypes,
  expenseTypes,
  paymentMethods,
  onClose,
  onSaved,
}: MovementModalProps) {
  const movementFields = useMemo(buildMovementFields, []);
  const [movementValues, setMovementValues] = useState<MovementValues>(movementInitialValues);
  const [movementErrors, setMovementErrors] = useState<MovementErrors>({});
  const [submitMessage, setSubmitMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const activeMovementFields = useMemo(
    () => movementFields.filter((field) => {
      if (type === "entry") {
        return field.name !== "paymentMethod" && field.name !== "installments";
      }

      return true;
    }),
    [type, movementFields],
  );

  useEffect(() => {
    if (!type) {
      return;
    }

    if (mode === "edit" && transaction) {
      setMovementValues(movementValuesFromTransaction(transaction));
    } else {
      setMovementValues({
        ...movementInitialValues,
        category: type === "entry" ? firstOptionValue(entryTypes) : firstOptionValue(expenseTypes),
        paymentMethod: type === "expense" ? firstOptionValue(paymentMethods) : null,
      });
    }

    setMovementErrors({});
    setSubmitMessage(null);
    setIsSubmitting(false);
  }, [type, mode, transaction, entryTypes, expenseTypes, paymentMethods]);

  if (!type) {
    return null;
  }

  function handleMovementChange(name: string, value: unknown) {
    setMovementValues((currentValues) => ({
      ...currentValues,
      [name]: value as MovementValues[keyof MovementValues],
    }));

    setMovementErrors((currentErrors) => ({
      ...currentErrors,
      [name]: undefined,
    }));
    setSubmitMessage(null);
  }

  async function handleMovementSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const nextErrors = validateFieldValues(activeMovementFields, movementValues);
    setMovementErrors(nextErrors);

    if (Object.values(nextErrors).some(Boolean)) {
      return;
    }

    const categoryId = selectedId(movementValues.category);
    const paymentMethodId = selectedId(movementValues.paymentMethod);
    const dateParts = backendDateParts(movementValues.date);

    if (!walletId) {
      setSubmitMessage("Selecione uma carteira antes de cadastrar movimentações.");
      return;
    }

    if (!categoryId) {
      setSubmitMessage("Selecione uma categoria válida.");
      return;
    }

    if (type === "expense" && !paymentMethodId) {
      setSubmitMessage("Selecione um método de pagamento válido.");
      return;
    }

    if (dateParts === null) {
      setSubmitMessage("Informe uma data válida.");
      return;
    }

    const basePayload = {
      amount: String(movementValues.amount ?? 0),
      date: dateParts.backendDate,
      description: movementValues.description.trim(),
      location: movementValues.location.trim(),
      month: dateParts.month,
      walletId,
      year: dateParts.year,
    };

    setIsSubmitting(true);
    setSubmitMessage(null);

    let keepModalOpen = true;

    try {
      if (type === "entry") {
        const entryPayload = {
          ...basePayload,
          entryTypeId: categoryId,
        };

        if (mode === "edit") {
          if (!transaction?.resourceId) {
            throw new Error("Entrada sem identificador para edição.");
          }

          await updateEntry({
            ...entryPayload,
            id: transaction.resourceId,
          });
        } else {
          await createEntry(entryPayload);
        }
      } else {
        const expensePayload = {
          ...basePayload,
          expenseTypeId: categoryId,
          installments: movementValues.installments ?? 1,
          paymentMethodId: paymentMethodId as number,
        };

        if (mode === "edit") {
          if (!transaction?.resourceId) {
            throw new Error("Despesa sem identificador para edição.");
          }

          await updateExpense({
            ...expensePayload,
            id: transaction.resourceId,
          });
        } else {
          await createExpense(expensePayload);
        }
      }

      await onSaved?.();
      keepModalOpen = false;
      onClose();
    } catch (error) {
      setSubmitMessage(apiErrorMessage(error, "Não foi possível salvar. Revise os dados e tente novamente."));
    } finally {
      if (keepModalOpen) {
        setIsSubmitting(false);
      }
    }
  }

  return (
    <AppModal
      description={mode === "edit" ? "Edite os dados da movimentação" : "Preencha os dados da nova movimentação"}
      onClose={onClose}
      title={mode === "edit" ? (type === "entry" ? "Editar entrada" : "Editar despesa") : (type === "entry" ? "Cadastrar entrada" : "Cadastrar despesa")}
      titleId="movement-modal-title"
    >
      {submitMessage && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
          {submitMessage}
        </div>
      )}

      <FieldsForm
        className="space-y-5"
        fieldInputClassName="border-slate-300 focus:border-blue-700 focus:ring-blue-700/20 dark:border-slate-700 dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
        fieldOptions={{
          category: type === "entry" ? entryTypes : expenseTypes,
          paymentMethod: type === "entry" ? [] : paymentMethods,
        }}
        fields={activeMovementFields}
        fieldsFrameClassName="grid gap-4"
        labels={movementLabels}
        messages={movementErrors}
        onFieldChange={handleMovementChange}
        onSubmit={handleMovementSubmit}
        placeholders={movementPlaceholders}
        values={movementValues}
      >
        <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <button className="btn-entrar btn-entrar--outlined" disabled={isSubmitting} onClick={onClose} type="button">
            <span>Cancelar</span>
          </button>
          <button className="btn-entrar disabled:cursor-not-allowed disabled:opacity-60" disabled={isSubmitting} type="submit">
            <span>{isSubmitting ? "Salvando..." : mode === "edit" ? "Salvar alterações" : type === "entry" ? "Salvar entrada" : "Salvar despesa"}</span>
          </button>
        </div>
      </FieldsForm>
    </AppModal>
  );
}
