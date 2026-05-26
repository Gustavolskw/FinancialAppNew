import { useState } from "react";
import { AppModal } from "../modals/AppModal";
import { FormStatusMessage } from "../feedback/FormStatusMessage";
import { updateUser } from "~/Infrastructure/Api/users";
import type { UserUpdateData } from "~/Infrastructure/Api/users";
import type { AuthUser } from "~/Infrastructure/Auth/session";

type UserProfileModalProps = {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (updatedUser: Partial<AuthUser>) => void;
  user: AuthUser;
};

export function UserProfileModal({ isOpen, onClose, onSuccess, user }: UserProfileModalProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [formData, setFormData] = useState({
    name: user.name ?? "",
    email: user.email ?? "",
    password: "",
    confirmPassword: "",
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);
    setSuccess(null);

    if (formData.password && formData.password !== formData.confirmPassword) {
      setError("As senhas não coincidem");
      setIsLoading(false);
      return;
    }

    try {
      const updateData: UserUpdateData = {
        id: user.id,
      };

      if (formData.name) {
        updateData.name = formData.name;
      }

      if (formData.email) {
        updateData.email = formData.email;
      }

      if (formData.password) {
        updateData.password = formData.password;
      }

      const updatedUser = await updateUser(updateData);
      setSuccess("Perfil atualizado com sucesso!");

      setTimeout(() => {
        onSuccess(updatedUser);
        onClose();
      }, 1500);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erro ao atualizar perfil");
    } finally {
      setIsLoading(false);
    }
  };

  const handleChange = (field: keyof typeof formData) => (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData((prev) => ({ ...prev, [field]: e.target.value }));
    setError(null);
    setSuccess(null);
  };

  if (!isOpen) {
    return null;
  }

  return (
    <AppModal
      title="Editar Perfil"
      description="Atualize suas informações pessoais"
      onClose={onClose}
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        <FormStatusMessage message={error} type="error" />
        <FormStatusMessage message={success} type="success" />

        <div>
          <label htmlFor="name" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Nome
          </label>
          <input
            id="name"
            type="text"
            value={formData.name}
            onChange={handleChange("name")}
            className="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
            disabled={isLoading}
            required
          />
        </div>

        <div>
          <label htmlFor="email" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
            E-mail
          </label>
          <input
            id="email"
            type="email"
            value={formData.email}
            onChange={handleChange("email")}
            className="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
            disabled={isLoading}
            required
          />
        </div>

        <div className="border-t border-slate-200 pt-4 dark:border-slate-700">
          <p className="mb-3 text-sm text-slate-600 dark:text-slate-400">
            Deixe em branco para manter a senha atual
          </p>

          <div className="space-y-3">
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                Nova Senha
              </label>
              <input
                id="password"
                type="password"
                value={formData.password}
                onChange={handleChange("password")}
                className="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                disabled={isLoading}
                placeholder="Mínimo 6 caracteres"
              />
            </div>

            <div>
              <label htmlFor="confirmPassword" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                Confirmar Nova Senha
              </label>
              <input
                id="confirmPassword"
                type="password"
                value={formData.confirmPassword}
                onChange={handleChange("confirmPassword")}
                className="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                disabled={isLoading}
                placeholder="Confirme a nova senha"
              />
            </div>
          </div>
        </div>

        <div className="flex gap-3 pt-2">
          <button
            type="button"
            onClick={onClose}
            className="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            disabled={isLoading}
          >
            Cancelar
          </button>
          <button
            type="submit"
            className="flex-1 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isLoading}
          >
            {isLoading ? "Salvando..." : "Salvar Alterações"}
          </button>
        </div>
      </form>
    </AppModal>
  );
}
