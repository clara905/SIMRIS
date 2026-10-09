import type {
  Asset,
  AssetCategory,
  AssetPayload,
  Captcha,
  DashboardSummary,
  LoginPayload,
  RoleName,
  StafDashboardData,
  User,
} from '@/types';

export const wait = (ms = 300) => new Promise<void>((r) => setTimeout(r, ms));

export function mockError(status: number, message: string) {
  return { response: { status, data: { message } } };
}

// ---------- Sesi & user ----------
const PERMS: Record<RoleName, string[]> = {
  super_admin: [
    'master.manage', 'satker.manage', 'user.view', 'user.manage', 'sop.manage',
    'asset.view', 'asset.manage', 'risk.view', 'risk.assess', 'risk.approve',
    'mitigation.view', 'mitigation.manage', 'mitigation.verify', 'report.view',
  ],
  admin_satker: [
    'user.view', 'user.manage', 'sop.manage', 'asset.view', 'risk.view',
    'mitigation.view', 'report.view',
  ],
  kasatker: ['asset.view', 'risk.view', 'risk.approve', 'mitigation.view', 'report.view'],
  kabid: ['asset.view', 'risk.view', 'risk.approve', 'mitigation.view', 'report.view'],
  kasubbid: [
    'asset.view', 'asset.manage', 'risk.view', 'risk.approve',
    'mitigation.view', 'mitigation.verify', 'report.view',
  ],
  staf: ['asset.view', 'risk.view', 'risk.assess', 'mitigation.view', 'mitigation.manage'],
};

function buildUser(role: RoleName): User {
  const isSuper = role === 'super_admin';
  return {
    users_id: 1,
    name: `Mock ${role}`,
    username: role,
    email: `${role}@simris.local`,
    roles: [role],
    permissions: PERMS[role],
    satker: isSuper ? null : { id: 1, nama: 'Satker Contoh' },
    bidang: isSuper || role === 'admin_satker' || role === 'kasatker' ? null : { id: 1, nama: 'Bidang Teknologi Informasi' },
    sub_bidang: ['kasubbid', 'staf'].includes(role) ? { id: 1, nama: 'Sub Bidang Infrastruktur' } : null,
  };
}

const SESSION_KEY = 'simris_mock_user';
export const mockSession = {
  get(): User | null {
    try {
      const raw = localStorage.getItem(SESSION_KEY);
      return raw ? (JSON.parse(raw) as User) : null;
    } catch {
      return null;
    }
  },
  set(u: User) {
    try { localStorage.setItem(SESSION_KEY, JSON.stringify(u)); } catch { /* abaikan */ }
  },
  clear() {
    try { localStorage.removeItem(SESSION_KEY); } catch { /* abaikan */ }
  },
};

// ---------- Captcha ----------
let captchaAnswer = '';
export function mockCaptcha(): Captcha {
  captchaAnswer = Math.random().toString(36).slice(2, 7).toUpperCase();
  const svg =
    `<svg xmlns="http://www.w3.org/2000/svg" width="160" height="48">` +
    `<rect width="100%" height="100%" fill="#f1f5f9"/>` +
    `<line x1="0" y1="14" x2="160" y2="38" stroke="#cbd5e1" stroke-width="2"/>` +
    `<text x="80" y="32" font-size="26" font-family="monospace" font-weight="bold" ` +
    `text-anchor="middle" fill="#334155" letter-spacing="4">${captchaAnswer}</text></svg>`;
  return { key: 'mock', image: `data:image/svg+xml;utf8,${encodeURIComponent(svg)}` };
}

export function mockLogin(p: LoginPayload): User {
  if (p.captcha.trim().toUpperCase() !== captchaAnswer) throw mockError(422, 'Captcha salah.');
  const role = (Object.keys(PERMS) as RoleName[]).find((r) => r === p.username.trim());
  if (!role) throw mockError(422, 'Mock: username harus salah satu nama role (mis. staf, kasubbid).');
  const user = buildUser(role);
  mockSession.set(user);
  return user;
}

