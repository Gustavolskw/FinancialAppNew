import { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router";

import type { Route } from "./+types/login";
import { AuthFormHeader, AuthHeroPanel, AuthPageLayout } from "../components/auth/AuthPageLayout";
import { LoginHeroSummary } from "../components/auth/AuthHeroContent";
import { emailValidationMessage, loginPasswordValidationMessage } from "../components/auth/authValidation";
import { FormStatusMessage } from "../components/feedback/FormStatusMessage";
import { login as loginRequest } from "../Infrastructure/Api/auth";
import { ApiRequestError } from "../Infrastructure/Api/client";
import { isAuthenticated, saveAuthSession } from "../Infrastructure/Auth/session";
import {
  FieldsForm,
  FieldsAttribute,
  FieldTypeEnum,
  validateFieldValues,
  type FieldDefinition,
  type MessageBag,
} from "../Infrastructure/DTO/EntityAttributes";

type LoginValues = {
  email: string;
  password: string;
};

type LoginErrors = MessageBag<keyof LoginValues>;
type LoginLocationState = {
  successMessage?: string;
};

const initialValues: LoginValues = {
  email: "",
  password: "",
};

const fieldLabels: Record<keyof LoginValues, string> = {
  email: "Email",
  password: "Senha",
};

function buildLoginFields(): FieldDefinition[] {
  return new FieldsAttribute()
    .setTextField("email", "getEmail", FieldTypeEnum.EMAILFIELD, true, emailValidationMessage)
    .setPassword("password", "getPassword", FieldTypeEnum.PASSWORDFIELD, true, loginPasswordValidationMessage)
    .getFields();
}

export function meta({}: Route.MetaArgs) {
  return [
    { title: "Entrar | AppFinanças" },
    { name: "description", content: "Acesse sua carteira, entradas e despesas no AppFinanças." },
  ];
}

export default function Login() {
  const navigate = useNavigate();
  const location = useLocation();
  const locationState = location.state as LoginLocationState | null;
  const fields = useMemo(buildLoginFields, []);
  const [values, setValues] = useState<LoginValues>(initialValues);
  const [errors, setErrors] = useState<LoginErrors>({});
  const [formMessage, setFormMessage] = useState<string | null>(locationState?.successMessage ?? null);
  const [formMessageType, setFormMessageType] = useState<"success" | "error">(
    locationState?.successMessage ? "success" : "error",
  );
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (isAuthenticated()) {
      navigate("/principal", { replace: true });
    }
  }, [navigate]);

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

  function validateForm(): LoginErrors {
    return validateFieldValues(fields, values);
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
      const session = await loginRequest(values);
      saveAuthSession(session);
      navigate("/principal", { replace: true });
    } catch (error) {
      setFormMessageType("error");
      setFormMessage(error instanceof ApiRequestError ? error.message : "Não foi possível realizar o login.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <AuthPageLayout
      hero={(
        <AuthHeroPanel
          eyebrow="Visão consolidada"
          footer="Acompanhe categorias, métodos de pagamento e movimentações sem perder a referência da carteira principal."
          title="Seu dinheiro organizado do recebimento ao pagamento."
          topBadge="2026"
          topLabel="Carteiras e transações"
        >
          <LoginHeroSummary />
        </AuthHeroPanel>
      )}
    >
      <AuthFormHeader
        description="Acesse suas carteiras, entradas e despesas em um painel organizado para decisões rápidas."
        eyebrow="AppFinanças"
        icon={(
          <svg aria-hidden="true" className="h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path
              d="M5 17.5V7.75C5 6.78 5.78 6 6.75 6h10.5C18.22 6 19 6.78 19 7.75v8.5c0 .97-.78 1.75-1.75 1.75H7.3L5 20.25V17.5Z"
              stroke="currentColor"
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="1.8"
            />
            <path d="M8.5 14.5h1.75l2-5 1.7 3.25H16" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
          </svg>
        )}
        subtitle="Controle financeiro pessoal"
        title="Entre na sua conta"
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
                email: "seuemail@exemplo.com",
                password: "Sua senha",
              }}
              values={values}
            >
              <FormStatusMessage message={formMessage} type={formMessageType} />

              <div className="flex flex-col gap-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between dark:text-slate-300">
                <span>Sessão protegida por token JWT</span>
                <a className="font-medium text-blue-700 underline-offset-4 hover:text-blue-800 hover:underline active:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200 dark:active:text-blue-100" href="/login">
                  Esqueci minha senha
                </a>
              </div>

              <button className="btn-entrar focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70 dark:focus:ring-blue-300 dark:focus:ring-offset-slate-900" disabled={isSubmitting} type="submit">
                <span>{isSubmitting ? "Entrando..." : "Entrar"}</span>
              </button>

              <p className="text-center text-sm text-slate-600 dark:text-slate-300">
                Ainda não tem conta?{" "}
                <Link className="font-semibold text-blue-700 underline-offset-4 hover:text-blue-800 hover:underline dark:text-blue-300 dark:hover:text-blue-200" to="/cadastro">
                  Criar cadastro
                </Link>
              </p>
            </FieldsForm>
    </AuthPageLayout>
  );
}
