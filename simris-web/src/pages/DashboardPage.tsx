import { getPrimaryRole } from '@/lib/roles';
import { useAuth } from '@/stores/auth';
import GeneralDashboard from './GeneralDashboard';
import StafDashboard from './StafDashboard';

export default function DashboardPage() {
  const user = useAuth((s) => s.user);
  // Staf punya dashboard khusus; role lain memakai dashboard monitoring
  // (cakupan data dibatasi backend: seluruh satker / satker / bidang / sub-bidang).
  return getPrimaryRole(user) === 'staf' ? <StafDashboard /> : <GeneralDashboard />;
}