import { useEffect } from 'react';
import { createBrowserRouter, Link, RouterProvider } from 'react-router-dom';
import { fetchMe } from '@/api/auth';
import AppLayout from '@/components/layout/AppLayout';
import LoginPage from '@/features/auth/LoginPage';
import AssetsPage from '@/features/aset/AssetsPage';
import DashboardPage from '@/pages/DashboardPage';
import PlaceholderPage from '@/pages/PlaceholderPage';
import { GuestRoute, ProtectedRoute } from '@/routes/ProtectedRoute';
import { useAuth } from '@/stores/auth';

function Message({ title, text }: { title: string; text: string }) {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center gap-2 p-6 text-center">
      <h1 className="text-3xl font-semibold">{title}</h1>
      <p className="text-muted-foreground">{text}</p>
      <Link to="/" className="text-sm underline">Kembali ke dashboard</Link>
    </div>
  );
}

const router = createBrowserRouter([
  { path: '/login', element: <GuestRoute><LoginPage /></GuestRoute> },
  {
    element: <ProtectedRoute />,
    children: [
      {
        element: <AppLayout />,
        children: [
          { index: true, element: <DashboardPage /> },
          { element: <ProtectedRoute permission="asset.view" />, children: [{ path: 'aset', element: <AssetsPage /> }] },
          { element: <ProtectedRoute permission="risk.view" />, children: [{ path: 'risiko', element: <PlaceholderPage title="Penilaian Risiko" /> }] },
          { element: <ProtectedRoute permission="mitigation.view" />, children: [{ path: 'mitigasi', element: <PlaceholderPage title="Mitigasi" /> }] },
          { element: <ProtectedRoute permission="risk.approve" />, children: [{ path: 'approval', element: <PlaceholderPage title="Approval" /> }] },
          { element: <ProtectedRoute permission="report.view" />, children: [{ path: 'laporan', element: <PlaceholderPage title="Laporan" /> }] },
          { element: <ProtectedRoute permission="sop.manage" />, children: [{ path: 'sop', element: <PlaceholderPage title="SOP" /> }] },
          { element: <ProtectedRoute permission="user.view" />, children: [{ path: 'users', element: <PlaceholderPage title="Manajemen User" /> }] },
          { element: <ProtectedRoute permission="master.manage" />, children: [{ path: 'master', element: <PlaceholderPage title="Master Data" /> }] },
        ],
      },
    ],
  },
  { path: '/403', element: <Message title="403" text="Kamu tidak punya akses ke halaman ini." /> },
  { path: '*', element: <Message title="404" text="Halaman tidak ditemukan." /> },
]);

export default function App() {
  const setUser = useAuth((s) => s.setUser);

  // Cek sesi saat aplikasi dibuka
  useEffect(() => {
    let cancelled = false;
    fetchMe()
      .then((u) => !cancelled && setUser(u))
      .catch(() => !cancelled && setUser(null));
    return () => {
      cancelled = true;
    };
  }, [setUser]);

  return <RouterProvider router={router} />;
}