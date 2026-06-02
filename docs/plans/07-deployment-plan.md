# Deployment Plan — Platform Sekolah

> **Goal:** Deploy SaaS multi-tenant dengan subdomain + Cloudflare; **server utama kemungkinan mini PC** founder (lihat [ADR 0005](../decisions/0005-home-mini-pc-server.md)).

---

## 1. Mode deploy

| Mode | Target | Catatan |
|------|--------|---------|
| **A — Home mini PC** ⭐ | Production awal Platform Sekolah | Docker + **Cloudflare Tunnel** wajib |
| **B — VPS cloud** | Scale / SLA lebih tinggi | Migrasi later dari A |
| **C — On-site LAN** | Satu sekolah (client Cahaya Sunnah) | Pilot: `project-cahaya-sunnah/docs/plans/07-deployment-plan.md` |

---

## 2. Arsitektur production — mini PC (rencana utama)

```text
Internet
    ↓
Cloudflare DNS + SSL (edge)
    ↓
cloudflared (tunnel dari mini PC — tanpa buka port 443 di router)
    ↓
┌─────────────────────────────────────────────┐
│  Mini PC (Windows/Linux + Docker)           │
│  ├── nginx                                  │
│  │   ├── platformsekolah.id → Astro dist/   │
│  │   └── *.platformsekolah.id → Laravel     │
│  ├── app (PHP 8.4-FPM + Laravel)            │
│  ├── mysql 8.4                              │
│  └── redis (+ horizon container opsional)   │
└─────────────────────────────────────────────┘
```

**Landing Astro:** `npm run build` → salin `marketing/dist/` ke volume Nginx (bukan wajib Cloudflare Pages).

**File upload siswa:** disk lokal mini PC awalnya; **R2/S3 disarankan** sebelum banyak sekolah (hemat backup & bandwidth upload).

### 2.1 Spesifikasi mini PC (minimum)

