import type { ReactNode } from 'react';
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { FullPageLoader } from '@/components/shared/FullPageLoader';
import { useAuth } from '@/stores/auth';
import type { RoleName } from '@/types';

interface Props {
  permission?: string;
  roles?: RoleName[];
}

/** Guard: wajib login, opsional wajib permission/role tertentu. */
export function ProtectedRoute({ permission, roles }: Props) {
  const { user, ready, can, hasRole } = useAuth();
  const location = useLocation();

  if (!ready) return <FullPageLoader />;
  if (!user) return <Navigate to="/login" replace state={{ from: location }} />;
  if ((permission && !can(permission)) || (roles && !hasRole(...roles))) {
    return <Navigate to="/403" replace />;
  }
  return <Outlet />;
}

/** Halaman tamu (login): kalau sudah login, lempar ke dashboard. */
export function GuestRoute({ children }: { children: ReactNode }) {
  const { user, ready } = useAuth();
  if (!ready) return <FullPageLoader />;
  if (user) return <Navigate to="/" replace />;
  return <>{children}</>;
}