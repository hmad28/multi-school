# ADR 0001: Laravel + Inertia monolith

**Status:** Accepted  
**Tanggal:** 30 Mei 2026  
**Konteks:** Platform Sekolah & pilot klien

## Keputusan

Gunakan **satu monolith Laravel** dengan **Inertia.js + Vue 3** untuk UI. Tidak memisahkan REST API + SPA terpisah di Fase 2.

## Alasan

- Pilot `project-cahaya-sunnah` sudah terbukti (107 tests, fitur lengkap).
- Tim kecil — satu deploy, satu codebase, auth session sederhana.
- Role/permission Spatie terintegrasi dengan Form Request & Policy.
- Port ke SaaS = tambah tenancy, bukan ganti stack.

## Konsekuensi

- SEO marketing pakai Blade/Inertia halaman terbatas atau landing statis.
- Mobile wali murid = responsive web, bukan native app.
- Scale horizontal = stateless PHP + Redis session jika diperlukan.

## Alternatif yang ditolak

- Next.js frontend terpisah — duplikasi auth & validasi.
- Livewire — pilot sudah Inertia; konsistensi port lebih penting.
