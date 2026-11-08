<?php
/**
 * WposCredit — customer credit accounts on top of the existing sales/sale_payments tables.
 * No new DB: owed = sales.total − Σ(non-credit sale_payments) per sale for that customer.
 * A "credit sale" is any completed sale (status=1) with custid>0 that has any
 * payment method='credit' OR balance>0. Repayments are extra non-credit payments
 * against the same sale_id (FIFO allocation). Due date = processdt + credit_due_days.
 */
class WposCredit {
    private $data;
    function __construct($data = null) {
        $this->data = $data !== null ? $data : new stdClass();
    }

    private function db() { return (new DbConfig())->_db; }

    private function custRow($custid) {
        $pdo = $this->db();
        $st = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
        $st->execute([':id' => (int)$custid]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        $r['credit_limit'] = isset($r['credit_limit']) ? (float)$r['credit_limit'] : 0;
        $r['credit_due_days'] = isset($r['credit_due_days']) ? (int)$r['credit_due_days'] : 7;
        $r['credit_enabled'] = isset($r['credit_enabled']) ? (int)$r['credit_enabled'] : 0;
        return $r;
    }

    /** Owed per sale for a customer (only sales that were part-credit or fully credit). */
    private function saleBalances($custid) {
        $pdo = $this->db();
        $st = $pdo->prepare(
            "SELECT s.id, s.ref, s.total, s.status, s.dt, s.processdt, s.duedt, s.balance,
                    COALESCE(SUM(CASE WHEN p.method='credit' THEN p.amount ELSE 0 END),0) AS credit_amt,
                    COALESCE(SUM(CASE WHEN p.method<>'credit' THEN p.amount ELSE 0 END),0) AS paid_amt
             FROM sales s LEFT JOIN sale_payments p ON p.saleid = s.id
             WHERE s.custid = :cid AND s.status = 1
             GROUP BY s.id
             ORDER BY s.dt ASC"
        );
        $st->execute([':cid' => (int)$custid]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $total = (float)$r['total']; $credit = (float)$r['credit_amt']; $paid = (float)$r['paid_amt'];
            $owed = max(0, $total - $paid);
            // Only sales that used credit (or still owe) are credit account entries.
            if ($credit <= 0 && $owed <= 0.005) continue;
            $dueDays = 7;
            $out[] = [
                'sale_id' => (int)$r['id'], 'ref' => $r['ref'],
                'total' => $total, 'credit_amt' => $credit, 'paid' => $paid, 'owed' => $owed,
                'dt' => $r['dt'], 'processdt' => (int)$r['processdt'],
                'duedt' => $r['duedt'] ? (int)$r['duedt'] : null,
                'overdue' => false,
            ];
        }
        // due dates need customer row for days
        return $out;
    }

    private function withDueDates($custid, $rows) {
        $cust = $this->custRow($custid);
        $days = $cust && $cust['credit_due_days'] > 0 ? $cust['credit_due_days'] : 7;
        $now = time() * 1000;
        foreach ($rows as &$r) {
            if (empty($r['duedt'])) $r['duedt'] = $r['processdt'] + $days * 86400000;
            $r['overdue'] = ($r['owed'] > 0.005 && $r['duedt'] < $now);
        }
        return $rows;
    }

    public function statement($result) {
        $custid = isset($this->data->custid) ? (int)$this->data->custid : 0;
        if ($custid <= 0) { $result['error'] = 'custid required'; return $result; }
        $cust = $this->custRow($custid);
        if (!$cust) { $result['error'] = 'Customer not found'; return $result; }
        $rows = $this->withDueDates($custid, $this->saleBalances($custid));
        $owed = 0; $credited = 0; $paid = 0; $overdue = 0;
        foreach ($rows as $r) { $owed += $r['owed']; $credited += $r['credit_amt']; $paid += $r['paid']; if ($r['overdue']) $overdue += $r['owed']; }
        $result['data'] = [
            'customer' => $cust,
            'open_balance' => round($owed, 2),
            'total_credited' => round($credited, 2),
            'total_paid' => round($paid, 2),
            'overdue' => round($overdue, 2),
            'available' => $cust['credit_limit'] > 0 ? round(max(0, $cust['credit_limit'] - $owed), 2) : null,
            'entries' => $rows,
        ];
        return $result;
    }

    public function report($result) {
        $pdo = $this->db();
        $rows = $pdo->query(
            "SELECT c.id, c.name, c.phone, c.email, c.credit_limit, c.credit_due_days, c.credit_enabled
             FROM customers c ORDER BY c.name"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $bal = $this->saleBalances((int)$r['id']);
            $owed = 0;
            foreach ($bal as $b) $owed += $b['owed'];
            $r['open_balance'] = round($owed, 2);
            $r['entries'] = count($bal);
        }
        foreach ($rows as &$r) {
            $r['open_balance'] = (float)$r['open_balance']; $r['credit_limit'] = (float)$r['credit_limit'];
            $r['available'] = $r['credit_limit'] > 0 ? round(max(0, $r['credit_limit'] - $r['open_balance']), 2) : null;
            $r['over_limit'] = $r['credit_limit'] > 0 && $r['open_balance'] > $r['credit_limit'] + 0.005;
        }
        $result['data'] = $rows;
        return $result;
    }

    public function search($result) {
        $q = isset($this->data->q) ? trim((string)$this->data->q) : '';
        $pdo = $this->db();
        if ($q === '') {
            $rows = $pdo->query("SELECT id,name,phone,email,credit_limit,credit_enabled FROM customers ORDER BY name LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $st = $pdo->prepare("SELECT id,name,phone,email,credit_limit,credit_enabled FROM customers WHERE name LIKE :q OR phone LIKE :q OR email LIKE :q OR mobile LIKE :q ORDER BY name LIMIT 50");
            $st->execute([':q' => '%' . $q . '%']);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        }
        // attach owed
        foreach ($rows as &$r) {
            $bal = $this->saleBalances((int)$r['id']);
            $owed = 0; foreach ($bal as $b) $owed += $b['owed'];
            $r['open_balance'] = round($owed, 2);
            $r['credit_limit'] = (float)$r['credit_limit'];
        }
        $result['data'] = $rows;
        return $result;
    }

    public function pay($result) {
        $custid = isset($this->data->custid) ? (int)$this->data->custid : 0;
        $amount = isset($this->data->amount) ? (float)$this->data->amount : 0;
        $method = isset($this->data->method) ? (string)$this->data->method : 'mpesa';
        $saleRef = isset($this->data->sale_ref) ? (string)$this->data->sale_ref : null;
        if ($custid <= 0 || $amount <= 0) { $result['error'] = 'custid and amount>0 required'; return $result; }
        if ($method === 'credit') { $result['error'] = 'Repayment method cannot be credit'; return $result; }
        $cust = $this->custRow($custid);
        if (!$cust) { $result['error'] = 'Customer not found'; return $result; }
        $entries = $this->withDueDates($custid, $this->saleBalances($custid));
        // target slice: specific ref or FIFO oldest first
        $targets = [];
        if ($saleRef) {
            foreach ($entries as $e) if ($e['ref'] === $saleRef && $e['owed'] > 0.005) $targets[] = $e;
            if (!$targets) { $result['error'] = 'Sale not found or already paid'; return $result; }
        } else {
            foreach ($entries as $e) if ($e['owed'] > 0.005) $targets[] = $e;
        }
        if (!$targets) { $result['error'] = 'No open credit balance'; return $result; }
        $pdo = $this->db();
        $remaining = $amount; $alloc = [];
        $proc = (int)(microtime(true) * 1000);
        try {
            $pdo->beginTransaction();
            foreach ($targets as $t) {
                if ($remaining <= 0.005) break;
                $pay = min($remaining, $t['owed']);
                $st = $pdo->prepare("INSERT INTO sale_payments (saleid, method, amount, processdt) VALUES (:sid, :m, :amt, :pdt)");
                $st->execute([':sid' => $t['sale_id'], ':m' => $method, ':amt' => $pay, ':pdt' => $proc]);
                $remaining -= $pay;
                $alloc[] = ['ref' => $t['ref'], 'paid' => round($pay, 2)];
                // refresh sales.balance
                $nb = $t['owed'] - $pay;
                $pdo->prepare("UPDATE sales SET balance = :b WHERE id = :id")->execute([':b' => round($nb, 2), ':id' => $t['sale_id']]);
            }
            if ($remaining > 0.005) { $pdo->rollBack(); $result['error'] = 'Payment exceeds open balance (over by ' . round($remaining, 2) . ')'; return $result; }
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $result['error'] = $e->getMessage(); return $result;
        }
        WposTransactions::addTransactionHistory($targets[0]['sale_id'], $_SESSION['userId'] ?? 0, 'CreditPayment', "Credit repayment KSh $amount via $method for customer $custid");
        Logger::write("Credit repayment KSh $amount via $method for customer $custid alloc=" . json_encode($alloc), "CREDIT");
        $result['data'] = ['allocated' => $alloc, 'amount' => $amount];
        return $result;
    }
}
