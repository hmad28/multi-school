# ADR 0005: Production di home mini PC

**Status:** Accepted (kemungkinan besar)  
**Tanggal:** 30 Mei 2026  
**Konteks:** Founder deploy sendiri, belum VPS cloud di awal.

## Keputusan

**Server production awal:** mini PC di rumah/kantor founder, menjalankan:

- Docker Compose: Nginx, PHP-FPM (Laravel), MySQL, Redis, Horizon (opsional)
- **Cloudflare Tunnel** (`cloudflared`) — wajib untuk akses publik tanpa buka port router & IP dinamis
- Landing **Astro** (`marketing/dist/`) di-serve **Nginx same host** (bukan wajib Cloudflare Pages)

VPS cloud = **opsional nanti** saat traffic atau SLA sekolah menuntut.

## Arsitektur

```text
Internet
    ↓
Cloudflare (DNS + SSL termination di edge)
    ↓
cloudflared tunnel (keluar dari mini PC)
    ↓
Mini PC
├── Nginx
│   ├── /           → Astro static (platformsekolah.id)
│   ├── /api        → Laravel (opsional prefix)
│   └── tenant vhost / server_name wildcard → Laravel
├── PHP-FPM + Laravel
├── MySQL
└── Redis
```

DNS: semua hostname (`@`, `*`, `admin`) → CNAME ke tunnel Cloudflare.

## Alasan

- Biaya rendah di fase awal (trial, &lt; 10 sekolah).
- Sudah familiar dari pilot Docker di PC sekolah.
- Kontrol penuh data di hardware sendiri.

## Risiko & mitigasi

| Risiko | Mitigasi |
|--------|----------|
| Listrik mati | UPS mini; status page; komunikasi ke sekolah |
| Internet rumah down | Provider backup / notifikasi; SLA jujur “best effort” |
| Upload bandwidth kecil | Kompres gambar; R2 untuk file siswa opsional |
| IP tidak statis | Tunnel Cloudflare, bukan port forward |
| Hardware gagal | Backup DB harian off-device (S3/R2/NAS) |
| Tidak “data center” | Founding school tier; migrasi VPS documented |

## OS

**Terpilih:** **Ubuntu Server 24.04 LTS** (mini PC). Awalnya dipertimbangkan Fedora; diganti karena belum familiar + ingin support panjang & tutorial Docker banyak.

Detail: [07-deployment-plan.md](../plans/07-deployment-plan.md) §2.2.

## Konsekuensi produk

- SLA marketing: **best effort** 99% (bukan 99.5% data center) sampai pindah VPS.
- Backup **wajib** keluar dari mini PC.
- Monitoring: Uptime Kuma / healthcheck + alert WA pribadi.

## Astro + Webflow/Framer

Tetap build Astro di CI atau lokal → copy `dist/` ke mini PC atau mount volume Nginx. Tidak bergantung Cloudflare Pages.

## Tidak mengubah

- Subdomain tenant `{slug}.platformsekolah.id`
- Laravel + Inertia untuk app
- Spatie teams + `school_id`
