# Frontend Plan — Platform Sekolah

> **Goal:** UI tenant sekolah di-port dari root pilot `C:\Projects\project-cahaya-sunnah`; UI baru difokuskan pada platform admin, marketing, onboarding, dan koneksi funnel.

**Stack:** Vue 3, TypeScript, Inertia 3, Tailwind CSS, Vite.

---

## 1. UX principles

1. **Staff-first** untuk app tenant — bukan marketing SaaS di dalam app sekolah.
2. **Mobile-first** untuk portal wali murid (Fase port P5).
3. Bahasa Indonesia semua label & error.
4. Target desktop staf: **1366×768**.
5. Sembunyikan/disable aksi tanpa permission; backend tetap enforce.
6. Nama sekolah di header = **tenant** (`school.name`), bukan "Platform Sekolah".
7. Brand produk di footer kecil opsional: *Didukung oleh Platform Sekolah*.

---

## 1.1 Brand baseline aplikasi

Aplikasi Laravel/Inertia mengikuti ritme layout root `C:\Projects\project-cahaya-sunnah`: dashboard operasional sekolah berbasis Vue 3 + Tailwind, bukan folder legacy `absensi-sekolah-qr-code-master` dan bukan landing marketing. Pola utama: sidebar kiri, logo/initial gradient, topbar sticky, mobile bottom nav, card putih rounded, canvas `brand-50`, dan aksen blue-slate brand Platform Sekolah. Light mode memakai sidebar putih; dark mode memakai sidebar gelap seperti pilot.

| Token | Hex | Penggunaan |
|-------|-----|------------|
| `brand-700` | `#2563EB` | Primary action, active menu, hero gradient |
| `brand-800` | `#1D4ED8` | Hover primary action |
| `brand-600` | `#3B82F6` | Hero gradient support |
| `brand-100` | `#DBEAFE` | Soft active/hover background |
| `brand-50` | `#F8FAFC` | Page surface light mode |
| `ink` | `#0F172A` | Main text |
| `line` | `#E2E8F0` | Border/divider |

Font app tetap `Figtree`; ikon memakai komponen SVG lokal `Components/App/Icon.vue` seperti reference root.

Dashboard tenant per sekolah harus mengikuti sistem root pilot `C:\Projects\project-cahaya-sunnah`: beranda operasional yang bisa langsung dipakai sekolah, bukan placeholder SaaS. Saat modul belum lengkap, isi tetap disusun mengikuti slot pilot: ringkasan absensi hari ini, kelas aktif, siswa/guru, shortcut aksi, notifikasi, dan status kalender. Data yang belum tersedia boleh tampil sebagai state kosong/coming soon, tapi struktur halaman harus siap diisi modul port berikutnya.

Shell aplikasi Inertia mem-port pola layout root Cahaya Sunnah dengan warna Platform Sekolah: sidebar `lg:w-72`, light sidebar putih + dark sidebar `#1A1D20`, active item blue, grouped navigation, sticky topbar desktop, mobile topbar + bottom nav, `app-card`, `app-button-primary`, `app-table`, dan hero gradient blue ke dark.

Marketing page, onboarding, platform admin, dan tenant dashboard adalah satu funnel produk. Marketing menjual value dan CTA daftar; onboarding mengumpulkan data minimum agar tenant bisa langsung membuka dashboard sekolah; dashboard tenant menampilkan pekerjaan harian sekolah dari port pilot; platform admin memantau status tenant/trial/billing tanpa mengelola data operasional sekolah. Fokus desain baru ada di platform/funnel; UI tenant semaksimal mungkin copy root pilot lalu ganti brand/token dan scope tenant.

---

## 2. Tiga shell UI + hubungan funnel

