# Cornerbites — Production Deployment Guide

Two sites, two document roots:

| Site | Domain | Document root (cPanel) | Package |
|------|--------|------------------------|---------|
| Website (landing + ordering) | `cornerbites.co.ke` | `/home/<user>/cornerbites.co.ke/` | `dist/cornerbites-website.zip` |
| POS (terminal, admin, kitchen, API) | `pos.cornerbites.co.ke` | `/home/<user>/pos.cornerbites.co.ke/` | `dist/cornerbites-pos.zip` |

The website POSTs customer orders to `https://pos.cornerbites.co.ke/webhooks/website`.

---

## What changed for production (already applied)

1. **`.htaccess`** — added a rewrite so `/webhooks/<platform>` reaches `api/webhooks.php`. Without it the website's order POST 404s.
2. **`api/webhooks.php`** — added CORS + `OPTIONS` preflight handling. The website is on `cornerbites.co.ke` and the POS on `pos.cornerbites.co.ke` (different origins), so the browser blocks the POST unless these headers are present. Allowed origins: the `app_url` in `docs/.config.json` plus `https://cornerbites.co.ke` / `https://www.cornerbites.co.ke`.
3. **`website/index.html`** — the order endpoint now points at `https://pos.cornerbites.co.ke/webhooks/website` (constant `WEBHOOK_URL` at the top of the script) instead of `http://127.0.0.1:8000`.

---

## 1. Prerequisites in cPanel

- **Addon / subdomain** `cornerbites.co.ke` → doc root `/home/<user>/cornerbites.co.ke`
- **Subdomain** `pos.cornerbites.co.ke` → doc root `/home/<user>/pos.cornerbites.co.ke`
- **MySQL database + user** for the POS (note the names; the installer needs them).
- **PHP** ≥ 7.4 with `pdo_mysql`, `curl`, `gd`, `mbstring`, `zip`.
- **Node.js app** (cPanel → Setup Node.js App) for the feed server, pointing at `api/server.js` in the POS doc root. (Optional but recommended for live kitchen updates.)

## 2. Build the packages (already built; rebuild if you change code)

```bash
mkdir -p dist
( cd website && zip -r -q ../dist/cornerbites-website.zip index.html )
zip -r -q dist/cornerbites-pos.zip . \
  -x ".git/*" "wallacepos-master.zip" "website/*" "dist/*" ".opencode/*" \
     "docs/logs/*" "docs/backup/*" "library/wpos/.dbconfig.json" "*.mybroken" "*.DS_Store"
```

## 3. Upload

**Option A — script (recommended):**
```bash
cp deploy/.ftpcreds.example deploy/.ftpcreds   # edit with real FTP details
./deploy/upload.sh --dry-run                    # sanity check
./deploy/upload.sh
```

**Option B — FileZilla / cPanel File Manager:** upload both zips from `dist/` to the cPanel home directory.

## 4. Extract on the server (cPanel → File Manager)

1. Extract `cornerbites-website.zip` into `/home/<user>/cornerbites.co.ke/`.
2. Extract `cornerbites-pos.zip` into `/home/<user>/pos.cornerbites.co.ke/`.
3. Delete the zips afterwards.

## 5. First-time POS install

1. Create an empty file `install.enabled` in the POS doc root.
2. Visit `https://pos.cornerbites.co.ke/installer/` and run the wizard (enter the MySQL details; it writes `library/wpos/.dbconfig.json` and seeds the schema).
3. **Delete `install.enabled`** immediately after.
4. Log in at `https://pos.cornerbites.co.ke/admin/` with `admin` / `admin` and **change the password** (Settings → Staff & Admins).
5. Confirm `docs/.config.json` has `"app_url":"https://cornerbites.co.ke"` (it does in this build).

## 6. Start the feed server (realtime kitchen/POS updates)

In cPanel → Setup Node.js App, create an app with:
- Application root: the POS doc root
- Application URL: `pos.cornerbites.co.ke`
- Application startup file: `api/server.js`

`node_modules/` is already included in the package, so no `npm install` is required. If you prefer, remove `node_modules/` and run `npm install` in the app instead.

## 7. Verify end-to-end

```bash
# Preflight (expect 204 + Access-Control-Allow-Origin)
curl -s -o /dev/null -w "%{http_code}\n" -X OPTIONS \
  https://pos.cornerbites.co.ke/webhooks/website \
  -H "Origin: https://cornerbites.co.ke" \
  -H "Access-Control-Request-Method: POST"

# Live order (expect errorCode OK + a ref)
curl -s -X POST https://pos.cornerbites.co.ke/webhooks/website \
  -H "Content-Type: application/json" \
  -d '{"source":"website","order_type":"pickup","items":[{"name":"Samosa","qty":2,"price":50}],"customer":{"name":"Test","phone":"0712345678"}}'
```

Then open `https://cornerbites.co.ke`, add an item, checkout, and confirm the order appears in the POS/kitchen.

---

## Troubleshooting

- **Order fails with a CORS/network error in the browser** → the POS isn't reachable at `pos.cornerbites.co.ke`, or `.htaccess`/`webhooks.php` weren't uploaded. Re-extract the POS package.
- **404 on `/webhooks/website`** → the root `.htaccess` rewrite is missing (Apache `mod_rewrite` must be enabled).
- **500 / DB error** → `library/wpos/.dbconfig.json` missing or wrong; re-run the installer.
- **No live updates** → the Node feed server isn't running; check the Node.js App in cPanel.
