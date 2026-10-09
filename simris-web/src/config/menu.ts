import {
  Boxes,
  Building2,
  CheckSquare,
  FileBarChart,
  FileText,
  LayoutDashboard,
  ShieldAlert,
  Users,
  Wrench,
  type LucideIcon,
} from 'lucide-react';

export interface MenuItem {
  label: string;
  to: string;
  icon: LucideIcon;
  /** kosong = semua user login */
  permission?: string;
  end?: boolean;
}

export const MENU: MenuItem[] = [
  { label: 'Dashboard', to: '/', icon: LayoutDashboard, end: true },
  { label: 'Aset', to: '/aset', icon: Boxes, permission: 'asset.view' },
  { label: 'Penilaian Risiko', to: '/risiko', icon: ShieldAlert, permission: 'risk.view' },
  { label: 'Mitigasi', to: '/mitigasi', icon: Wrench, permission: 'mitigation.view' },
  { label: 'Approval', to: '/approval', icon: CheckSquare, permission: 'risk.approve' },
  { label: 'Laporan', to: '/laporan', icon: FileBarChart, permission: 'report.view' },
  { label: 'SOP', to: '/sop', icon: FileText, permission: 'sop.manage' },
  { label: 'Manajemen User', to: '/users', icon: Users, permission: 'user.view' },
  { label: 'Master Data', to: '/master', icon: Building2, permission: 'master.manage' },
];