# Marketing Landing & SEO — Platform Sekolah

> **Goal:** Landing `platformsekolah.id` SEO-first; app operasional tetap Laravel di subdomain.

**Keputusan:** [ADR 0004](../decisions/0004-astro-marketing-site.md) — **Astro** (production) + desain **Webflow / Framer** (handoff).

---

## 1. Arsitektur tiga deploy

```text
┌─────────────────────────────────────────────────────────────┐
│  platformsekolah.id                                         │
│  Astro (SSG) @ Cloudflare Pages                             │
│  /  /harga  /fitur  /tentang  /blog (opsional)              │
│  Desain: Framer / Webflow → implementasi Astro              │
└──────────────────────────┬──────────────────────────────────┘
                           │ CTA "Daftar" / API register
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  Laravel (VPS / tunnel)                                     │
│  • POST /api/.../register  atau  /daftar (minimal)          │
│  • admin.platformsekolah.id  → super-admin                  │
│  • {slug}.platformsekolah.id → app sekolah (Inertia)        │
└─────────────────────────────────────────────────────────────┘
```

| Host | Stack | Index Google |
|------|-------|--------------|
| `platformsekolah.id` | Astro | **Ya** |
| `*.platformsekolah.id` (tenant) | Laravel Inertia | **Tidak** (`noindex`) |
| `admin.platformsekolah.id` | Laravel Inertia | **Tidak** |

---

## 2. Peran Webflow vs Framer vs Astro

| Tool | Peran | Bukan untuk |
|------|-------|-------------|
| **Framer** | Prototipe tinggi, animasi, presentasi ke stakeholder | Production app sekolah |
| **Webflow** | Layout marketing detail, komponen visual, kadang copy | Backend, database |
| **Astro** | **Site production** — HTML statis cepat, SEO, deploy Pages | Dashboard absensi |

**Workflow disarankan:**

1. Desain section di **Framer** (cepat) atau **Webflow** (halaman panjang).
2. Export asset (gambar, SVG), token warna, spacing → dev.
3. Bangun halaman di **Astro** (`marketing/` di monorepo).
4. Review side-by-side dengan Framer/Webflow sebelum launch.

Jangan publish Webflow dan Astro di URL yang sama — satu source of truth: **Astro**.

---

## 3. Struktur proyek Astro (rencana)

```text
marketing/
├── astro.config.mjs
├── package.json
├── public/
│   ├── robots.txt
│   ├── favicon.ico
│   └── og-default.png
└── src/
    ├── layouts/
    │   └── MarketingLayout.astro    # meta, OG, JSON-LD
    ├── components/
    │   ├── Hero.astro
    │   ├── Pricing.astro
    │   └── CtaRegister.astro
    └── pages/
        ├── index.astro
        ├── harga.astro
        ├── fitur.astro
        └── daftar.astro              # form → API Laravel
```

Deploy (mini PC): `npm run build` → `marketing/dist/` → volume Nginx di server yang sama dengan Laravel.

Alternatif: Cloudflare Pages hanya jika nanti pindah marketing off mini PC; **default founder: satu mesin**.

DNS: `platformsekolah.id` + `*` + `admin` → **Cloudflare Tunnel** ke mini PC (bukan A record IP rumah).

---

## 4. Integrasi registrasi (pilih satu di P6)

### Opsi A — Form di Astro → API Laravel (disarankan jangka panjang)

```http
POST https://platformsekolah.id/api/v1/public/register-school
Content-Type: application/json

{ "school_name", "slug", "admin_name", "email", "password" }
```

- Laravel: route `api.php`, throttle, validasi, kirim email verifikasi.
- Response: `{ "redirect": "https://{slug}.platformsekolah.id/onboarding" }`
- Astro: fetch + error handling bahasa Indonesia.
- CORS: allow origin `https://platformsekolah.id` saja.

### Opsi B — CTA link ke Laravel

Tombol di Astro: `href="https://platformsekolah.id/daftar"`  
Satu route Blade minimal di Laravel (boleh Inertia guest satu halaman) — lebih cepat v1, SEO form page di Laravel (Blade) masih OK.

**Rekomendasi:** v1 **Opsi B** cepat; v1.1 migrasi form ke Astro + API.

---

## 5. Checklist SEO (Astro)

- [ ] `output: 'static'` atau hybrid per halaman
- [ ] `<title>`, description, canonical per page
- [ ] `opengraph` + Twitter di `MarketingLayout.astro`
- [ ] JSON-LD `SoftwareApplication` di homepage
- [ ] `sitemap` — `@astrojs/sitemap`
- [ ] `robots.txt` allow `/`, disallow tidak perlu path app
- [ ] Gambar: `@astrojs/image` atau `<img loading="lazy">`
- [ ] `lang="id"`, font subset, LCP &lt; 2.5s
- [ ] Preview deploy Pages per PR

---

## 6. Cloudflare setup

| Record | Target |
|--------|--------|
| `@` | Cloudflare Pages (Astro) |
| `www` | redirect → `@` |
| `*` (wildcard) | Laravel origin (tunnel) |
| `admin` | Laravel origin |

Pages dan tunnel bisa satu akun Cloudflare.

---

## 7. Milestone (tambahan di development plan)

| Task | Fase |
|------|------|
| Framer/Webflow mock landing | Sebelum P6 |
| Scaffold `marketing/` Astro | P0 atau P6 |
| Halaman index + harga + fitur | P6 |
| Wire CTA register ke Laravel | P6 |
| Pages deploy + DNS | P7 |

---

## 8. Biaya & tooling

| Item | Catatan |
|------|---------|
| Framer | Subscription desain |
| Webflow | Subscription opsional |
| Cloudflare Pages | Free tier biasanya cukup |
| Astro | Open source |

---

## 9. Referensi

- [07-deployment-plan.md](./07-deployment-plan.md)
- [06-frontend-plan.md](./06-frontend-plan.md) — shell UI app (bukan marketing)
- [03-project-structure.md](./03-project-structure.md) — update monorepo `marketing/`
