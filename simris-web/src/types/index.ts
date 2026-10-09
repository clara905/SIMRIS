export type RoleName =
  | 'super_admin'
  | 'admin_satker'
  | 'kasatker'
  | 'kabid'
  | 'kasubbid'
  | 'staf';

export interface OrgRef {
  id: number;
  nama: string;
}

export interface User {
  users_id: number;
  name: string;
  username: string;
  email: string;
  roles: RoleName[];
  permissions: string[];
  satker: OrgRef | null;
  bidang: OrgRef | null;
  sub_bidang: OrgRef | null;
}

export interface Captcha {
  key: string;
  image: string; // data URI atau URL gambar
}

export interface LoginPayload {
  username: string;
  password: string;
  captcha: string;
  captcha_key: string;
}

export interface AssetCategory {
  asset_category_id: number;
  nama_kategori: string;
}

export interface Asset {
  asset_id: number;
  kode_aset: string;
  nama_aset: string;
  asset_category_id: number;
  kategori: string;
  nilai_kekritisan: number | null;
  jumlah: number;
  kondisi: string | null;
  merk: string | null;
  snumber: string | null;
  pengadaan: string | null;
  lokasi: string | null;
  deskripsi: string | null;
  sub_bidang: string | null;
  status: string;
}

export type AssetPayload = Omit<Asset, 'asset_id' | 'kategori' | 'sub_bidang' | 'status'>;

export interface RiskDistribution {
  level: string;
  warna: string;
  jumlah: number;
}

export interface RiskRow {
  risk_assessment_id: number;
  kode_risiko: string;
  deskripsi_risiko: string;
  level: string;
  warna: string;
  status: string;
  oleh: string;
}

export interface MitigationTask {
  risk_treatments_id: number;
  rencana_mitigasi: string;
  target_date: string | null;
  status: string;
}

export interface DashboardSummary {
  totals: {
    aset: number;
    risiko: number;
    menunggu_approval: number;
    mitigasi_berjalan: number;
  };
  distribusi_risiko: RiskDistribution[];
  perlu_tindakan: RiskRow[];
}

export interface StafDashboardData {
  totals: {
    aset: number;
    draft: number;
    diajukan: number;
    perlu_revisi: number;
    mitigasi_aktif: number;
  };
  perlu_revisi: RiskRow[];
  tugas_mitigasi: MitigationTask[];
}