| Shell | Layout | Host | Peran dalam produk |
|-------|--------|------|--------------------|
| **Marketing** | Astro landing / public pages | `platformsekolah.id` | Menjelaskan value, harga/trial, CTA daftar, trust, dan demo. |
| **Platform admin** | `AuthenticatedLayout.vue` mode platform atau `PlatformLayout.vue` jika dipisah nanti | `admin.platformsekolah.id` / `/platform` | Kelola lifecycle tenant: trial, active, suspended, reset admin, billing draft. Tidak masuk data operasional sekolah v1. |
| **Tenant app** | `AuthenticatedLayout.vue` | `{slug}.platformsekolah.id` / `/t/{slug}` | Operasional harian sekolah, mengikuti sistem dashboard root pilot. |
| **Onboarding** | `Onboarding/*` dengan stepper | setelah daftar | Membuat tenant siap pakai sebelum dashboard: profil sekolah, tahun ajaran, kelas, import siswa, undang user. |

Alur utama: Marketing CTA → Register School → Verify Email → Onboarding 5 langkah → Tenant Dashboard → Platform Admin memantau status tenant/trial/billing.

---

## 3. Platform admin UI (v1)

### 3.1 Pages

```text
resources/js/Pages/Platform/
├── Dashboard.vue      # founder summary: tenant status, MRR, trial ending soon, recent schools
├── Tenants/
│   ├── Index.vue      # tabel: nama, slug, status, trial_ends_at, usage, actions
│   └── Show.vue       # detail profil sekolah, subscription, users, activity log, actions
├── Billing/
│   └── Index.vue      # subscription list: filter by status/plan, status toggle manual
└── Auth/
    └── Login.vue      # jika login terpisah di admin host
```

### 3.2 Index columns

| Kolom | Aksi |
|-------|------|
| Nama sekolah | Link show (opsional) |
| Subdomain | `slug.platformsekolah.id` |
| Status | Badge: trial / active / suspended |
| Trial berakhir | Tanggal |
| Daftar | Tanggal |
| Aksi | Suspend, Aktifkan, Reset password admin |

### 3.3 UX rules

- Konfirmasi modal untuk suspend & reset password.
- Toast sukses/gagal bahasa Indonesia.
- Tidak tampilkan data murid di v1.
- Platform admin mengikuti brand baseline aplikasi: hero blue-slate, cards rounded, status badge, dashboard summary, tenant detail, dan table modern.

---

## 4. Onboarding & registrasi (P6)

```text
resources/js/Pages/
├── Welcome.vue              # landing marketing
├── Auth/RegisterSchool.vue  # form daftar
└── Onboarding/
    ├── Profile.vue          # logo, alamat, kepala sekolah
    ├── AcademicYear.vue
    ├── Classes.vue
    ├── ImportStudents.vue
    └── InviteUsers.vue
```

Progress stepper 1–5; simpan state di backend (`onboarding_step` di `schools` opsional).

---

## 5. Tenant app — port dari pilot

### 5.1 Sidebar menu (sama pilot)

```text
Beranda
Data Siswa
Data Guru
Kelas
Absensi Siswa               ✅ Live route (T1 Slice 2)
  └── QR Scanner              ✅ Live route (T1 Slice 3)
Absensi Guru                ✅ Live route (T1 Slice 4)
Pelanggaran
Jenis Pelanggaran
Poin Karakter
Laporan Murid
Laporan & Cetak
Notifikasi
Kirim WhatsApp
Kalender Akademik            ✅ Live route (T1 Slice 1)
Pengaturan Sekolah
Backup
User
```

Filter menu by `permissions` dari Inertia shared props (team-scoped).

### 5.2 Layout authenticated

Port `AuthenticatedLayout.vue` dari pilot:

- Topbar: **{school.name}** + user menu
- Sidebar collapsible &lt; 1024px
- Breadcrumb opsional halaman dalam

### 5.2.1 Dashboard tenant per sekolah

Dashboard tenant adalah halaman `tenant.dashboard` dan harus memakai pola root pilot `C:\Projects\project-cahaya-sunnah`, bukan dashboard marketing/platform. Struktur target:

