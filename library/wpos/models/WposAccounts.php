<?php
/**
 * WposAccounts — money holders + cash movement ledger.
 *
 * Balance per account = Σ(in) − Σ(out) over account_movements.
 * Transfers (sweeps) write an OUT row + an IN row sharing a batch ref.
 * Sources: purchase | supplierpay | manual | transfer | opening | sale.
 */
class WposAccounts {
    private $data;
    function __construct($data = null) { $this->data = $data !== null ? $data : new stdClass(); }
    private function db() { return (new DbConfig())->_db; }
    private function uid() { return isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : null; }

    private function balances() {
        $pdo = $this->db();
        $out = [];
        $rows = $pdo->query(
            "SELECT a.id, a.name, a.type, a.active, a.sortorder,
                    COALESCE(SUM(CASE WHEN m.direction='in' THEN m.amount ELSE -m.amount END),0) AS balance,
                    COALESCE(SUM(CASE WHEN m.direction='in' THEN m.amount ELSE 0 END),0) AS total_in,
                    COALESCE(SUM(CASE WHEN m.direction='out' THEN m.amount ELSE 0 END),0) AS total_out,
                    COUNT(m.id) AS moves
             FROM accounts a LEFT JOIN account_movements m ON m.accountid = a.id
             GROUP BY a.id ORDER BY a.sortorder, a.id"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $r['id'] = (int)$r['id'];
            $r['balance'] = round((float)$r['balance'], 2);
            $r['total_in'] = round((float)$r['total_in'], 2);
            $r['total_out'] = round((float)$r['total_out'], 2);
            $r['moves'] = (int)$r['moves'];
            $out[] = $r;
        }
        return $out;
    }

    public function list($result) {
        $result['data'] = $this->balances();
        return $result;
    }

    public function add($result) {
        $name = trim((string)($this->data->name ?? ''));
        $type = (string)($this->data->type ?? 'cash');
        if ($name === '') { $result['error'] = 'Account name required'; return $result; }
        if (!in_array($type, ['cash','mobile','bank','other'], true)) $type = 'cash';
        $pdo = $this->db();
        $exists = $pdo->prepare("SELECT id FROM accounts WHERE name = :n");
        $exists->execute([':n' => $name]);
        if ($exists->fetchColumn()) { $result['error'] = 'An account with that name already exists'; return $result; }
        $mx = $pdo->query("SELECT COALESCE(MAX(sortorder),0) FROM accounts")->fetchColumn();
        $st = $pdo->prepare("INSERT INTO accounts (name, type, sortorder) VALUES (:n,:t,:o)");
        $st->execute([':n' => $name, ':t' => $type, ':o' => ((int)$mx) + 1]);
        $id = (int)$pdo->lastInsertId();
        Logger::write("Account $name created", "ACCOUNTS");
        $result['data'] = ['id' => $id, 'name' => $name, 'type' => $type];
        return $result;
    }

    /** Write one movement (also used internally by purchases/payments). */
    public function record($pdo, $accountId, $direction, $amount, $source, $ref, $desc, $proc = null, $supplierId = 0) {
        $amount = round((float)$amount, 2);
        if ($amount <= 0 || !in_array($direction, ['in','out'], true)) return false;
        if ($proc === null) $proc = (int)(microtime(true) * 1000);
        $st = $pdo->prepare(
            "INSERT INTO account_movements (accountid, direction, amount, source, ref, description, processdt, userid, supplierid)
             VALUES (:a,:d,:amt,:s,:ref,:desc,:pdt,:uid,:sup)"
        );
        return $st->execute([
            ':a' => (int)$accountId, ':d' => $direction, ':amt' => $amount,
            ':s' => $source, ':ref' => (string)$ref, ':desc' => (string)$desc,
            ':pdt' => $proc, ':uid' => $this->uid(), ':sup' => ((int)$supplierId > 0 ? (int)$supplierId : null),
        ]);
    }

    /** Manual entry: deposit (in), withdrawal (out) or transfer (sweep). */
    public function entry($result) {
        $kind = (string)($this->data->kind ?? 'deposit');
        $amount = round((float)($this->data->amount ?? 0), 2);
        $desc = (string)($this->data->description ?? '');
        if ($amount <= 0) { $result['error'] = 'Amount must be > 0'; return $result; }
        $pdo = $this->db();
        $proc = (int)(microtime(true) * 1000);
        try {
            $pdo->beginTransaction();
            if ($kind === 'deposit' || $kind === 'withdrawal') {
                $accId = (int)($this->data->accountid ?? $this->data->account ?? 0);
                if ($accId <= 0) { $pdo->rollBack(); $result['error'] = 'accountid required'; return $result; }
                $dir = $kind === 'deposit' ? 'in' : 'out';
                // Category -> ledger source so outflows/recoveries stay classifiable.
                $cat = strtolower(trim((string)($this->data->category ?? '')));
                $allowed = ['manual','external','marketing','customer_refund','supplier_refund','sale','purchase','supplierpay','transfer','opening'];
                $src = in_array($cat, $allowed, true) ? $cat : 'manual';
                $ref = 'EXT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
                $ok = $this->record($pdo, $accId, $dir, $amount, $src, $ref, $desc, $proc);
                if (!$ok) { $pdo->rollBack(); $result['error'] = 'Could not record movement'; return $result; }
                $result['data'] = ['ref' => $ref, 'amount' => $amount, 'kind' => $kind];
            } elseif ($kind === 'transfer') {
                $from = (int)($this->data->from_accountid ?? $this->data->accountid ?? 0);
                $to = (int)($this->data->to_accountid ?? 0);
                if ($from <= 0 || $to <= 0) { $pdo->rollBack(); $result['error'] = 'from_accountid and to_accountid required'; return $result; }
                if ($from === $to) { $pdo->rollBack(); $result['error'] = 'Source and destination must differ'; return $result; }
                $batch = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
                $this->record($pdo, $from, 'out', $amount, 'transfer', $batch, $desc ?: 'Sweep/transfer', $proc);
                $this->record($pdo, $to, 'in', $amount, 'transfer', $batch, $desc ?: 'Sweep/transfer', $proc);
                $result['data'] = ['ref' => $batch, 'amount' => $amount, 'kind' => 'transfer'];
            } else {
                $pdo->rollBack(); $result['error'] = 'kind must be deposit | withdrawal | transfer'; return $result;
            }
            $pdo->commit();
            Logger::write(json_encode($result['data']), "ACCOUNTS");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $result['error'] = $e->getMessage();
        }
        return $result;
    }

