# ADR 0003: Subdomain tenant resolution

**Status:** Accepted  
**Tanggal:** 30 Mei 2026

## Keputusan

Setiap sekolah (tenant) diakses via:

```text
https://{slug}.platformsekolah.id
```

Resolusi tenant dari **HTTP Host** → `schools.slug`.

Dashboard founder:

```text
https://admin.platformsekolah.id
```

Landing & registrasi:

```text
https://platformsekolah.id
```

## Alasan

- Branding per sekolah di URL (TU bisa bookmark subdomain sendiri).
- Cocok dengan **Cloudflare wildcard DNS** + tunnel.
- Session natural per host (tidak perlu path prefix `/s/{slug}`).

## Implementasi

- Middleware `SetCurrentSchool` + `TenantResolver`.
- Slug unik saat registrasi (validasi alphanumeric + hyphen).
- Dev lokal: `*.platformsekolah.test` via hosts file atau Laravel Valet.

## Alternatif yang ditolak (v1)

- Path-based `/s/{slug}` — kurang branding; tetap bisa fallback marketing.
- Pilih sekolah setelah login — tidak diperlukan v1 (satu email = satu sekolah).

## Infrastruktur

- Wildcard DNS `*.platformsekolah.id` → origin.
- Domain belum dibeli — development dengan `.test` atau tunnel hostname sementara.