| Komponen | Rekomendasi |
|----------|-------------|
| RAM | 16 GB (8 GB minimum untuk dev kecil) |
| Storage | SSD 256 GB+ |
| OS | **Linux** — lihat [§2.2](#22-pilihan-linux-mini-pc) |
| Jaringan | Ethernet ke router (bukan WiFi untuk uptime) |
| Listrik | UPS kecil |

### 2.2 Pilihan Linux (mini PC)

| Distro | Cocok untuk server 24/7? | Catatan |
|--------|--------------------------|---------|
| **Ubuntu Server 24.04 LTS** | ⭐ Paling umum | Support ~5 tahun, tutorial Docker paling banyak |
| **Debian 12** | ⭐ Sangat stabil | Konservatif, ringan, jarang breaking change |
| Fedora Server | Opsi (tidak dipilih) | Support pendek; kurang familiar |
| Rocky / Alma 9 | Opsi | Enterprise-style |

**Terpilih: Ubuntu Server 24.04 LTS** — support panjang, dokumentasi Docker/Laravel paling banyak, cocok homelab pertama.

#### Setup awal Ubuntu Server (mini PC)

1. Install **Ubuntu Server 24.04 LTS** — centang OpenSSH server, **tanpa** snap desktop.
2. Update:

```bash
sudo apt update && sudo apt upgrade -y
```

3. Install Docker (official):

```bash
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker $USER
```

4. Firewall (UFW): default deny; tunnel Cloudflare = **tidak perlu** buka 80/443 ke internet (traffic masuk via `cloudflared` outbound).

```bash
sudo ufw allow OpenSSH
sudo ufw enable
```

5. Pasang **cloudflared** (Cloudflare Tunnel) — container di `docker-compose` atau paket `.deb` dari Cloudflare.
6. Aktifkan **unattended-upgrades** untuk patch keamanan:

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

7. Timezone: `sudo timedatectl set-timezone Asia/Jakarta`

**AppArmor** (default Ubuntu): biasanya cocok dengan Docker; jarang masalah dibanding SELinux Fedora.

### 2.2 Yang tidak dilakukan di rumah (tanpa mitigasi)

- Port forward 80/443 ke router (risiko keamanan + IP dinamis).
- Satu copy DB tanpa backup off-site.
- Janji SLA 99.5% seperti data center.

---

## 2b. Arsitektur alternatif — VPS cloud

```text
Internet → Cloudflare → VPS
├── Nginx → PHP-FPM (Laravel)
├── MySQL 8
├── Redis + Horizon
└── Astro: Pages ATAU static di Nginx VPS

Object storage: R2 / S3
```

Gunakan saat: &gt; ~20 sekolah aktif, butuh uptime, atau upload rumah bottleneck.

---

## 3. Domain & DNS (terkonfirmasi)

**Domain produk:** `platformsekolah.id` (rencana — beli saat go-live)

| Record | Tipe | Target |
|--------|------|--------|
| `@` | A / CNAME | VPS atau tunnel |
| `www` | CNAME | `@` |
| `admin` | CNAME | same origin |
| `*` | CNAME | same origin (wildcard tenant) |

**URL:**

```text
https://platformsekolah.id              → landing + register
https://admin.platformsekolah.id      → super-admin
https://{slug}.platformsekolah.id     → tenant app
```

### 3.1 Cloudflare Tunnel (**wajib untuk mini PC**)

- Mini PC di belakang NAT — **tidak** buka port inbound di router.
- Container `cloudflared` di Docker Compose → `http://nginx:80`.
- Satu tunnel, banyak **Public Hostname**:
  - `platformsekolah.id` → nginx
  - `admin.platformsekolah.id` → nginx (server_name)
  - `*.platformsekolah.id` → nginx (wildcard → Laravel tenant resolver)
- SSL di edge Cloudflare (Full).

Referensi pilot: `project-cahaya-sunnah/docker/cloudflared/`

### 3.2 Laravel config

```env
APP_URL=https://platformsekolah.id
TENANT_BASE_DOMAIN=platformsekolah.id
SESSION_DOMAIN=.platformsekolah.id
SANCTUM_STATEFUL_DOMAINS=*.platformsekolah.id,platformsekolah.id
```

Cookie session domain `.platformsekolah.id` agar auth tidak bocor antar subdomain tenant (isolasi session per host lebih aman — prefer **session per full host** tanpa shared cookie; default Laravel: satu cookie per subdomain host).

**Rekomendasi:** Session **per host** (default) — tidak set `SESSION_DOMAIN` shared; user login terpisah per subdomain.

---

## 4. Environment production

```env
APP_NAME="Platform Sekolah"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://platformsekolah.id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=platform_sekolah
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

FILESYSTEM_DISK=s3
AWS_BUCKET=platform-sekolah-prod
# atau R2 equivalent

MAIL_MAILER=resend
MAIL_FROM_ADDRESS=noreply@platformsekolah.id

SENTRY_LARAVEL_DSN=...
```

Rules:

- `APP_DEBUG=false` wajib.
- Jangan commit `.env`.
- Rotate `APP_KEY` hanya dengan re-encrypt plan.

---

## 5. Docker Compose (dev & mini PC prod)

```text
docker-compose.yml
├── nginx          # Astro dist + Laravel, vhost wildcard
├── app            # PHP 8.4-FPM
├── mysql
├── redis
├── horizon        # production queue (opsional awal: sync)
└── cloudflared    # production mini PC
```

**Volume:**

```text
./marketing/dist  → /var/www/marketing (read-only)
./storage         → Laravel storage
```

Dev lokal: port `8011` seperti pilot. Prod tunnel: tidak perlu expose port ke LAN publik.

---

## 6. CI/CD & deploy ke mini PC

**CI (GitHub Actions):** test + build artifacts.

```yaml
# ringkas
jobs:
  test:
    - composer install && php artisan test
    - cd marketing && npm ci && npm run build
    - npm run build (Laravel Vite)
  artifact:
    - upload marketing/dist + app tarball
```

**CD ke mini PC (pilih salah satu):**

| Cara | Cocok untuk |
|------|-------------|
| **SSH + git pull** + `docker compose up -d --build` | Mini PC Linux, akses SSH |
| **Watchtower** + registry image | Build image di CI, pull di PC |
| **Manual** deploy awal | Fase pertama, 1 orang |

Tidak wajib Vercel/Forge jika server di rumah.

Staging: subdomain `staging.platformsekolah.id` → tunnel hostname kedua atau env terpisah di PC yang sama.

---

## 7. SSL & security

- Cloudflare SSL Full (strict) + origin cert atau Let's Encrypt.
- Rate limit login: Laravel throttle + Cloudflare rule.
- `TrustProxies` untuk Cloudflare IP.
- Backup DB harian encrypted; retensi 30 hari.
- Super-admin: IP allowlist opsional di Cloudflare Access.

---

## 8. Backup & restore

| Item | Metode |
|------|--------|
| Database | `mysqldump` nightly → S3 private |
| Tenant files | S3 versioning |
| Restore | Runbook: restore DB + R2 prefix per `school_id` |

Pilot client on-prem: tetap SOP manual di `project-cahaya-sunnah/docs/handover/backup-restore-sop.md`.

---

## 9. Monitoring

| Tool | Fungsi |
|------|--------|
| Sentry | PHP/JS errors |
| Uptime Robot | HTTPS check admin + 1 tenant |
| Laravel logs | `LOG_LEVEL=warning` → centralized optional |

Alert: backup gagal, queue stalled, disk full.

---

## 10. Pre-launch checklist (mini PC)

- [ ] Mini PC: SSD, RAM 16 GB, Ethernet, UPS
- [ ] Docker + Compose jalan stabil 24/7
- [ ] `cloudflared` tunnel healthy (dashboard Zero Trust)
- [ ] Domain `platformsekolah.id` aktif → CNAME tunnel (wildcard `*`)
- [ ] Astro `dist/` ter-deploy di Nginx
- [ ] `php artisan migrate --force` production
- [ ] Seed `super-admin` + permissions
- [ ] Tenant isolation tests pass di CI
- [ ] Trial job scheduled (scheduler cron)
- [ ] Email verifikasi terkirim
- [ ] Smoke: register → onboarding → input absensi
- [ ] Privacy: isolasi 2 tenant manual test

---

## 11. On-prem (mode B) — ringkas

Untuk sekolah yang minta server di lokasi:

1. Gunakan build pilot `project-cahaya-sunnah` atau single-tenant flag di Platform Sekolah.
2. Deploy Docker di PC Windows sekolah (`WEB_PORT=8000`).
3. Tidak perlu wildcard DNS; `APP_URL=http://<LAN-IP>:8000`.
4. Maintenance kontrak terpisah dari SaaS.

---

## 12. Referensi

- PRD §8.3–8.6
- Pilot deployment: `project-cahaya-sunnah/docs/handover/deployment-checklist.md`