// ---------- Dashboard ----------
export const mockSummary: DashboardSummary = {
  totals: { aset: 128, risiko: 46, menunggu_approval: 7, mitigasi_berjalan: 12 },
  distribusi_risiko: [
    { level: 'Rendah', warna: '#22C55E', jumlah: 14 },
    { level: 'Sedang', warna: '#EAB308', jumlah: 18 },
    { level: 'Tinggi', warna: '#F97316', jumlah: 10 },
    { level: 'Sangat Tinggi', warna: '#EF4444', jumlah: 4 },
  ],
  perlu_tindakan: [
    { risk_assessment_id: 1, kode_risiko: 'R-2026-001', deskripsi_risiko: 'Kegagalan server aplikasi karena tidak ada redundansi', level: 'Sangat Tinggi', warna: '#EF4444', status: 'review_kasatker', oleh: 'Budi Santoso' },
    { risk_assessment_id: 2, kode_risiko: 'R-2026-004', deskripsi_risiko: 'Akses tidak sah ke basis data kepegawaian', level: 'Tinggi', warna: '#F97316', status: 'review_kabid', oleh: 'Siti Rahma' },
    { risk_assessment_id: 3, kode_risiko: 'R-2026-007', deskripsi_risiko: 'Kehilangan data akibat backup tidak terjadwal', level: 'Tinggi', warna: '#F97316', status: 'review_kasubbid', oleh: 'Andi Pratama' },
  ],
};

export const mockStaf: StafDashboardData = {
  totals: { aset: 24, draft: 3, diajukan: 5, perlu_revisi: 1, mitigasi_aktif: 4 },
  perlu_revisi: [
    { risk_assessment_id: 9, kode_risiko: 'R-2026-012', deskripsi_risiko: 'Router utama tanpa firmware terbaru', level: 'Sedang', warna: '#EAB308', status: 'revisi', oleh: 'Saya' },
  ],
  tugas_mitigasi: [
    { risk_treatments_id: 1, rencana_mitigasi: 'Pasang UPS cadangan di ruang server', target_date: '2026-10-30', status: 'berjalan' },
    { risk_treatments_id: 2, rencana_mitigasi: 'Terapkan jadwal backup harian otomatis', target_date: '2026-11-15', status: 'direncanakan' },
  ],
};

// ---------- Aset ----------
export const mockCategories: AssetCategory[] = [
  { asset_category_id: 1, nama_kategori: 'Perangkat Keras' },
  { asset_category_id: 2, nama_kategori: 'Perangkat Lunak' },
  { asset_category_id: 3, nama_kategori: 'Data & Informasi' },
  { asset_category_id: 4, nama_kategori: 'Jaringan' },
];

let assets: Asset[] = [
  { asset_id: 1, kode_aset: 'AST-001', nama_aset: 'Server Aplikasi SIMPEG', asset_category_id: 1, kategori: 'Perangkat Keras', nilai_kekritisan: 4.5, jumlah: 2, kondisi: 'Baik', merk: 'Dell PowerEdge', snumber: 'SN-DELL-0912', pengadaan: '2022-03-14', lokasi: 'Ruang Server Lt.2', deskripsi: null, sub_bidang: 'Sub Bidang Infrastruktur', status: 'aktif' },
  { asset_id: 2, kode_aset: 'AST-002', nama_aset: 'Basis Data Kepegawaian', asset_category_id: 3, kategori: 'Data & Informasi', nilai_kekritisan: 5, jumlah: 1, kondisi: 'Baik', merk: null, snumber: null, pengadaan: null, lokasi: 'Data Center', deskripsi: 'Data induk pegawai', sub_bidang: 'Sub Bidang Infrastruktur', status: 'aktif' },
  { asset_id: 3, kode_aset: 'AST-003', nama_aset: 'Switch Core Gedung A', asset_category_id: 4, kategori: 'Jaringan', nilai_kekritisan: 3.5, jumlah: 1, kondisi: 'Rusak Ringan', merk: 'Cisco', snumber: 'SN-CSC-7781', pengadaan: '2020-08-01', lokasi: 'Ruang Panel Lt.1', deskripsi: null, sub_bidang: 'Sub Bidang Infrastruktur', status: 'aktif' },
];

const catName = (id: number) =>
  mockCategories.find((c) => c.asset_category_id === id)?.nama_kategori ?? '-';

export const mockAssets = {
  list: () => [...assets],
  create(p: AssetPayload): Asset {
    const a: Asset = {
      ...p,
      asset_id: Math.max(0, ...assets.map((x) => x.asset_id)) + 1,
      kategori: catName(p.asset_category_id),
      sub_bidang: 'Sub Bidang Infrastruktur',
      status: 'aktif',
    };
    assets = [a, ...assets];
    return a;
  },
  update(id: number, p: AssetPayload): Asset {
    const old = assets.find((x) => x.asset_id === id);
    if (!old) throw mockError(404, 'Aset tidak ditemukan');
    const a: Asset = { ...old, ...p, kategori: catName(p.asset_category_id) };
    assets = assets.map((x) => (x.asset_id === id ? a : x));
    return a;
  },
  remove(id: number) {
    assets = assets.filter((x) => x.asset_id !== id);
  },
};