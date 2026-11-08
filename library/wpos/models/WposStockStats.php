<?php
/**
 * WposStockStats — inventory dashboard aggregates + item detail setters.
 * Stock value = Σ(stocklevel × cost), cost read from stored_items.data JSON.
 */
class WposStockStats {
    private $data;
    function __construct($data = null) { $this->data = $data !== null ? $data : new stdClass(); }
    private function db() { return (new DbConfig())->_db; }

    private function itemCost($dataJson) {
        $d = json_decode($dataJson ?: '{}', true);
        return (float)($d['cost'] ?? 0);
    }

    /** Lightweight field updater for an item: name / price / image (kept in data JSON). */
    public function setDetails($result) {
        $itemId = isset($this->data->itemid) ? (int)$this->data->itemid : (isset($this->data->id) ? (int)$this->data->id : 0);
        if ($itemId <= 0) { $result['error'] = 'itemid required'; return $result; }
        $pdo = $this->db();
        $row = $pdo->prepare("SELECT name, price, data FROM stored_items WHERE id = :id");
        $row->execute([':id' => $itemId]);
        $cur = $row->fetch(PDO::FETCH_ASSOC);
        if (!$cur) { $result['error'] = 'Item not found'; return $result; }
        $data = json_decode($cur['data'] ?: '{}', true);
        if (!is_array($data)) $data = [];
        if (isset($this->data->name) && trim((string)$this->data->name) !== '') $cur['name'] = trim((string)$this->data->name);
        if (isset($this->data->price) && is_numeric($this->data->price)) $cur['price'] = (string)number_format((float)$this->data->price, 2, '.', '');
        if (isset($this->data->image)) $data['image'] = trim((string)$this->data->image);
        $pdo->prepare("UPDATE stored_items SET name = :n, price = :p, data = :d WHERE id = :id")->execute([':n' => $cur['name'], ':p' => $cur['price'], ':d' => json_encode($data), ':id' => $itemId]);
        Logger::write("Item $itemId details updated (image/price/name)", "ITEM");
        $result['data'] = ['itemid' => $itemId, 'name' => $cur['name'], 'price' => (float)$cur['price'], 'image' => $data['image'] ?? ''];
        return $result;
    }

