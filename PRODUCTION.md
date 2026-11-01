# Cornerbites POS — Production Deployment Notes

## Stack
- PHP >= 5.4 with `pdo_mysql`, `curl`, `gd` (tested on PHP 8.5)
- MySQL 8.0 (or MariaDB 10.x) — schema in `library/installer/schemas/install.sql`
- Node.js + socket.io 1.7 (feed server: `api/server.js`)
- Apache 2.4+ with `rewrite`, `proxy_http`, `proxy_wstunnel` (see README virtual host snippet)

## First-time install
1. Upload the app to the document root (e.g. `public_html` on cPanel).
2. Create an empty file `install.enabled` in the document root.
3. Visit `/installer/` and follow the wizard (it writes `library/wpos/.dbconfig.json` and seeds the DB).
4. **Delete `install.enabled`** immediately after install.
5. Log in to `/admin/` with `admin` / `admin` and change the password in Settings → Staff & Admins.
6. Set business details, tax rules, printers and SMTP in Settings.
7. Start the feed server: `node api/server.js` (or via the Utilities page). Keep it running (use `pm2`/`supervisor`/cPanel Node app).

## Config files (never commit)
- `library/wpos/.dbconfig.json` — DB credentials (git-ignored)
- `docs/.config.json` — timezone, SMTP, feed-server port/key (git-ignored; seeded from `docs-template/templates/.config.json`)

## Domain
- Production domain: `cornerbites.co.ke` (https://cornerbites.co.ke)
- CORS on `customerapi/` is restricted to the configured `app_url`.
- No outbound telemetry (the old WallaceIT analytics beacon was removed).

## Security notes
- `display_errors` is Off; errors are logged, not shown.
- `.htaccess` blocks dotfiles and credential/config files.
- The installer is disabled unless `install.enabled` exists.
- Default seed credentials are `admin`/`admin` — change on first login.
- The `staff` account is enabled by default (staff/staff); change the password in Settings -> Staff & Admins if needed.

## Local dev
- `./run-local.sh` starts MySQL + PHP (`php -S`) + the feed server.
- `./run-local.sh stop` stops them.
