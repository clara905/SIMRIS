import { useQueryClient } from '@tanstack/react-query';
import { ChevronDown, LogOut, Menu } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { logout } from '@/api/auth';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getPrimaryRole, ROLE_LABEL, scopeLabel } from '@/lib/roles';
import { useAuth } from '@/stores/auth';

export function Navbar({ onMenuClick }: { onMenuClick: () => void }) {
  const user = useAuth((s) => s.user);
  const setUser = useAuth((s) => s.setUser);
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const role = getPrimaryRole(user);
  const scope = scopeLabel(user);

  async function handleLogout() {
    try {
      await logout();
    } finally {
      setUser(null);
      queryClient.clear();
      navigate('/login', { replace: true });
    }
  }

  return (
    <header className="sticky top-0 z-20 flex h-14 items-center gap-3 border-b bg-background px-4">
      <Button variant="ghost" size="icon" className="lg:hidden" onClick={onMenuClick} aria-label="Buka menu">
        <Menu className="h-5 w-5" />
      </Button>

      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-medium">{scope}</p>
        {user?.satker && role !== 'super_admin' && (
          <p className="truncate text-xs text-muted-foreground">{user.satker.nama}</p>
        )}
      </div>

      <DropdownMenu>
        <DropdownMenuTrigger
          render={
            <Button variant="ghost" className="gap-2">
              <span className="hidden text-sm sm:inline">{user?.name}</span>
              {role && <Badge variant="secondary">{ROLE_LABEL[role]}</Badge>}
              <ChevronDown className="h-4 w-4" />
            </Button>
          }
        />
        <DropdownMenuContent align="end" className="w-56">
          <DropdownMenuLabel>
            <p className="text-sm font-medium">{user?.name}</p>
            <p className="text-xs font-normal text-muted-foreground">{user?.email}</p>
          </DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuItem onClick={() => void handleLogout()}>
            <LogOut className="mr-2 h-4 w-4" />
            Keluar
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </header>
  );
}