    /** Compute category images: manual image wins; else the top-selling product's image (30d units sold). */
    public function categoryImages($result) {
        $pdo = $this->db();
        $cats = $pdo->query("SELECT id, name, image FROM stored_categories ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        // units sold per item (30d) + item images
        $sold = [];
        foreach ($pdo->query("SELECT storeditemid, COALESCE(SUM(amount),0) AS sold FROM stock_history WHERE auxdir = 2 AND dt >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY storeditemid")->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sold[(int)$r['storeditemid']] = (float)$r['sold'];
        }
        $items = $pdo->query("SELECT id, name, categoryid, data FROM stored_items ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $byCat = [];
        foreach ($items as $it) {
            $cid = (int)$it['categoryid'];
            $d = json_decode($it['data'] ?: '{}', true);
            $byCat[$cid][] = ['id' => (int)$it['id'], 'name' => $it['name'], 'image' => (string)($d['image'] ?? ''), 'sold' => $sold[(int)$it['id']] ?? 0];
        }
        $out = [];
        foreach ($cats as $c) {
            $list = $byCat[(int)$c['id']] ?? [];
            $topImg = '';
            $best = -1;
            foreach ($list as $it) {
                if ($it['image'] === '' || $it['sold'] < 0) continue;
                if ($it['sold'] >= $best) { $best = $it['sold']; $topImg = $it['image']; }
            }
            $out[] = [
                'id' => (int)$c['id'], 'name' => $c['name'],
                'image' => (string)$c['image'],
                'top_seller_image' => $topImg,
                'effective_image' => (string)$c['image'] !== '' ? (string)$c['image'] : $topImg,
                'items' => count($list),
            ];
        }
        $result['data'] = $out;
        return $result;
    }

    /** Set a category image (manual override; tray falls back to top seller when empty). */
    public function setCategoryImage($result) {
        $id = isset($this->data->id) ? (int)$this->data->id : (isset($this->data->categoryid) ? (int)$this->data->categoryid : 0);
        $image = trim((string)($this->data->image ?? ''));
        if ($id <= 0) { $result['error'] = 'category id required'; return $result; }
        $m = new CategoriesModel();
        $m->setImage($id, $image);
        $result['data'] = ['id' => $id, 'image' => $image];
        return $result;
    }

    /** Create a POS product from a website menu item (complete menu <-> POS sync). */
    public function createFromMenu($result) {
        $name = trim((string)($this->data->name ?? ''));
        $price = isset($this->data->price) ? (float)$this->data->price : 0;
        $catLabel = trim((string)($this->data->category_label ?? $this->data->category ?? ''));
        $image = trim((string)($this->data->image ?? ''));
        if ($name === '' || $price <= 0) { $result['error'] = 'name and price>0 required'; return $result; }
        $pdo = $this->db();
        $ex = $pdo->prepare("SELECT id FROM stored_items WHERE lower(name) = lower(:n) LIMIT 1");
        $ex->execute([':n' => $name]);
        if ($id = $ex->fetchColumn()) {
            // Already exists — just sync image
            $this->data->itemid = (int)$id;
            $this->data->image = $image;
            return $this->setDetails($result);
        }
        // category (create if needed)
        $catId = 0;
        if ($catLabel !== '') {
            $c = $pdo->prepare("SELECT id FROM stored_categories WHERE lower(name) = lower(:n) LIMIT 1");
            $c->execute([':n' => $catLabel]);
            $catId = (int)$c->fetchColumn();
            if ($catId === 0) {
                $pdo->prepare("INSERT INTO stored_categories (name) VALUES (:n)")->execute([':n' => $catLabel]);
                $catId = (int)$pdo->lastInsertId();
            }
        }
        $code = 'CB' . strtoupper(substr(md5($name . $price), 0, 8));
        $obj = new stdClass();
        $obj->code = $code; $obj->qty = 100; $obj->name = $name; $obj->description = '';
        $obj->taxid = 1; $obj->cost = $price; $obj->price = $price; $obj->type = '';
        $obj->supplierid = 0; $obj->categoryid = $catId;
        if ($image !== '') $obj->image = $image;
        $itemMdl = new StoredItemsModel();
        $result['data'] = $itemMdl->create($obj);
        // default stock level at default location
        $loc = $pdo->query("SELECT id FROM locations ORDER BY id LIMIT 1")->fetchColumn();
        if ($loc && is_numeric($result['data'])) {
            try { $pdo->prepare("INSERT INTO stock_levels (storeditemid, locationid, stocklevel) VALUES (:i,:l,0)")->execute([':i' => (int)$result['data'], ':l' => (int)$loc]); } catch (Exception $e) {}
        }
        Logger::write("Menu item created as POS product: $name", "ITEM");
        return $result;
    }

    /** Dashboard: KPIs, value by supplier, low-stock, movement stats, recent moves. */
    public function stats($result) {
        $pdo = $this->db();
        $items = $pdo->query("SELECT id, name, supplierid, code, price, data FROM stored_items ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $pdo->query("SELECT storeditemid, SUM(stocklevel) AS qty FROM stock_levels GROUP BY storeditemid")->fetchAll(PDO::FETCH_ASSOC);
        $qtyMap = [];
        foreach ($levels as $l) $qtyMap[(int)$l['storeditemid']] = (int)$l['qty'];

        $supNames = [];
        foreach ($pdo->query("SELECT id, name FROM stored_suppliers")->fetchAll(PDO::FETCH_ASSOC) as $s) $supNames[(int)$s['id']] = $s['name'];

        $sku = count($items); $units = 0; $value = 0.0;
        $out = 0; $low = 0; $lowList = []; $bySup = [];
        foreach ($items as $it) {
            $id = (int)$it['id'];
            $qty = $qtyMap[$id] ?? 0;
            $cost = $this->itemCost($it['data']);
            $full = $qty * $cost;
            $units += $qty; $value += $full;
            $sid = (int)$it['supplierid'];
            $sn = $sid > 0 ? ($supNames[$sid] ?? ('#' . $sid)) : '— unassigned —';
            if (!isset($bySup[$sn])) $bySup[$sn] = ['supplier' => $sn, 'value' => 0.0, 'units' => 0, 'skus' => 0];
            $bySup[$sn]['value'] += $full; $bySup[$sn]['units'] += $qty; $bySup[$sn]['skus'] += 1;
            if ($qty <= 0) $out++;
            $dd = json_decode($it['data'] ?: '{}', true);
            $lowLevel = (int)($dd['lowlevel'] ?? 10);
            if ($qty <= $lowLevel) {
                $low++;
                $lowList[] = ['itemid' => $id, 'name' => $it['name'], 'qty' => $qty, 'lowlevel' => $lowLevel, 'supplier' => $sn, 'code' => $it['code']];
            }
        }
        usort($lowList, function ($a, $b) { return $a['qty'] <=> $b['qty']; });
        $bySupArr = array_values($bySup);
        usort($bySupArr, function ($a, $b) { return $b['value'] <=> $a['value']; });
        foreach ($bySupArr as &$b) $b['value'] = round($b['value'], 2);

        // Top items by stock value + value by location
        $locNames = [];
        foreach ($pdo->query("SELECT id, name FROM locations")->fetchAll(PDO::FETCH_ASSOC) as $l) $locNames[(int)$l['id']] = $l['name'];
        $topValue = [];
        foreach ($items as $it) {
            $id = (int)$it['id'];
            $qty = $qtyMap[$id] ?? 0;
            $cost = $this->itemCost($it['data']);
            $topValue[] = ['itemid' => $id, 'name' => $it['name'], 'qty' => $qty, 'cost' => $cost, 'value' => round($qty * $cost, 2)];
        }
        usort($topValue, function ($a, $b) { return $b['value'] <=> $a['value']; });
        $topValue = array_slice($topValue, 0, 5);

        $byLoc = [];
        $locRows = $pdo->query("SELECT l.id, l.name, SUM(sl.stocklevel) AS qty FROM stock_levels sl JOIN locations l ON l.id = sl.locationid GROUP BY l.id")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($locRows as $lr) {
            $byLoc[] = ['locationid' => (int)$lr['id'], 'location' => $lr['name'], 'qty' => (int)$lr['qty']];
        }
        // attach value by summing item costs per location
        foreach ($byLoc as &$lb) {
            $lb['value'] = 0.0;
            $rows = $pdo->prepare("SELECT si.data, SUM(sl.stocklevel) AS qty FROM stock_levels sl JOIN stored_items si ON si.id = sl.storeditemid WHERE sl.locationid = :lid GROUP BY si.id");
            $rows->execute([':lid' => $lb['locationid']]);
            foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $rr) $lb['value'] += $this->itemCost($rr['data']) * (float)$rr['qty'];
            $lb['value'] = round($lb['value'], 2);
        }

        // Movement stats from stock_history (auxdir 1=in purchase, 2=out sale, 3=adjust, 4=transfer)
        $now = "NOW()";
        $q = function ($days) use ($pdo) {
            $sql = "SELECT type,
                    COALESCE(SUM(CASE WHEN auxdir=1 THEN amount ELSE 0 END),0) AS in_u,
                    COALESCE(SUM(CASE WHEN auxdir=2 THEN amount ELSE 0 END),0) AS out_u,
                    COUNT(*) AS moves
                    FROM stock_history WHERE dt >= DATE_SUB(NOW(), INTERVAL $days DAY)
                    GROUP BY type ORDER BY (in_u+out_u) DESC";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $tin = 0; $tout = 0; $tmoves = 0;
            foreach ($rows as &$r) { $r['in_units'] = (int)$r['in_u']; $r['out_units'] = (int)$r['out_u']; $r['moves'] = (int)$r['moves']; $tin += $r['in_units']; $tout += $r['out_units']; $tmoves += $r['moves']; }
            return ['total_in' => $tin, 'total_out' => $tout, 'moves' => $tmoves, 'by_type' => $rows];
        };

        $recent = $pdo->query("SELECT h.dt, h.type, h.amount, h.auxdir, i.name AS item, l.name AS location
                                FROM stock_history h
                                LEFT JOIN stored_items i ON i.id = h.storeditemid
                                LEFT JOIN locations l ON l.id = h.locationid
                                ORDER BY h.dt DESC, h.id DESC LIMIT 25")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recent as &$r) $r['amount'] = (int)$r['amount'];

        $result['data'] = [
            'kpis' => [
                'skus' => $sku, 'units' => $units, 'stock_value' => round($value, 2),
                'out_of_stock' => $out, 'low_stock' => $low,
            ],
            'by_supplier' => $bySupArr,
            'by_location' => $byLoc,
            'top_value' => $topValue,
            'low_stock_list' => array_slice($lowList, 0, 15),
            'moves_7d' => $q(7),
            'moves_30d' => $q(30),
            'recent_moves' => $recent,
            'items' => array_map(function ($it) {
                $dd = json_decode($it['data'] ?: '{}', true);
                return ['id' => (int)$it['id'], 'name' => $it['name'], 'supplierid' => (int)$it['supplierid'], 'code' => $it['code'], 'cost' => round((float)($dd['cost'] ?? 0), 2), 'lowlevel' => (int)($dd['lowlevel'] ?? 10)];
            }, $items),
        ];
        return $result;
    }

    /** Set an item's supplier (0 = unassigned). */
    public function setSupplier($result) {
        $itemId = isset($this->data->itemid) ? (int)$this->data->itemid : (isset($this->data->id) ? (int)$this->data->id : 0);
        $supId = isset($this->data->supplierid) ? (int)$this->data->supplierid : 0;
        if ($itemId <= 0) { $result['error'] = 'itemid required'; return $result; }
        if ($supId > 0) {
            $ex = $this->db()->prepare("SELECT id FROM stored_suppliers WHERE id = :id");
            $ex->execute([':id' => $supId]);
            if (!$ex->fetchColumn()) { $result['error'] = 'Supplier not found'; return $result; }
        }
        $pdo = $this->db();
        $pdo->prepare("UPDATE stored_items SET supplierid = :s WHERE id = :id")->execute([':s' => $supId, ':id' => $itemId]);
        Logger::write("Item $itemId supplier set to $supId", "ITEM");
        $result['data'] = ['itemid' => $itemId, 'supplierid' => $supId];
        return $result;
    }

    /** Set cost and/or low-stock alert level inside the item's data JSON. */
    public function setCost($result) {
        $itemId = isset($this->data->itemid) ? (int)$this->data->itemid : (isset($this->data->id) ? (int)$this->data->id : 0);
        if ($itemId <= 0) { $result['error'] = 'itemid required'; return $result; }
        $pdo = $this->db();
        $row = $pdo->prepare("SELECT data FROM stored_items WHERE id = :id");
        $row->execute([':id' => $itemId]);
        $cur = json_decode($row->fetchColumn() ?: '{}', true);
        if (!is_array($cur)) $cur = [];
        if (isset($this->data->cost)) $cur['cost'] = number_format((float)$this->data->cost, 2, '.', '');
        if (isset($this->data->lowlevel)) $cur['lowlevel'] = (int)$this->data->lowlevel;
        $pdo->prepare("UPDATE stored_items SET data = :d WHERE id = :id")->execute([':d' => json_encode($cur), ':id' => $itemId]);
        Logger::write("Item $itemId cost/lowlevel updated", "ITEM");
        $result['data'] = ['itemid' => $itemId, 'cost' => (float)($cur['cost'] ?? 0), 'lowlevel' => (int)($cur['lowlevel'] ?? 10)];
        return $result;
    }
}
