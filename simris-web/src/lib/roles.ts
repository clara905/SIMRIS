import type { RoleName, User } from '@/types';

export const ROLE_LABEL: Record<RoleName, string> = {
  super_admin: 'Super Admin',
  admin_satker: 'Admin Satker',
  kasatker: 'Kasatker',
  kabid: 'Kabid',
  kasubbid: 'Kasubbid',
  staf: 'Staf',
};

const ROLE_PRIORITY: RoleName[] = [
  'super_admin',
  'admin_satker',
  'kasatker',
  'kabid',
  'kasubbid',
  'staf',
];

export function getPrimaryRole(user: User | null): RoleName | null {
  if (!user) return null;
  return ROLE_PRIORITY.find((r) => user.roles.includes(r)) ?? null;
}

/** Teks cakupan data yang sedang dilihat user (ditampilkan di navbar & dashboard). */
export function scopeLabel(user: User | null): string {
  const role = getPrimaryRole(user);
  if (!user || !role) return '';
  switch (role) {
    case 'super_admin':
      return 'Seluruh Satker';
    case 'kabid':
      return user.bidang?.nama ?? user.satker?.nama ?? '';
    case 'kasubbid':
    case 'staf':
      return user.sub_bidang?.nama ?? user.bidang?.nama ?? '';
    default:
      return user.satker?.nama ?? '';
  }
}