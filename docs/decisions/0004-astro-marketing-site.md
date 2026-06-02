# ADR 0004: Marketing site — Astro + desain Webflow/Framer

**Status:** Accepted  
**Tanggal:** 30 Mei 2026  
**Konteks:** Landing `platformsekolah.id` butuh SEO kuat; app tenant tetap Laravel Inertia.

## Keputusan

| Lapisan | Teknologi | Host |
|---------|-----------|------|
| **Marketing / SEO** | **Astro** (SSG) | `platformsekolah.id` — static di **Nginx mini PC** (lihat ADR 0005) |
| **Desain UI** | **Webflow** dan/atau **Framer** | Prototipe & handoff visual; bukan runtime app |
| **App sekolah** | Laravel + Inertia | `{slug}.platformsekolah.id` |
| **Super-admin** | Laravel + Inertia | `admin.platformsekolah.id` |

Landing **tidak** di-render Inertia dan **tidak** wajib Blade Laravel di production (boleh API-only untuk registrasi).

## Alur kerja desain → production

```text
Framer / Webflow (desain, copy, section)
        ↓ handoff (spacing, warna, asset, copy final)
Astro (implementasi komponen, SEO, performa)
        ↓ deploy
platformsekolah.id
```

- **Framer:** cocok prototipe cepat + animasi; export referensi untuk dev Astro.
- **Webflow:** cocok layout marketing lengkap; bisa jadi referensi visual (jarang auto-export ke Astro — biasanya rebuild di Astro).

Tidak hosting **dua** landing sekaligus (Webflow live + Astro live) di domain sama — pilih **Astro sebagai production**.

## Integrasi dengan Laravel

| Kebutuhan | Pola |
|-----------|------|
| Tombol "Daftar" / "Coba gratis" | Link ke `https://platformsekolah.id/daftar` **atau** `https://app.platformsekolah.id/register` |
| Form registrasi | **Opsi A:** halaman Astro + `POST` ke API Laravel `POST /api/v1/schools/register` |
| | **Opsi B:** CTA → subdomain/route Laravel Blade satu halaman register (minimal) |
| Login sekolah | Selalu ke `https://{slug}.platformsekolah.id/login` (tenant exists) atau `platformsekolah.id/masuk` → pilih slug |

Rekomendasi v1: **CTA Astro → URL Laravel** `/daftar` (satu halaman register SSR minimal) **atau** API JSON + Astro form — pilih saat P6.

## Repo

```text
multi-school/              ← Laravel app (monorepo root)
marketing/                   ← Astro project (baru)
  ├── src/pages/index.astro
  ├── src/pages/harga.astro
  └── package.json
```

Atau repo terpisah `platform-sekolah-marketing` — monorepo lebih mudah satu PR.

## SEO (tetap wajib di Astro)

- SSG/SSR prerender per halaman
- `sitemap.xml`, `robots.txt`
- Meta + OG + JSON-LD di layout Astro
- `lang="id"`

## Konsekuensi

- Dua pipeline deploy: Pages (Astro) + VPS/tunnel (Laravel).
- CORS/API auth untuk form register harus didesain (token, rate limit).
- Desain Webflow/Framer = **biaya subscription** + waktu handoff ke Astro.

## Alternatif ditolak untuk landing prod

- Inertia `Welcome.vue` sebagai homepage utama
- Hanya Webflow hosted tanpa Astro — SEO OK tapi vendor lock-in & integrasi API lebih kaku untuk tim dev Laravel