    public function movements($result) {
        $accId = isset($this->data->accountid) ? (int)$this->data->accountid : 0;
        $source = isset($this->data->source) ? (string)$this->data->source : '';
        $dirFilter = isset($this->data->direction) ? (string)$this->data->direction : '';
        $limit = isset($this->data->limit) ? (int)$this->data->limit : 300;
        $limit = max(1, min($limit, 1000));
        $sql = "SELECT m.id, m.accountid, a.name AS account, m.direction, m.amount, m.source, m.ref, m.description, m.processdt, m.dt
                FROM account_movements m JOIN accounts a ON a.id = m.accountid WHERE 1=1";
        $params = [];
        if ($accId > 0) { $sql .= " AND m.accountid = :acc"; $params[':acc'] = $accId; }
        if ($source !== '') { $sql .= " AND m.source = :src"; $params[':src'] = $source; }
        if ($dirFilter === 'in' || $dirFilter === 'out') { $sql .= " AND m.direction = :dir"; $params[':dir'] = $dirFilter; }
        if (isset($this->data->from) && (int)$this->data->from > 0) { $sql .= " AND m.processdt >= :from"; $params[':from'] = (int)$this->data->from; }
        if (isset($this->data->to) && (int)$this->data->to > 0) { $sql .= " AND m.processdt <= :to"; $params[':to'] = (int)$this->data->to; }
        $sql .= " ORDER BY m.processdt DESC, m.id DESC LIMIT " . $limit;
        $st = $this->db()->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['amount'] = round((float)$r['amount'], 2); $r['processdt'] = (int)$r['processdt']; }
        $result['data'] = $rows;
        return $result;
    }

    /** Rich aggregates for the Accounts page: totals by source, daily flow, filters. */
    public function stats($result) {
        $pdo = $this->db();
        $from = isset($this->data->from) ? (int)$this->data->from : 0;
        $to = isset($this->data->to) ? (int)$this->data->to : 0;
        $accId = isset($this->data->accountid) ? (int)$this->data->accountid : 0;
        $where = "WHERE 1=1"; $params = [];
        if ($from > 0) { $where .= " AND processdt >= :from"; $params[':from'] = $from; }
        if ($to > 0) { $where .= " AND processdt <= :to"; $params[':to'] = $to; }
        if ($accId > 0) { $where .= " AND accountid = :acc"; $params[':acc'] = $accId; }

        $sum = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0) AS tin,
                                      COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) AS tout,
                                      COUNT(*) AS moves
                               FROM account_movements $where");
        $sum->execute($params);
        $tot = $sum->fetch(PDO::FETCH_ASSOC);

        $bs = $pdo->prepare("SELECT source,
                                      COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0) AS tin,
                                      COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) AS tout,
                                      COUNT(*) AS moves
                               FROM account_movements $where GROUP BY source ORDER BY (tin+tout) DESC");
        $bs->execute($params);
        $bySource = [];
        foreach ($bs->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $bySource[] = ['source' => $r['source'], 'in' => round((float)$r['tin'], 2), 'out' => round((float)$r['tout'], 2), 'net' => round((float)$r['tin'] - (float)$r['tout'], 2), 'moves' => (int)$r['moves']];
        }

        $d14 = (time() - 14 * 86400) * 1000;
        $daily = $pdo->query("SELECT DATE(FROM_UNIXTIME(processdt/1000)) AS d,
                                      COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0) AS tin,
                                      COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) AS tout
                               FROM account_movements WHERE processdt >= $d14
                               GROUP BY d ORDER BY d")
            ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($daily as &$r) { $r['in'] = round((float)$r['tin'], 2); $r['out'] = round((float)$r['tout'], 2); unset($r['tin'], $r['tout']); }

        $result['data'] = [
            'totals' => [
                'in' => round((float)$tot['tin'], 2), 'out' => round((float)$tot['tout'], 2),
                'net' => round((float)$tot['tin'] - (float)$tot['tout'], 2), 'moves' => (int)$tot['moves'],
            ],
            'by_source' => $bySource,
            'daily_14d' => $daily,
            'accounts' => $this->balances(),
        ];
        return $result;
    }

    /** Dashboard-ready summary: per-account balances + grand totals. */
    public function summary($result) {
        $bals = $this->balances();
        $in = 0; $out = 0; $bal = 0;
        foreach ($bals as $b) { $in += $b['total_in']; $out += $b['total_out']; $bal += $b['balance']; }
        $result['data'] = [
            'accounts' => $bals,
            'balance' => round($bal, 2),
            'total_in' => round($in, 2),
            'total_out' => round($out, 2),
            'net' => round($in - $out, 2),
        ];
        return $result;
    }
}
