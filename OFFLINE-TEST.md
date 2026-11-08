# Corner Bites POS — Offline Quick-Test (fully local, no internet needed)

All apps run on `127.0.0.1:8000` with the existing `wpos` database. The
realtime feed (`:8080`) is optional — Admin/POS/Kitchen poll `/api/poll.php`
so everything works with the feed down (true offline).

## Start / stop

```bash
./run-local.sh        # foreground; starts MySQL + feed + PHP :8000
./run-local.sh stop   # stop PHP + feed + MySQL
```

Current stack (verified 22:52 EAT): MySQL `127.0.0.1:3306`, PHP `127.0.0.1:8000`, feed `:8080` — all 200.

## URLs + logins

| App | URL | Login |
|---|---|---|
| Staff POS | http://127.0.0.1:8000/ | `staff` / `admin` (via `/staff-login.html`) |
| Admin | http://127.0.0.1:8000/admin/ | `admin` / `admin` |
| Kitchen | http://127.0.0.1:8000/kitchen/ | `admin` / `admin` (setup as kitchen device first) |
| Website (customer) | http://127.0.0.1:8000/website/ | — |
| Delivery test | http://127.0.0.1:8000/website/ | choose Delivery, name+phone+address |
| Dine-in table (e.g. 5) | http://127.0.0.1:8000/website/?table=5#menu | — |
| Table QR plaque (print) | http://127.0.0.1:8000/admin/ → Website → Table QR Codes | `admin` |
| Single QR PNG | http://127.0.0.1:8000/api/table_qr.php?table=3 | public |

Sessions: 12h (`43200`) everywhere; multiple `admin` browsers stay logged in
concurrently (per-session token, no-rotate renew).

## 60-second smoke test

1. **Delivery order** — open `/website/`, add Samosa, checkout as Delivery
   (e.g. `Kenyatta Ave, Utawala`, phone `0712…`). Close the WhatsApp popup
   that opens (offline — expected). Order appears in:
   - Admin Dashboard **Recent Orders** → `Location = Kenyatta Ave, Utawala`
   - Admin **Live Sales Tracker** → `Type=delivery`, Location same, **View**
   - Admin **Website Orders** → Address + Table columns
   - Staff POS **Online Sales** (Tab 5) → Location column
   - Kitchen board → Address + (Table for dine-in)
   - Soft ding + toast on Admin & POS if Sound toggle is On.
2. **Dine-in table order** — open `/website/?table=5#menu`, add Chips Masala,
   checkout (no address needed). Shows as `Table 5` / `[T5]` in Dashboard,
   Tracker (Type=`dinein`), Website Orders (Table), POS Online Sales,
   Kitchen (`Table: Table 5`).
3. **View dialog** — Live Tracker → **View** any website row. Shows:
   `Source: Website (queued)`, `Order Type: delivery/dinein`,
   `Delivery: <address>`, `Table: Table 5/-`, `External: <ref>`,
   `Location: Utawala` (store), plus embedded Customer name/phone/address
   (online orders have no `customers` row, so they use the sale JSON).
4. **POS sale → Sales** — POS Till: add item, Process Sale (M-Pesa is
   default). Appears immediately in Sales tab (Tab 2) as `synced`.
5. **Complete online order** — POS Online Sales → **Complete & Move to Sales**
   (or Admin → Preparing/Ready/Done). It stays visible in Tracker as
   Completed and in Reports; payment defaults to M-Pesa.
6. **Offline check** — stop the feed (or pull Ethernet): `./run-local.sh stop`
   then `./run-local.sh` again. POS Till keeps selling (offline queue),
   `/api/poll.php` still returns orders, `/api/menu.php` still serves products.

## API quick-test (no browser)

Delivery:

```bash
curl -s -X POST "http://127.0.0.1:8000/api/webhooks.php?platform=website" \
  -H "Content-Type: application/json" \
  -d '{"order_type":"delivery","delivery_address":"Kenyatta Ave, Utawala","customer":{"name":"Offline Tester","phone":"0712345678"},"items":[{"name":"Samosa","qty":1,"price":50}],"external_ref":"OFFLINE-TEST-001"}'
```

Dine-in table 7:

```bash
curl -s -X POST "http://127.0.0.1:8000/api/webhooks.php?platform=website" \
  -H "Content-Type: application/json" \
  -d '{"order_type":"dinein","table_number":7,"customer":{"name":"Table Tester","phone":"0712345678"},"items":[{"name":"Chips Masala","qty":1,"price":180}],"external_ref":"OFFLINE-TEST-002"}'
```

Verified live (IDs 32/33): delivery `Kenyatta Ave, Utawala` + `Table 5`
appear in poll, `sales/get`, `transactions/get`, `orders/get`, Dashboard,
Tracker, Website Orders, POS Online Sales, and the View dialog.

Notes: website food photos load from Wikimedia (blank offline — menu/ordering
still work). Checkout opens `wa.me` — just close it offline.
