import { useQuery } from '@tanstack/react-query';
import type { ColumnDef } from '@tanstack/react-table';
import { Boxes, ClipboardCheck, ShieldAlert, Wrench } from 'lucide-react';
import { Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { dashboardApi } from '@/api/dashboard';
import { DataTable } from '@/components/shared/DataTable';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatCard } from '@/components/shared/StatCard';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { scopeLabel } from '@/lib/roles';
import { useAuth } from '@/stores/auth';
import type { RiskRow } from '@/types';

const columns: ColumnDef<RiskRow>[] = [
  { accessorKey: 'kode_risiko', header: 'Kode' },
  {
    accessorKey: 'deskripsi_risiko',
    header: 'Risiko',
    cell: ({ row }) => <span className="line-clamp-2 max-w-md">{row.original.deskripsi_risiko}</span>,
  },
  {
    accessorKey: 'level',
    header: 'Level',
    cell: ({ row }) => (
      <span className="inline-flex items-center gap-2">
        <span className="inline-block h-2.5 w-2.5 rounded-full" style={{ background: row.original.warna }} />
        {row.original.level}
      </span>
    ),
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) => (
      <Badge variant="outline" className="capitalize">
        {row.original.status.replace(/_/g, ' ')}
      </Badge>
    ),
  },
  { accessorKey: 'oleh', header: 'Diajukan oleh' },
];

export default function GeneralDashboard() {
  const user = useAuth((s) => s.user);
  const { data, isLoading, isError } = useQuery({
    queryKey: ['dashboard', 'summary'],
    queryFn: dashboardApi.summary,
  });

  if (isError) return <p className="text-sm text-red-600">Gagal memuat dashboard.</p>;
  if (isLoading || !data) return <p className="text-sm text-muted-foreground">Memuat dashboard…</p>;

  return (
    <div className="space-y-6">
      <PageHeader title="Dashboard" description={`Ringkasan manajemen risiko · ${scopeLabel(user)}`} />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Total Aset" value={data.totals.aset} icon={Boxes} />
        <StatCard label="Total Risiko" value={data.totals.risiko} icon={ShieldAlert} />
        <StatCard label="Menunggu Approval" value={data.totals.menunggu_approval} icon={ClipboardCheck} />
        <StatCard label="Mitigasi Berjalan" value={data.totals.mitigasi_berjalan} icon={Wrench} />
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-base">Distribusi Level Risiko</CardTitle>
        </CardHeader>
        <CardContent className="h-64">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data.distribusi_risiko}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis dataKey="level" tickLine={false} axisLine={false} />
              <YAxis allowDecimals={false} tickLine={false} axisLine={false} width={32} />
              <Tooltip cursor={{ fill: 'rgba(0,0,0,0.04)' }} />
              <Bar dataKey="jumlah" name="Jumlah risiko" radius={[4, 4, 0, 0]}>
                {data.distribusi_risiko.map((d) => (
                  <Cell key={d.level} fill={d.warna} />
                ))}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="text-base">Perlu Tindakan</CardTitle>
        </CardHeader>
        <CardContent>
          <DataTable columns={columns} data={data.perlu_tindakan} pageSize={5} emptyText="Tidak ada laporan yang menunggu." />
        </CardContent>
      </Card>
    </div>
  );
}