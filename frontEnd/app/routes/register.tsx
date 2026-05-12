import { useMemo, useState } from "react";
import { Link, useNavigate } from "react-router";

import type { Route } from "./+types/register";
import { AuthFormHeader, AuthHeroPanel, AuthPageLayout } from "../components/auth/AuthPageLayout";
import { RegisterHeroSteps } from "../components/auth/AuthHeroContent";
import { emailValidationMessage, strongPasswordValidationMessage } from "../components/auth/authValidation";
import { FormStatusMessage } from "../components/feedback/FormStatusMessage";
import { register as registerRequest } from "../Infrastructure/Api/auth";
import { ApiRequestError } from "../Infrastructure/Api/client";
import {
  FieldsForm,
  FieldsAttribute,
  FieldTypeEnum,
  validateFieldValues,
  type FieldDefinition,
  type MessageBag,
} from "../Infrastructure/DTO/EntityAttributes";

type RegisterValues = {
  name: string;
  email: string;
  password: string;
  passwordConfirmation: string;
};

type RegisterErrors = MessageBag<keyof RegisterValues>;

const initialValues: RegisterValues = {
  name: "",
  email: "",
  password: "",
  passwordConfirmation: "",
};

const fieldLabels: Record<keyof RegisterValues, string> = {
  name: "Nome",
  email: "Email",
  password: "Senha",
  passwordConfirmation: "Confirmar senha",
};

function buildRegisterFields(): FieldDefinition[] {
  return new FieldsAttribute()
    .setNameField("name", "getName", FieldTypeEnum.NAMEFIELD, true)
    .setTextField("email", "getEmail", FieldTypeEnum.EMAILFIELD, true, emailValidationMessage)
    .setPassword("password", "getPassword", FieldTypeEnum.PASSWORDFIELD, true, strongPasswordValidationMessage)
    .setPassword("passwordConfirmation", "getPassword", FieldTypeEnum.PASSWORDFIELD, true)
    .getFields();
}

export function meta({}: Route.MetaArgs) {
  return [
    { title: "Cadastro | AppFinanças" },
    { name: "description", content: "Crie sua conta para organizar carteiras, entradas e despesas." },
  ];
}

export default function Register() {
  const navigate = useNavigate();
  const fields = useMemo(buildRegisterFields, []);
  const [values, setValues] = useState<RegisterValues>(initialValues);
  const [errors, setErrors] = useState<RegisterErrors>({});
  const [formMessage, setFormMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function handleFieldChange(name: string, value: unknown) {
    setValues((currentValues) => ({
      ...currentValues,
      [name]: typeof value === "string" ? value : "",
    }));

    setErrors((currentErrors) => ({
      ...currentErrors,
      [name]: undefined,
    }));
    setFormMessage(null);
  }

  function validateForm(): RegisterErrors {
    const nextErrors = validateFieldValues(fields, values);

    if (values.password && values.passwordConfirmation && values.password !== values.passwordConfirmation) {
      nextErrors.passwordConfirmation = "As senhas precisam ser iguais";
    }

    return nextErrors;
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const nextErrors = validateForm();
    setErrors(nextErrors);

    if (Object.values(nextErrors).some(Boolean)) {
      return;
    }

    setIsSubmitting(true);
    setFormMessage(null);

    try {
      await registerRequest({
        email: values.email,
        name: values.name,
        password: values.password,
      });

      navigate("/", {
        replace: true,
        state: {
          successMessage: "Cadastro realizado com sucesso. Entre com seu email e senha.",
        },
      });
    } catch (error) {
      setFormMessage(error instanceof ApiRequestError ? error.message : "Não foi possível criar a conta.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <AuthPageLayout
      hero={(
        <AuthHeroPanel
          eyebrow="Comece com uma carteira"
          footer="O cadastro usa o fluxo público do backend e mantém permissões de usuário comum por padrão."
          title="Cadastre-se para acompanhar entradas, despesas e métodos de pagamento."
          topBadge="Ativação rápida"
          topLabel="Nova conta"
        >
          <RegisterHeroSteps />
        </AuthHeroPanel>
      )}
    >
      <AuthFormHeader
        description="Informe seus dados para acessar o painel financeiro e começar a organizar sua primeira carteira."
        eyebrow="AppFinanças"
        icon={(
          <svg aria-hidden="true" className="h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
          </svg>
        )}
        subtitle="Cadastro de usuário"
        title="Criar sua conta"
      />

            <FieldsForm
              className="space-y-5 rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
              fieldInputClassName="border-slate-300 focus:border-blue-700 focus:ring-blue-700/20 dark:border-slate-700 dark:focus:border-blue-300 dark:focus:ring-blue-300/20"
              fields={fields}
              fieldsFrameClassName="space-y-5"
              labels={fieldLabels}
              messages={errors}
              onFieldChange={handleFieldChange}
              onSubmit={handleSubmit}
              placeholders={{
                name: "Seu nome",
                email: "seuemail@exemplo.com",
                password: "Crie uma senha",
                passwordConfirmation: "Repita sua senha",
              }}
              values={values}
            >
              <FormStatusMessage message={formMessage} />

              <button className="btn-entrar focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70 dark:focus:ring-blue-300 dark:focus:ring-offset-slate-900" disabled={isSubmitting} type="submit">
                <span>{isSubmitting ? "Criando..." : "Criar conta"}</span>
              </button>

              <p className="text-center text-sm text-slate-600 dark:text-slate-300">
                Já tem conta?{" "}
                <Link className="font-semibold text-blue-700 underline-offset-4 hover:text-blue-800 hover:underline dark:text-blue-300 dark:hover:text-blue-200" to="/">
                  Entrar
                </Link>
              </p>
            </FieldsForm>
    </AuthPageLayout>
  );
}
