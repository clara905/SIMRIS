import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { ColumnDef } from '@tanstack/react-table';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { assetApi } from '@/api/assets';
import { getErrorMessage } from '@/api/client';
import { DataTable } from '@/components/shared/DataTable';
import { PageHeader } from '@/components/shared/PageHeader';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Asset, AssetPayload } from '@/types';
import { AssetFormDialog } from './AssetForm';

const ASSET_QUERY_KEY = ['assets'];

export default function AssetsPage() {
  const queryClient = useQueryClient();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Asset>();

  const assetsQuery = useQuery({
    queryKey: ASSET_QUERY_KEY,
    queryFn: assetApi.list,
  });
  const categoriesQuery = useQuery({
    queryKey: ['asset-categories'],
    queryFn: assetApi.categories,
  });

  const saveMutation = useMutation({
    mutationFn: ({ id, payload }: { id?: number; payload: AssetPayload }) =>
      id === undefined ? assetApi.create(payload) : assetApi.update(id, payload),
    onSuccess: async (_asset, { id }) => {
      await queryClient.invalidateQueries({ queryKey: ASSET_QUERY_KEY });
      setDialogOpen(false);
      setEditing(undefined);
      toast.success(id === undefined ? 'Aset berhasil ditambahkan.' : 'Aset berhasil diperbarui.');
    },
    onError: (error) => toast.error(getErrorMessage(error, 'Gagal menyimpan aset.')),
  });

  const deleteMutation = useMutation({
    mutationFn: assetApi.remove,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ASSET_QUERY_KEY });
      toast.success('Aset berhasil dihapus.');
    },
    onError: (error) => toast.error(getErrorMessage(error, 'Gagal menghapus aset.')),
  });

  const columns: ColumnDef<Asset>[] = [
    { accessorKey: 'kode_aset', header: 'Kode Aset' },
    { accessorKey: 'nama_aset', header: 'Nama Aset' },
    { accessorKey: 'kategori', header: 'Kategori' },
    { accessorKey: 'lokasi', header: 'Lokasi', cell: ({ row }) => row.original.lokasi || '-' },
    { accessorKey: 'jumlah', header: 'Jumlah' },
    {
      accessorKey: 'kondisi',
      header: 'Kondisi',
      cell: ({ row }) => row.original.kondisi ? <Badge variant="outline">{row.original.kondisi}</Badge> : '-',
    },
    {
      id: 'actions',
      header: 'Aksi',
      enableGlobalFilter: false,
      cell: ({ row }) => (
        <div className="flex gap-1">
          <Button
            type="button"
            variant="ghost"
            size="icon"
            aria-label={`Edit ${row.original.nama_aset}`}
            onClick={() => {
              setEditing(row.original);
              setDialogOpen(true);
            }}
          >
            <Pencil />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            aria-label={`Hapus ${row.original.nama_aset}`}
            disabled={deleteMutation.isPending}
            onClick={() => {
              if (window.confirm(`Hapus aset "${row.original.nama_aset}"?`)) {
                deleteMutation.mutate(row.original.asset_id);
              }
            }}
          >
            <Trash2 />
          </Button>
        </div>
      ),
    },
  ];

  function openCreateDialog() {
    setEditing(undefined);
    setDialogOpen(true);
  }

  function closeDialog() {
    setDialogOpen(false);
    setEditing(undefined);
  }

  return (
    <div>
      <PageHeader
        title="Data Aset"
        description="Kelola inventaris aset pada lingkup kerja kamu."
        actions={
          <Button onClick={openCreateDialog} disabled={categoriesQuery.isError || !categoriesQuery.data}>
            <Plus />
            Tambah Aset
          </Button>
        }
      />

      {assetsQuery.isError ? (
        <p role="alert" className="text-sm text-destructive">Gagal memuat data aset. Silakan coba lagi.</p>
      ) : (
        <DataTable
          columns={columns}
          data={assetsQuery.data ?? []}
          isLoading={assetsQuery.isLoading}
          searchPlaceholder="Cari aset..."
          emptyText="Belum ada data aset."
        />
      )}

      {categoriesQuery.isError && (
        <p role="alert" className="mt-3 text-sm text-destructive">Gagal memuat kategori aset. Silakan coba lagi.</p>
      )}

      <AssetFormDialog
        key={editing?.asset_id ?? 'new'}
        open={dialogOpen}
        onClose={closeDialog}
        initial={editing}
        categories={categoriesQuery.data ?? []}
        submitting={saveMutation.isPending}
        onSubmit={(payload) => saveMutation.mutate({ id: editing?.asset_id, payload })}
      />
    </div>
  );
}
