# Corner Bites POS — Full Setup & Deployment Guide

## 1. Repository

- **GitHub:** https://github.com/sidwashere/cornerbitesPOS
- **Branch:** `main`
- **Website lives inside the repo** at `website/` (deployed separately to `cornerbites.co.ke`)

## 2. Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x (front controllers: `api/wpos.php`, `api/webhooks.php`, `api/poll.php`) |
| Database | MySQL / MariaDB (PDO) |
| Web server | Apache (`.htaccess` routing) or PHP built-in server for local dev |
| Frontend (POS) | jQuery 2.2, jQuery UI, Bootstrap, DataTables, custom JS in `assets/js/wpos/` |
| Website | Static HTML + Tailwind (CDN), no build step |
| Dependencies | Composer (`composer.json` / `composer.lock`) |

## 3. Local Development Setup

### Prerequisites
- PHP 8.x with PDO MySQL extension
- MySQL 8 / MariaDB 10.5+
- Apache (optional; PHP built-in server works for dev)
- Composer

### Steps

1. **Clone the repo:**
   ```bash
   git clone https://github.com/sidwashere/cornerbitesPOS.git
   cd cornerbitesPOS
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Create the database:**
   ```sql
   CREATE DATABASE cornerbites_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Import the schema:**
   ```bash
   mysql -u root -p cornerbites_pos < library/installer/schemas/install.sql
   ```
   (Or run the web installer at `/installer/`)

5. **Configure DB credentials** — create `library/wpos/.dbconfig.json`:
   ```json
   {
     "host": "localhost",
     "port": 3306,
     "database": "cornerbites_pos",
     "username": "root",
     "password": "your_password"
   }
   ```
   This file is gitignored — never commit it.

6. **Run locally:**
   ```bash
   php -S localhost:8000 router.php
   ```
   Then open http://localhost:8000

7. **POS auto-login:** The POS auto-logs in as `staff` / `admin` (password `admin` is hashed by AuthModel).

8. **Admin panel:** https://localhost:8000/admin/ — login with `staff` / `admin`.

## 4. Key Configuration Files

| File | Purpose | Gitignored? |
|------|---------|-------------|
| `library/wpos/.dbconfig.json` | DB credentials | Yes |
| `deploy/.ftpcreds` | FTP deploy credentials | Yes |
| `docs/.config.json` | Timezone, app URL, etc. | Yes |
| `deploy/.lastdeploy-pos` | Last deployed commit (POS) | Yes |
| `deploy/.lastdeploy-web` | Last deployed commit (Web) | Yes |

## 5. Deployment

### Deploy script: `deploy/push.sh`

```bash
# POS only (changed files since last push)
./deploy/push.sh pos

# Website only (full mirror)
./deploy/push.sh web --all

# Both
./deploy/push.sh all

# Dry run (preview only)
./deploy/push.sh pos --dry-run
```

### FTP Credentials (`deploy/.ftpcreds`)

```bash
FTP_HOST="ftp.cornerbites.co.ke"
FTP_PORT="21"
POS_USER="cbpos@pos.cornerbites.co.ke"
POS_PASS="8adc65ae2b8ec370a7138a24"
WEB_USER="cbweb@cornerbites.co.ke"
WEB_PASS="25276286ba56f3f04ccf071e"
```

### Critical deployment notes

1. **Two separate FTP trees:**
   - POS → `/home/mllbontz/home/mllbontz/pos.cornerbites.co.ke` (nested legacy path — the REAL doc root)
   - Web → `/home/mllbontz/cornerbites.co.ke` (top-level)
   - cPanel's claimed POS doc root is stale; always use the deploy script.

2. **`website/` folder is excluded from POS deploys** and is the source for web deploys.

3. **`library/wpos/.dbconfig.json` and `docs/` are never pushed.**

4. **Nothing is deleted on the server** — uploads only.

## 6. Database Reset (dev only)

`reset_data.php` clears transactional tables while preserving `stored_items`, `stored_suppliers`, `stored_categories`, `tax_items`, `tax_rules`, and `config`. It seeds fresh `auth` (staff admin), location `utawala`, device `general register`, and `device_map`.

**Do NOT run on production.**

## 7. Key URLs

| Service | URL |
|---------|-----|
| POS | https://pos.cornerbites.co.ke/ |
| Admin | https://pos.cornerbites.co.ke/admin/ |
| Website | https://cornerbites.co.ke/ |
| Webhook (website orders) | https://pos.cornerbites.co.ke/api/webhooks.php?platform=website |

## 8. Default Credentials

| User | Password | Role |
|------|----------|------|
| `staff` | `admin` | Admin (auto-login on POS; manual on admin panel) |

## 9. Device UUID (fixed)

`483d76551020146d79c50ab68334a3680c84456090812738b252bedbcfb7e5ad`

## 10. Important: Rotate Credentials

The FTP passwords and any DB passwords in this setup must be rotated before production use. The credentials shared here are for development replication only.
