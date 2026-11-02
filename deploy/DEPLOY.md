# Cornerbites — Production Deployment Guide

Two sites, two document roots — **and they live in DIFFERENT trees of the same
cPanel account** (discovered 2026-11-02, this is critical):

| Site | Domain | REAL document root (served by the web node) |
|------|--------|---------------------------------------------|
| Website (landing + ordering) | `cornerbites.co.ke` | `/home/mllbontz/cornerbites.co.ke/` |
| POS (terminal, admin, kitchen, API) | `pos.cornerbites.co.ke` | `/home/mllbontz/home/mllbontz/pos.cornerbites.co.ke/` (nested!) |

> **WARNING:** cPanel's "Domains" page claims the POS doc root is
> `/home/mllbontz/pos.cornerbites.co.ke`. That is a **stale, unserved copy**
> (an artifact of an account migration). Writing there — via cPanel File
> Manager, UAPI, or an FTP account scoped there — has **no effect on the live
> site**. The live web server (das125b, 102.212.247.115) reads the nested path
> above. Always deploy with `deploy/push.sh`, which uses FTP accounts scoped
> to the correct trees.

The website POSTs customer orders to `https://pos.cornerbites.co.ke/webhooks/website`.

---

## Deploy with push.sh (recommended)

```bash
cp deploy/.ftpcreds.example deploy/.ftpcreds   # one-time: fill in real FTP credentials

./deploy/push.sh pos            # push committed POS changes since the last push
./deploy/push.sh web            # push committed website changes since the last push
./deploy/push.sh all            # both
./deploy/push.sh pos --dry-run  # preview only
./deploy/push.sh pos <commit>   # push everything changed since <commit>
./deploy/push.sh pos --all      # full mirror resync (slower, thorough)
```

How it works:

1. `push.sh` diffs git (`<last-deploy-marker>..HEAD`), so **only committed,
   tracked, changed files** are uploaded — fast and precise, no local junk,
   no zips, no secrets (`library/wpos/.dbconfig.json` and `docs/` are
   gitignored and can never be pushed).
2. Files go over FTP (lftp) straight into the **real** doc root of the target
   site, using per-site FTP accounts:
   - `cbpos@pos.cornerbites.co.ke` → scoped to the nested POS doc root
   - `cbweb@cornerbites.co.ke` → scoped to the website doc root
3. Every upload is **size-verified**; the deploy marker
   (`deploy/.lastdeploy-<target>`) is only advanced after verification passes.
4. Nothing is ever deleted on the server.

FTP accounts are managed in cPanel → FTP Accounts (or the API2 `Ftp/addftp`
endpoint, which this hosting supports even though the UAPI `Ftp` module is
stripped). When creating the POS account, set its directory to
`home/mllbontz/pos.cornerbites.co.ke` (the nested path).

### After changing admin JS

When `admin/assets/wpos/wpos.js` changes, bump the `?v=` cache-buster on its
`<script>` tag in `admin/index.html` (e.g. `?v=20261102`) so staff browsers
fetch fresh code instead of a cached copy.

---

## Legacy zip workflow (superseded — kept for reference)

1. Build packages:
   ```bash
   mkdir -p dist
   ( cd website && zip -r -q ../dist/cornerbites-website.zip index.html )
   zip -r -q dist/cornerbites-pos.zip . \
     -x ".git/*" "wallacepos-master.zip" "website/*" "dist/*" ".opencode/*" \
        "docs/logs/*" "docs/backup/*" "library/wpos/.dbconfig.json" "*.mybroken" "*.DS_Store"
   ```
2. Upload both zips to the cPanel home directory (FTP or File Manager).
3. Extract `cornerbites-website.zip` into `/home/mllbontz/cornerbites.co.ke/`.
4. Extract `cornerbites-pos.zip` into `/home/mllbontz/home/mllbontz/pos.cornerbites.co.ke/`
   (**the nested path** — extracting into the top-level `pos.cornerbites.co.ke/`
   will NOT update the live site).
5. Delete the zips afterwards.

## First-time POS install

1. Create an empty file `install.enabled` in the POS doc root.
2. Visit `https://pos.cornerbites.co.ke/installer/` and run the wizard (enter the MySQL details; it writes `library/wpos/.dbconfig.json` and seeds the schema).
3. **Delete `install.enabled`** immediately after.
4. Log in at `https://pos.cornerbites.co.ke/admin/` with `admin` / `admin` and **change the password** (Settings → Staff & Admins).
5. Confirm `docs/.config.json` has `"app_url":"https://cornerbites.co.ke"`.

## Verify end-to-end

```bash
# Preflight (expect 204 + Access-Control-Allow-Origin)
curl -s -o /dev/null -w "%{http_code}\n" -X OPTIONS \
  https://pos.cornerbites.co.ke/webhooks/website \
  -H "Origin: https://cornerbites.co.ke" \
  -H "Access-Control-Request-Method: POST"

# Staff login (expect errorCode OK)
curl -s "https://pos.cornerbites.co.ke/api/wpos.php?a=auth&data=%7B%22username%22%3A%22staff%22%2C%22password%22%3A%22<sha256-of-password>%22%7D"
```

Then open `https://cornerbites.co.ke`, add an item, checkout, and confirm the
order appears in the POS admin dashboard (All Orders / Website Orders tabs).

---

## Troubleshooting

- **Deployed but the live site didn't change** → you deployed into the stale
  top-level `pos.cornerbites.co.ke/` tree. Use `deploy/push.sh pos`, which
  targets the real nested doc root.
- **Order fails with a CORS/network error in the browser** → the POS isn't
  reachable at `pos.cornerbites.co.ke`, or `.htaccess`/`webhooks.php` weren't
  uploaded. Re-run `./deploy/push.sh pos`.
- **404 on `/webhooks/website`** → the root `.htaccess` rewrite is missing
  (Apache `mod_rewrite` must be enabled).
- **500 / DB error** → `library/wpos/.dbconfig.json` missing or wrong; re-run
  the installer.
- **Admin dashboard orders table stuck on "Loading..."** → a JS error in
  `admin/content/dashboard.php`; check the browser console, fix, and
  `./deploy/push.sh pos`.
- **FTP login rejected** → verify the account in cPanel → FTP Accounts, and
  that `deploy/.ftpcreds` matches (usernames are full email form, e.g.
  `cbpos@pos.cornerbites.co.ke`).