1. Hero/header tenant: nama sekolah, status trial/active, tanggal/hari aktif, CTA aksi harian.
2. KPI operasional: siswa aktif, guru aktif, kelas aktif, absensi hari ini, pelanggaran pending, notifikasi.
3. Shortcut modul: Data Siswa, Data Guru, Kelas, Absensi Siswa, Absensi Guru, Pelanggaran, Laporan.
4. Panel aktivitas: absensi terbaru, siswa perlu perhatian, notifikasi sekolah, kalender akademik.
5. Empty state jujur untuk modul yang belum di-port, tapi layout slot tetap dipertahankan agar P3–P5 tinggal mengisi data.

Data awal dashboard boleh berasal dari P2 master data (`students`, `teachers`, `classes`, `academic_years`, `semesters`) supaya setelah onboarding/import, sekolah langsung melihat ringkasan nyata. Setelah P3/P4/P5, slot yang sama diisi attendance/violation/report metrics.

### 5.3 Shared props

```typescript
// HandleInertiaRequests
{
  auth: { user, roles, permissions },
  school: { id, name, slug, logo_url, status },
  flash: { success, error },
  appName: 'Platform Sekolah', // untuk title tag saja
}
```

### 5.4 Halaman kritikal UX

| Page | Catatan |
|------|---------|
| `AcademicCalendar/Holidays/Index` | Kalender + list per bulan; Create, Edit — ✅ (T1 Slice 1) |
| `Attendance/Students/Index` | Grid status H/T/I/S/A cepat — ✅ (T1 Slice 2) |
| `Attendance/Students/Recap` | Rekap per periode — ✅ (T1 Slice 2) |
| `Attendance/Students/QrScanner` | Scanner camera; fallback input token — ✅ (T1 Slice 3) |
| `Attendance/Students/QrToken` | Tampil QR siswa + cetak — ✅ (T1 Slice 3) |
| `Attendance/Teachers/Index` | Absensi guru per tanggal — ✅ (T1 Slice 4) |
| `Attendance/Teachers/Recap` | Rekap absensi guru — ✅ (T1 Slice 4) |
| `Students/Index` | Filter kelas, search NIS/nama |
| `Violations/Create` | Auto-fill poin |
| `Reports/*` | Tombol PDF/Excel jelas |
| `Guardian/Dashboard` | Mobile-friendly cards |

---

## 6. Types

Port + tambah:

```text
resources/js/types/
├── school.ts         # School, SchoolStatus
├── students.ts
├── platform.ts       # TenantListItem, PlatformStats (future)
└── index.ts
```

```typescript
export type School = {
  id: string
  name: string
  slug: string
  logo_url: string | null
  status: 'trial' | 'active' | 'suspended' | 'expired'
}
```

---

## 7. Components

Port reusable dari pilot:

- `DataTable`, `Pagination`, `ConfirmDialog`, `StatusBadge`
- `AttendanceStatusSelect`, `StudentSearchCombobox`

Baru:

- `Platform/TenantStatusBadge.vue`
- `ActivityLog`, `ActivityLogger` (Support class — platform audit trail)
- `Onboarding/StepIndicator.vue`

---

## 8. Permissions di UI

```typescript
// lib/permissions.ts
export function can(user: User, permission: string): boolean
```

Hide tombol "Tambah Siswa" jika tidak punya `students.create`; route tetap protected Policy.

---

## 9. Error states tenant

| State | UI |
|-------|-----|
| School suspended | Halaman statis: hubungi Platform Sekolah |
| Trial expired read-only | Banner + disable submit buttons (middleware blocks access with 403 + message) |
| 404 subdomain | Halaman "Sekolah tidak ditemukan" |

---

## 10. Build & assets

- Vite; code split per Page lazy optional.
- Logo tenant dari URL S3 signed atau public path.
- Favicon default Platform Sekolah; tidak override favicon tenant di v1.

---

## 11. Testing manual

| Check | Device |
|-------|--------|
| Sidebar semua role | 1366×768 desktop |
| QR scanner | Android Chrome |
| Wali murid dashboard | iPhone Safari |
| Platform tenant table | Desktop |

---

## 12. Referensi

- Pilot: `project-cahaya-sunnah/docs/plans/06-frontend-plan.md`
- Struktur: [03-project-structure.md](./03-project-structure.md)
