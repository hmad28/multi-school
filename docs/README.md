# Dokumentasi — Platform Sekolah

Dokumen perencanaan teknis untuk produk SaaS **Platform Sekolah** (`platformsekolah.id`).

**PRD utama:** [../PRD-cahaya-sunnah-school-system.md](../PRD-cahaya-sunnah-school-system.md) (v2.2)

**Referensi implementasi pilot (klien):** `C:\Projects\project-cahaya-sunnah` — fitur operasional sudah ada; di-port ke SaaS dengan `school_id`.

---

## Plans (`docs/plans/`)

| # | File | Isi |
|---|------|-----|
| 01 | [01-project-scope.md](./plans/01-project-scope.md) | Scope Fase 2 SaaS, pemisahan pilot vs produk, modul |
| 02 | [02-erd-database.md](./plans/02-erd-database.md) | Skema DB multi-tenant, tabel platform, unique constraints |
| 03 | [03-project-structure.md](./plans/03-project-structure.md) | Struktur folder Laravel + Vue, naming |
| 04 | [04-development-plan.md](./plans/04-development-plan.md) | Milestone P0–P6, urutan kerja, definisi selesai |
| 05 | [05-backend-plan.md](./plans/05-backend-plan.md) | Middleware tenant, teams, Actions, platform API |
| 06 | [06-frontend-plan.md](./plans/06-frontend-plan.md) | Layout tenant vs platform, onboarding UI |
| 07 | [07-deployment-plan.md](./plans/07-deployment-plan.md) | Cloud, subdomain, Cloudflare, CI/CD, on-prem opsional |
| 08 | [08-marketing-landing-seo.md](./plans/08-marketing-landing-seo.md) | Landing SEO: Blade vs Inertia vs situs terpisah |

## Architecture decisions (`docs/decisions/`)

| ADR | Topik |
|-----|--------|
| [0001-laravel-inertia-monolith.md](./decisions/0001-laravel-inertia-monolith.md) | Monolith Laravel + Inertia |
| [0002-single-database-school-id.md](./decisions/0002-single-database-school-id.md) | Shared DB + `school_id` |
| [0003-subdomain-tenant-resolution.md](./decisions/0003-subdomain-tenant-resolution.md) | `{slug}.platformsekolah.id` |
| [0004-astro-marketing-site.md](./decisions/0004-astro-marketing-site.md) | Landing Astro + desain Webflow/Framer |
| [0005-home-mini-pc-server.md](./decisions/0005-home-mini-pc-server.md) | Production awal di mini PC + Cloudflare Tunnel |

## Reference (`docs/reference/`)

| File | Isi |
|------|-----|
| [pilot-repo.md](./reference/pilot-repo.md) | Peta modul & path di `project-cahaya-sunnah` |

---

## Cara pakai

1. Baca **01-project-scope** + **PRD** untuk konteks bisnis.
2. **02-erd** + **05-backend** sebelum migrasi database.
3. **04-development-plan** untuk urutan sprint.
4. **07-deployment** saat siap staging/production.

Update dokumen ini ketika ADR atau milestone berubah.
