# ADR 0002: Single database dengan school_id

**Status:** Accepted  
**Tanggal:** 30 Mei 2026

## Keputusan

Multi-tenant **shared database**, isolasi via kolom **`school_id`** + Global Scope + Spatie teams.

## Alasan

- &lt; 500 sekolah target awal — operasi sederhana (satu migrasi, satu backup schema).
- Biaya infrastruktur rendah vs DB per tenant.
- Port dari pilot = tambah kolom + ubah unique indexes.

## Keamanan wajib

- Automated **tenant isolation** tests.
- `BelongsToSchool` di semua model domain.
- Super-admin akses tenant hanya dengan `TenantContext` eksplisit + audit.

## Alternatif (deferred)

- **DB per tenant** — jika compliance atau &gt; 500 sekolah.
- **Schema per tenant** — kompleksitas migrasi Laravel tinggi.

## Konsekuensi

- Satu query salah scope = risiko tinggi → mitigasi test + code review.
- Backup full DB; restore per tenant butuh script filter (atau logical export per `school_id`).
