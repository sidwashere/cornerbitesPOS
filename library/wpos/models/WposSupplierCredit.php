<?php
/**
 * WposSupplierCredit — supplier payables on the new supplier_purchases ledger.
 * Owed per purchase = total − paid_immediate − Σ(supplier_payments). Due = processdt + due_days.
 * Mirrors customer WposCredit; purchases also adjust stock_levels.
 */
class WposSupplierCredit {
    private $data;
    function __construct($data = null) { $this->data = $data !== null ? $data : new stdClass(); }
    private function db() { return (new DbConfig())->_db; }

    private function supRow($id) {
        $pdo = $this->db();
        $st = $pdo->prepare("SELECT * FROM stored_suppliers WHERE id = :id");
        $st->execute([':id' => (int)$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        $r['credit_limit'] = (float)($r['credit_limit'] ?? 0);
        $r['credit_due_days'] = (int)($r['credit_due_days'] ?? 7);
        $r['credit_enabled'] = (int)($r['credit_enabled'] ?? 0);
        return $r;
    }

    private function purchaseBalances($supid) {
        $pdo = $this->db();
        $st = $pdo->prepare(
            "SELECT p.id, p.ref, p.total, p.paid_immediate, p.balance, p.status, p.processdt, p.duedt, p.notes, p.dt, p.items_json,
                    COALESCE((SELECT SUM(amount) FROM supplier_payments WHERE purchaseid = p.id),0) AS paid_after
             FROM supplier_purchases p WHERE p.supplierid = :sid ORDER BY p.processdt ASC"
        );
        $st->execute([':sid' => (int)$supid]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $total = (float)$r['total']; $paid = (float)$r['paid_immediate'] + (float)$r['paid_after'];
            $owed = max(0, $total - $paid);
            if ($owed <= 0.005 && $total <= 0.005) continue; // skip empty
            if ($owed <= 0.005 && (float)$r['paid_immediate'] <= 0.005 && (float)$r['paid_after'] <= 0.005 && $total <= 0.005) continue;
            // include only purchases that were part-credit or still owe (paid_immediate < total)
            if ((float)$r['paid_immediate'] >= $total - 0.005 && $owed <= 0.005) continue;
            $out[] = [
                'purchase_id' => (int)$r['id'], 'ref' => $r['ref'],
                'total' => $total, 'paid_immediate' => (float)$r['paid_immediate'],
                'paid_after' => (float)$r['paid_after'], 'paid' => $paid, 'owed' => $owed,
                'processdt' => (int)$r['processdt'], 'duedt' => $r['duedt'] ? (int)$r['duedt'] : null,
                'notes' => $r['notes'], 'dt' => $r['dt'],
                'items' => (isset($r['items_json']) && is_string($r['items_json']) && $r['items_json'] !== '') ? (json_decode($r['items_json'], true) ?: []) : [],
            ];
        }
        $days = 7;
        $sup = $this->supRow($supid);
        if ($sup) $days = (int)$sup['credit_due_days'] ?: 7;
        $now = time() * 1000;
        foreach ($out as &$r) {
            if (empty($r['duedt'])) $r['duedt'] = $r['processdt'] + $days * 86400000;
            $r['overdue'] = ($r['owed'] > 0.005 && $r['duedt'] < $now);
        }
        return $out;
    }

    public function statement($result) {
        $id = isset($this->data->supplierid) ? (int)$this->data->supplierid : (isset($this->data->id) ? (int)$this->data->id : 0);
        if ($id <= 0) { $result['error'] = 'supplierid required'; return $result; }
        $sup = $this->supRow($id);
        if (!$sup) { $result['error'] = 'Supplier not found'; return $result; }
        $entries = $this->purchaseBalances($id);
        $owed = 0; $credited = 0; $paid = 0; $overdue = 0;
        foreach ($entries as $e) { $owed += $e['owed']; $credited += $e['total']; $paid += $e['paid']; if ($e['overdue']) $overdue += $e['owed']; }
        // Payment history: ledger supplier payments + which account each left from
        $payments = [];
        try {
            $pdo = $this->db();
            $pst = $pdo->prepare(
                "SELECT m.processdt, a.name AS account, m.amount, m.description
                 FROM account_movements m LEFT JOIN accounts a ON a.id = m.accountid
                 WHERE m.supplierid = :sid AND m.source = 'supplierpay' AND m.direction = 'out'
                 ORDER BY m.processdt DESC"
            );
            $pst->execute([':sid' => $id]);
            foreach ($pst->fetchAll(PDO::FETCH_ASSOC) as $pr) {
                $payments[] = ['processdt' => (int)$pr['processdt'], 'account' => $pr['account'] ?: '—', 'amount' => round((float)$pr['amount'], 2), 'description' => (string)$pr['description']];
            }
        } catch (Exception $e) { $payments = []; }
        $result['data'] = [
            'supplier' => $sup, 'open_balance' => round($owed, 2),
            'total_purchased' => round($credited, 2), 'total_paid' => round($paid, 2),
            'overdue' => round($overdue, 2),
            'available' => $sup['credit_limit'] > 0 ? round(max(0, $sup['credit_limit'] - $owed), 2) : null,
            'entries' => $entries,
            'payments' => $payments,
        ];
        return $result;
    }

    public function report($result) {
        $pdo = $this->db();
        $rows = $pdo->query("SELECT id, name, phone, email, credit_limit, credit_due_days, credit_enabled FROM stored_suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $bal = $this->purchaseBalances((int)$r['id']);
            $owed = 0; $pur = 0; $nextDue = null; $overdueAmt = 0;
            foreach ($bal as $b) {
                $owed += $b['owed']; $pur += $b['total'];
                if ($b['owed'] > 0.005) {
                    if ($b['duedt'] && ($nextDue === null || $b['duedt'] < $nextDue)) $nextDue = $b['duedt'];
                    if (!empty($b['overdue'])) $overdueAmt += $b['owed'];
                }
            }
            $r['open_balance'] = round($owed, 2);
            $r['total_purchased'] = round($pur, 2);
            // Lifetime paid (immediate + later payments) actually sent to this supplier
            $paidRow = $pdo->prepare("SELECT COALESCE((SELECT SUM(total) FROM supplier_purchases WHERE supplierid = :id),0) AS tot_all, COALESCE((SELECT SUM(paid_immediate) FROM supplier_purchases WHERE supplierid = :id),0) AS imm, COALESCE((SELECT SUM(sp.amount) FROM supplier_payments sp JOIN supplier_purchases pp ON pp.id = sp.purchaseid WHERE pp.supplierid = :id),0) AS later");
            $paidRow->execute([':id' => (int)$r['id']]);
            $pr = $paidRow->fetch(PDO::FETCH_ASSOC);
            $r['total_paid'] = round((float)$pr['imm'] + (float)$pr['later'], 2);
            $r['total_all_purchased'] = round((float)$pr['tot_all'], 2);
            $r['next_due'] = $nextDue;
            $r['overdue'] = round($overdueAmt, 2);
            $r['open_purchases'] = count(array_filter($bal, function ($b) { return $b['owed'] > 0.005; }));
            $r['credit_limit'] = (float)$r['credit_limit'];
            $r['available'] = $r['credit_limit'] > 0 ? round(max(0, $r['credit_limit'] - $owed), 2) : null;
            $r['over_limit'] = $r['credit_limit'] > 0 && $owed > $r['credit_limit'] + 0.005;
            $r['entries'] = count($bal);
        }
        $result['data'] = $rows;
        return $result;
    }

    public function search($result) {
        $q = isset($this->data->q) ? trim((string)$this->data->q) : '';
        $pdo = $this->db();
        if ($q === '') {
            $rows = $pdo->query("SELECT id,name,phone,email,credit_limit,credit_enabled FROM stored_suppliers ORDER BY name LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $st = $pdo->prepare("SELECT id,name,phone,email,credit_limit,credit_enabled FROM stored_suppliers WHERE name LIKE :q OR phone LIKE :q OR email LIKE :q ORDER BY name LIMIT 50");
            $st->execute([':q' => '%' . $q . '%']);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach ($rows as &$r) {
            $bal = $this->purchaseBalances((int)$r['id']);
            $owed = 0; foreach ($bal as $b) $owed += $b['owed'];
            $r['open_balance'] = round($owed, 2);
            $r['credit_limit'] = (float)$r['credit_limit'];
        }
        $result['data'] = $rows;
        return $result;
    }

    /** Create a purchase (credit and/or immediate pay) + adjust stock. */
    public function purchase($result) {
        $supid = isset($this->data->supplierid) ? (int)$this->data->supplierid : 0;
        $items = isset($this->data->items) && is_array($this->data->items) ? $this->data->items : [];
        $paidImmediate = isset($this->data->paid_immediate) ? (float)$this->data->paid_immediate : 0;
        $method = isset($this->data->method) ? (string)$this->data->method : 'mpesa';
        $notes = isset($this->data->notes) ? (string)$this->data->notes : '';
        if ($supid <= 0 || !$items) { $result['error'] = 'supplierid and items required'; return $result; }
        $sup = $this->supRow($supid);
        if (!$sup) { $result['error'] = 'Supplier not found'; return $result; }
        // total from items (qty * cost)
        $total = 0;
        foreach ($items as $it) { $total += ((float)($it->qty ?? $it['qty'] ?? 0)) * ((float)($it->cost ?? $it['cost'] ?? $it->price ?? 0)); }
        $total = round($total, 2);
        if ($total <= 0) { $result['error'] = 'Purchase total must be > 0 (check qty × cost)'; return $result; }
        if ($paidImmediate < 0 || $paidImmediate > $total + 0.005) { $result['error'] = 'Immediate payment cannot exceed total'; return $result; }
        $onCredit = round($total - $paidImmediate, 2);
        if ($onCredit > 0.005) {
            if ((int)$sup['credit_enabled'] !== 1) { $result['error'] = 'This supplier is not approved for credit — enable Credit in their profile'; return $result; }
            $owedNow = 0;
            foreach ($this->purchaseBalances($supid) as $b) $owedNow += $b['owed'];
            $limit = (float)$sup['credit_limit'];
            if ($limit > 0 && ($owedNow + $onCredit) > $limit + 0.005) {
                $result['error'] = 'Supplier credit limit exceeded — owes KSh ' . number_format($owedNow,2) . ' of KSh ' . number_format($limit,2) . ' (this adds KSh ' . number_format($onCredit,2) . ')';
                return $result;
            }
        }
        $ref = isset($this->data->ref) && $this->data->ref ? (string)$this->data->ref : ('PUR-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)));
        $proc = isset($this->data->processdt) ? (int)$this->data->processdt : (int)(microtime(true) * 1000);
        $days = (int)$sup['credit_due_days'] ?: 7;
        $due = $onCredit > 0.005 ? $proc + $days * 86400000 : null;
        $pdo = $this->db();
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare("INSERT INTO supplier_purchases (supplierid, ref, total, paid_immediate, balance, status, processdt, duedt, notes, items_json) VALUES (:sid,:ref,:tot,:paid,:bal,1,:pdt,:due,:notes,:items)");
            $st->execute([':sid' => $supid, ':ref' => $ref, ':tot' => $total, ':paid' => $paidImmediate, ':bal' => $onCredit, ':pdt' => $proc, ':due' => $due, ':notes' => $notes, ':items' => json_encode($items)]);
            $pid = $pdo->lastInsertId();
            // stock adjustments: items carry storeditemid (+qty, +cost); fall back to name match
            $loc = 1;
            if (isset($this->data->locationid) && (int)$this->data->locationid > 0) $loc = (int)$this->data->locationid;
            foreach ($items as $it) {
                $qty = (int)($it->qty ?? 0);
                $cost = (float)($it->cost ?? 0);
                $itemId = (int)($it->storeditemid ?? $it->id ?? 0);
                if ($itemId <= 0 && !empty($it->name)) {
                    $q = $pdo->prepare("SELECT id FROM stored_items WHERE name = :n ORDER BY id LIMIT 1");
                    $q->execute([':n' => (string)$it->name]);
                    $itemId = (int)($q->fetchColumn() ?: 0);
                }
                if ($itemId > 0 && $qty !== 0) {
                    $pdo->prepare("UPDATE stock_levels SET stocklevel = stocklevel + :q, dt = NOW() WHERE storeditemid = :iid AND locationid = :loc")->execute([':q' => $qty, ':iid' => $itemId, ':loc' => $loc]);
                    $pdo->prepare("INSERT INTO stock_history (storeditemid, locationid, auxid, auxdir, type, amount, dt) VALUES (:iid,:loc,0,1,'purchase',:amt,NOW())")->execute([':iid' => $itemId, ':loc' => $loc, ':amt' => $qty]);
                    if ($cost > 0) $pdo->prepare("UPDATE stored_items SET data = REPLACE(data, '\"cost\":\"0.00\"', '\"cost\":\"" . number_format($cost,2) . "\"') WHERE id = :iid")->execute([':iid' => $itemId]);
                }
            }
            // Cash movement: paid-up portion leaves the chosen account (if any).
            $accId = isset($this->data->account_id) ? (int)$this->data->account_id : (isset($this->data->accountid) ? (int)$this->data->accountid : 0);
            if ($paidImmediate > 0.005 && $accId > 0) {
                $acct = $pdo->prepare("SELECT id FROM accounts WHERE id = :id AND active = 1");
                $acct->execute([':id' => $accId]);
                if ($acct->fetchColumn()) {
                    (new WposAccounts())->record($pdo, $accId, 'out', $paidImmediate, 'purchase', $ref, "Stock purchase from supplier #$supid" . ($notes ? " — $notes" : ""), $proc, $supid);
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $result['error'] = $e->getMessage(); return $result;
        }
        Logger::write("Supplier purchase $ref for supplier $supid total $total onCredit $onCredit", "SUPPLIER");
        $result['data'] = ['id' => $pid, 'ref' => $ref, 'total' => $total, 'on_credit' => $onCredit, 'duedt' => $due];
        return $result;
    }

    /** Record payment against purchases (FIFO or specific ref). */
    public function pay($result) {
        $supid = isset($this->data->supplierid) ? (int)$this->data->supplierid : 0;
        $amount = isset($this->data->amount) ? (float)$this->data->amount : 0;
        $method = isset($this->data->method) ? (string)$this->data->method : 'mpesa';
        $pref = isset($this->data->purchase_ref) ? (string)$this->data->purchase_ref : null;
        if ($supid <= 0 || $amount <= 0) { $result['error'] = 'supplierid and amount>0 required'; return $result; }
        $entries = $this->purchaseBalances($supid);
        $targets = [];
        if ($pref) {
            foreach ($entries as $e) if ($e['ref'] === $pref && $e['owed'] > 0.005) $targets[] = $e;
            if (!$targets) { $result['error'] = 'Purchase not found or already paid'; return $result; }
        } else {
            foreach ($entries as $e) if ($e['owed'] > 0.005) $targets[] = $e;
        }
        if (!$targets) { $result['error'] = 'No open payable balance'; return $result; }
        $pdo = $this->db();
        $remaining = $amount; $alloc = [];
        $proc = (int)(microtime(true) * 1000);
        try {
            $pdo->beginTransaction();
            foreach ($targets as $t) {
                if ($remaining <= 0.005) break;
                $pay = min($remaining, $t['owed']);
                $pdo->prepare("INSERT INTO supplier_payments (purchaseid, method, amount, processdt) VALUES (:pid,:m,:amt,:pdt)")->execute([':pid' => $t['purchase_id'], ':m' => $method, ':amt' => $pay, ':pdt' => $proc]);
                $remaining -= $pay;
                $alloc[] = ['ref' => $t['ref'], 'paid' => round($pay, 2)];
                $pdo->prepare("UPDATE supplier_purchases SET balance = :b WHERE id = :id")->execute([':b' => round($t['owed'] - $pay, 2), ':id' => $t['purchase_id']]);
            }
            if ($remaining > 0.005) { $pdo->rollBack(); $result['error'] = 'Payment exceeds payable (over by ' . round($remaining, 2) . ')'; return $result; }
            // Cash movement: payment leaves the chosen account (if any).
            $accId = isset($this->data->account_id) ? (int)$this->data->account_id : (isset($this->data->accountid) ? (int)$this->data->accountid : 0);
            if ($accId > 0) {
                $acct = $pdo->prepare("SELECT id FROM accounts WHERE id = :id AND active = 1");
                $acct->execute([':id' => $accId]);
                if ($acct->fetchColumn()) {
                    (new WposAccounts())->record($pdo, $accId, 'out', $amount, 'supplierpay', 'PAY-' . $proc, "Supplier payment to #$supid (" . implode(', ', array_map(function ($a) { return $a['ref']; }, $alloc)) . ")", $proc, $supid);
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $result['error'] = $e->getMessage(); return $result;
        }
        Logger::write("Supplier payment KSh $amount via $method for supplier $supid alloc=" . json_encode($alloc), "SUPPLIER");
        $result['data'] = ['allocated' => $alloc, 'amount' => $amount];
        return $result;
    }

    /** Dashboard tallies: total owed, overdue, due-7d, purchase volume (all-time + range). */
    public function dashboard($result) {
        $pdo = $this->db();
        $sups = $pdo->query("SELECT id FROM stored_suppliers")->fetchAll(PDO::FETCH_COLUMN);
        $owed = 0; $overdue = 0; $due7 = 0; $debtors = 0; $top = [];
        $now = time() * 1000;
        foreach ($sups as $sid) {
            $e = $this->purchaseBalances((int)$sid);
            $o = 0;
            foreach ($e as $x) {
                $o += $x['owed'];
                if ($x['overdue']) $overdue += $x['owed'];
                if ($x['owed'] > 0.005 && $x['duedt'] && $x['duedt'] <= $now + 7 * 86400000 && $x['duedt'] >= $now) $due7 += $x['owed'];
            }
            $owed += $o;
            if ($o > 0.005) { $debtors++; $top[] = ['supplierid' => (int)$sid, 'owed' => round($o, 2)]; }
        }
        $pur = $pdo->query("SELECT COALESCE(SUM(total),0) FROM supplier_purchases")->fetchColumn();
        $pur30 = $pdo->query("SELECT COALESCE(SUM(total),0) FROM supplier_purchases WHERE processdt >= " . (time() * 1000 - 30 * 86400000))->fetchColumn();
        $paid30 = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM supplier_payments WHERE processdt >= " . (time() * 1000 - 30 * 86400000))->fetchColumn();
        $paidImm30 = $pdo->query("SELECT COALESCE(SUM(paid_immediate),0) FROM supplier_purchases WHERE processdt >= " . (time() * 1000 - 30 * 86400000))->fetchColumn();
        usort($top, function ($a, $b) { return $b['owed'] <=> $a['owed']; });
        $top = array_slice($top, 0, 8);
        // names for top
        foreach ($top as &$t) {
            $s = $pdo->prepare("SELECT name FROM stored_suppliers WHERE id = :id");
            $s->execute([':id' => $t['supplierid']]);
            $t['name'] = $s->fetchColumn() ?: ('#' . $t['supplierid']);
        }
        $result['data'] = [
            'owed' => round($owed, 2), 'overdue' => round($overdue, 2), 'due_7d' => round($due7, 2),
            'debtors' => $debtors, 'purchases_total' => round((float)$pur, 2),
            'paid_30d' => round((float)$paid30 + (float)$paidImm30, 2), 'purchases_30d' => round((float)$pur30, 2), 'top_suppliers' => $top,
        ];
        return $result;
    }
}
