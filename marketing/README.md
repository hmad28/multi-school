# Platform Sekolah — Marketing (Astro)

Landing & halaman marketing untuk **platformsekolah.id**. Static site — di-deploy ke Nginx mini PC atau Cloudflare Pages.

## Halaman

| Path | File |
|------|------|
| `/` | `src/pages/index.astro` |
| `/harga` | `src/pages/harga.astro` |
| `/daftar` | `src/pages/daftar.astro` (form placeholder → Laravel nanti) |

## Development

```bash
cd marketing
npm install
npm run dev
```

Buka http://localhost:4321

## Build

```bash
npm run build
# output: dist/
```

Copy `dist/` ke volume Nginx di server (lihat `docs/plans/07-deployment-plan.md`).

## Konfigurasi

- `src/config/site.ts` — nama, URL, email, link daftar
- `astro.config.mjs` — `site` untuk sitemap

## Stack

- Astro 6 + Tailwind 4
- `@astrojs/sitemap`
- Font: Fraunces + Figtree (Google Fonts)
