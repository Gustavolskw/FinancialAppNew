import { useState, useEffect } from "react";
import { useNavigate } from "react-router";
import { UserProfileModal } from "../components/user/UserProfileModal";
import { readAuthSession, saveAuthSession } from "../Infrastructure/Auth/session";
import type { AuthUser, AuthSession } from "../Infrastructure/Auth/session";

export default function ProfilePage() {
  const navigate = useNavigate();
  const [session, setSession] = useState<AuthSession | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  useEffect(() => {
    const currentSession = readAuthSession();
    
    if (!currentSession) {
      navigate("/", { replace: true });
      return;
    }

    setSession(currentSession);
    setIsModalOpen(true);
  }, [navigate]);

  const handleSuccess = (updatedUser: Partial<AuthUser>) => {
    if (session) {
      const updatedSession: AuthSession = {
        ...session,
        user: {
          ...session.user,
          ...updatedUser,
        },
      };
      saveAuthSession(updatedSession);
      setSession(updatedSession);
    }
    navigate("/principal");
  };

  const handleClose = () => {
    navigate("/principal");
  };

  if (!session) {
    return null;
  }

  return (
    <UserProfileModal
      isOpen={isModalOpen}
      onClose={handleClose}
      onSuccess={handleSuccess}
      user={session.user}
    />
  );
}
