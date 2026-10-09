import { zodResolver } from '@hookform/resolvers/zod';
import { Loader2 } from 'lucide-react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { FormField, NativeSelect } from '@/components/shared/FormField';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import type { Asset, AssetCategory, AssetPayload } from '@/types';

const KONDISI = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

// Semua field bertipe string di form; dikonversi ke angka/null di toPayload().
const schema = z.object({
  kode_aset: z.string().trim().min(1, 'Kode aset wajib diisi').max(50, 'Maksimal 50 karakter'),
  nama_aset: z.string().trim().min(1, 'Nama aset wajib diisi'),
  asset_category_id: z.string().min(1, 'Pilih kategori'),
  // Skala 0-5 (sesuaikan dengan pedoman penilaian kekritisan aset instansi)
  nilai_kekritisan: z
    .string()
    .refine((v) => v === '' || (!Number.isNaN(Number(v)) && Number(v) >= 0 && Number(v) <= 5), 'Isi angka 0–5'),
  jumlah: z.string().refine((v) => Number.isInteger(Number(v)) && Number(v) >= 1, 'Minimal 1'),
  kondisi: z.string(),
  merk: z.string(),
  snumber: z.string(),
  pengadaan: z.string(),
  lokasi: z.string(),
  deskripsi: z.string(),
});
type Values = z.infer<typeof schema>;

const EMPTY: Values = {
  kode_aset: '', nama_aset: '', asset_category_id: '', nilai_kekritisan: '', jumlah: '1',
  kondisi: '', merk: '', snumber: '', pengadaan: '', lokasi: '', deskripsi: '',
};

function toValues(a?: Asset): Values {
  if (!a) return EMPTY;
  return {
    kode_aset: a.kode_aset,
    nama_aset: a.nama_aset,
    asset_category_id: String(a.asset_category_id),
    nilai_kekritisan: a.nilai_kekritisan?.toString() ?? '',
    jumlah: String(a.jumlah),
    kondisi: a.kondisi ?? '',
    merk: a.merk ?? '',
    snumber: a.snumber ?? '',
    pengadaan: a.pengadaan ?? '',
    lokasi: a.lokasi ?? '',
    deskripsi: a.deskripsi ?? '',
  };
}

function toPayload(v: Values): AssetPayload {
  const opt = (s: string) => (s.trim() === '' ? null : s.trim());
  return {
    kode_aset: v.kode_aset.trim(),
    nama_aset: v.nama_aset.trim(),
    asset_category_id: Number(v.asset_category_id),
    nilai_kekritisan: v.nilai_kekritisan === '' ? null : Number(v.nilai_kekritisan),
    jumlah: Number(v.jumlah),
    kondisi: opt(v.kondisi),
    merk: opt(v.merk),
    snumber: opt(v.snumber),
    pengadaan: opt(v.pengadaan),
    lokasi: opt(v.lokasi),
    deskripsi: opt(v.deskripsi),
  };
}

interface FormProps {
  initial?: Asset;
  categories: AssetCategory[];
  submitting?: boolean;
  onSubmit: (payload: AssetPayload) => void;
  onCancel: () => void;
}

export function AssetForm({ initial, categories, submitting, onSubmit, onCancel }: FormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<Values>({ resolver: zodResolver(schema), defaultValues: toValues(initial) });

  return (
    <form onSubmit={handleSubmit((v) => onSubmit(toPayload(v)))} className="space-y-4" noValidate>
      <div className="grid gap-4 md:grid-cols-2">
        <FormField label="Kode Aset" htmlFor="kode_aset" required error={errors.kode_aset?.message}>
          <Input id="kode_aset" {...register('kode_aset')} />
        </FormField>
        <FormField label="Nama Aset" htmlFor="nama_aset" required error={errors.nama_aset?.message}>
          <Input id="nama_aset" {...register('nama_aset')} />
        </FormField>

        <FormField label="Kategori" htmlFor="asset_category_id" required error={errors.asset_category_id?.message}>
          <NativeSelect id="asset_category_id" {...register('asset_category_id')}>
            <option value="">Pilih kategori…</option>
            {categories.map((c) => (
              <option key={c.asset_category_id} value={c.asset_category_id}>
                {c.nama_kategori}
              </option>
            ))}
          </NativeSelect>
        </FormField>
        <FormField label="Kondisi" htmlFor="kondisi" error={errors.kondisi?.message}>
          <NativeSelect id="kondisi" {...register('kondisi')}>
            <option value="">-</option>
            {KONDISI.map((k) => (
              <option key={k} value={k}>{k}</option>
            ))}
          </NativeSelect>
        </FormField>

        <FormField label="Nilai Kekritisan" htmlFor="nilai_kekritisan" hint="Skala 0–5" error={errors.nilai_kekritisan?.message}>
          <Input id="nilai_kekritisan" type="number" step="0.1" min={0} max={5} {...register('nilai_kekritisan')} />
        </FormField>
        <FormField label="Jumlah" htmlFor="jumlah" required error={errors.jumlah?.message}>
          <Input id="jumlah" type="number" min={1} {...register('jumlah')} />
        </FormField>

        <FormField label="Merk" htmlFor="merk" error={errors.merk?.message}>
          <Input id="merk" {...register('merk')} />
        </FormField>
        <FormField label="Nomor Seri" htmlFor="snumber" error={errors.snumber?.message}>
          <Input id="snumber" {...register('snumber')} />
        </FormField>

        <FormField label="Tanggal Pengadaan" htmlFor="pengadaan" error={errors.pengadaan?.message}>
          <Input id="pengadaan" type="date" {...register('pengadaan')} />
        </FormField>
        <FormField label="Lokasi" htmlFor="lokasi" error={errors.lokasi?.message}>
          <Input id="lokasi" {...register('lokasi')} />
        </FormField>
      </div>

      <FormField label="Deskripsi" htmlFor="deskripsi" error={errors.deskripsi?.message}>
        <Textarea id="deskripsi" rows={3} {...register('deskripsi')} />
      </FormField>

      <div className="flex justify-end gap-2 pt-2">
        <Button type="button" variant="outline" onClick={onCancel} disabled={submitting}>
          Batal
        </Button>
        <Button type="submit" disabled={submitting}>
          {submitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          Simpan
        </Button>
      </div>
    </form>
  );
}

interface DialogProps extends Omit<FormProps, 'onCancel'> {
  open: boolean;
  onClose: () => void;
}

export function AssetFormDialog({ open, onClose, initial, ...rest }: DialogProps) {
  return (
    <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>{initial ? 'Edit Aset' : 'Tambah Aset'}</DialogTitle>
          <DialogDescription>
            Aset otomatis tercatat pada sub-bidang kamu.
          </DialogDescription>
        </DialogHeader>
        {/* key: form di-reset tiap ganti aset yang diedit */}
        <AssetForm key={initial?.asset_id ?? 'new'} initial={initial} onCancel={onClose} {...rest} />
      </DialogContent>
    </Dialog>
  );
}