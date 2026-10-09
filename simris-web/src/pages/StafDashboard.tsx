import { useQuery } from '@tanstack/react-query';
import { Boxes, FilePen, Send, Wrench } from 'lucide-react';
import { Link } from 'react-router-dom';
import { dashboardApi } from '@/api/dashboard';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatCard } from '@/components/shared/StatCard';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { scopeLabel } from '@/lib/roles';
import { useAuth } from '@/stores/auth';

function formatDate(iso: string | null) {
  return iso ? new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
}

export default function StafDashboard() {
  const user = useAuth((s) => s.user);
  const { data, isLoading, isError } = useQuery({
    queryKey: ['dashboard', 'staf'],
    queryFn: dashboardApi.staf,
  });

  if (isError) return <p className="text-sm text-red-600">Gagal memuat dashboard.</p>;
  if (isLoading || !data) return <p className="text-sm text-muted-foreground">Memuat dashboard…</p>;

  return (
    <div className="space-y-6">
      <PageHeader
        title={`Halo, ${user?.name ?? ''}`}
        description={`Pekerjaan kamu · ${scopeLabel(user)}`}
        actions={
          <>
            <Button variant="outline" render={<Link to="/aset" />}>
              Lihat Aset
            </Button>
            <Button render={<Link to="/risiko" />}>
              Nilai Risiko Baru
            </Button>
          </>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Aset Sub-Bidang" value={data.totals.aset} icon={Boxes} />
        <StatCard label="Draft Penilaian" value={data.totals.draft} icon={FilePen} />
        <StatCard label="Sudah Diajukan" value={data.totals.diajukan} icon={Send} />
        <StatCard label="Mitigasi Aktif" value={data.totals.mitigasi_aktif} icon={Wrench} />
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center justify-between text-base">
              Perlu Revisi
              <Badge variant={data.totals.perlu_revisi ? 'destructive' : 'secondary'}>{data.totals.perlu_revisi}</Badge>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            {data.perlu_revisi.length === 0 && <p className="text-sm text-muted-foreground">Tidak ada yang perlu direvisi.</p>}
            {data.perlu_revisi.map((r) => (
              <div key={r.risk_assessment_id} className="rounded-md border p-3">
                <div className="flex items-center justify-between gap-2">
                  <span className="text-sm font-medium">{r.kode_risiko}</span>
                  <span className="inline-flex items-center gap-2 text-xs">
                    <span className="inline-block h-2.5 w-2.5 rounded-full" style={{ background: r.warna }} />
                    {r.level}
                  </span>
                </div>
                <p className="mt-1 text-sm text-muted-foreground">{r.deskripsi_risiko}</p>
              </div>
            ))}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-base">Tugas Mitigasi</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            {data.tugas_mitigasi.length === 0 && <p className="text-sm text-muted-foreground">Belum ada tugas mitigasi.</p>}
            {data.tugas_mitigasi.map((t) => (
              <div key={t.risk_treatments_id} className="rounded-md border p-3">
                <p className="text-sm font-medium">{t.rencana_mitigasi}</p>
                <div className="mt-1 flex items-center justify-between text-xs text-muted-foreground">
                  <span>Target: {formatDate(t.target_date)}</span>
                  <Badge variant="outline" className="capitalize">{t.status}</Badge>
                </div>
              </div>
            ))}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}