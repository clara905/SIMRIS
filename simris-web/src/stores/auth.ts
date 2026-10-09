import { create } from 'zustand';
import type { RoleName, User } from '@/types';

interface AuthState {
  user: User | null;
  /** false selama sesi awal (/api/me) belum dicek */
  ready: boolean;
  setUser: (user: User | null) => void;
  can: (permission: string) => boolean;
  hasRole: (...roles: RoleName[]) => boolean;
}

export const useAuth = create<AuthState>((set, get) => ({
  user: null,
  ready: false,
  setUser: (user) => set({ user, ready: true }),
  can: (permission) => {
    const u = get().user;
    return !!u && (u.roles.includes('super_admin') || u.permissions.includes(permission));
  },
  hasRole: (...roles) => {
    const u = get().user;
    return !!u && roles.some((r) => u.roles.includes(r));
  },
}));