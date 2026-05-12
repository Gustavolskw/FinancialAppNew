export function emailValidationMessage(value: unknown): string | null {
  if (typeof value !== "string" || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
    return "Informe um email válido";
  }

  return null;
}

export function loginPasswordValidationMessage(value: unknown): string | null {
  if (typeof value !== "string" || value.length < 6) {
    return "A senha deve ter pelo menos 6 caracteres";
  }

  return null;
}

export function strongPasswordValidationMessage(value: unknown): string | null {
  if (typeof value !== "string" || !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\s])\S{6,}$/.test(value)) {
    return "Use 6+ caracteres com maiúscula, minúscula, número e especial";
  }

  return null;
}
