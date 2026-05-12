import { useEffect, useState } from "react";
import { useNavigate } from "react-router";

import { isAuthenticated } from "./session";

export type AuthGuardStatus = "checking" | "authenticated";

export function useRequireAuth(): AuthGuardStatus {
  const navigate = useNavigate();
  const [status, setStatus] = useState<AuthGuardStatus>("checking");

  useEffect(() => {
    if (!isAuthenticated()) {
      navigate("/", { replace: true });
      return;
    }

    setStatus("authenticated");
  }, [navigate]);

  return status;
}
