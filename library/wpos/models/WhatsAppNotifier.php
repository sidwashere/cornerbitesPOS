<?php
/**
 * WhatsAppNotifier - durable, retrying WhatsApp order notifications.
 *
 * Orders are NEVER blocked by WhatsApp: enqueueOrder() only inserts a row and
 * returns. A separate processor (api/process_whatsapp.php) drains the queue,
 * sends via the configured provider, retries with backoff, and dead-letters
 * after max_attempts so nothing is silently lost.
 *
 * Default provider: CallMeBot (free, no node, no paid account). Swap to a
 * generic webhook (Meta Cloud API / Twilio / self-hosted gateway) via config.
 */
class WhatsAppNotifier {
    private $db;
    private $config;

    public function __construct() {
        $this->db = new DbConfig();
        $this->config = $this->loadConfig();
    }

    private function loadConfig() {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/docs/whatsapp.json';
        if (file_exists($path)) {
            $cfg = @json_decode(file_get_contents($path), true);
            if (is_array($cfg)) return $cfg;
        }
        return ['enabled' => false];
    }

    /**
     * Enqueue a notification for a newly ingested order.
     * @param object $saleObj the ingested order jsonobj (has items map, orderdata, customer...)
     * @param string $channel website|glovo|ubereats|phone
     */
    public function enqueueOrder($saleObj, $channel) {
        if (empty($this->config['enabled'])) return;

        $message = $this->formatOrderMessage($saleObj, $channel);
        if ($message === null) return;

        $recipients = [];
        if (!empty($this->config['recipients']) && is_array($this->config['recipients'])) {
            $recipients = $this->config['recipients'];
        } elseif (!empty($this->config['callmebot']['phone'])) {
            $recipients = [$this->config['callmebot']['phone']];
        } elseif (!empty($this->config['webhook']['phone'])) {
            $recipients = [$this->config['webhook']['phone']];
        }
        if (empty($recipients)) return;

        $saleId = isset($saleObj->id) ? (int)$saleObj->id : 0;
        $stmt = $this->db->_db->prepare(
            "INSERT INTO wpos_whatsapp_queue (sale_id, channel, recipient, message, status, attempts, last_error, created_at, updated_at)
             VALUES (:sale_id, :channel, :recipient, :message, 'pending', 0, NULL, NOW(), NOW())"
        );
        foreach ($recipients as $r) {
            $stmt->execute([
                ':sale_id'   => $saleId,
                ':channel'   => $channel,
                ':recipient' => $r,
                ':message'   => $message,
            ]);
        }
    }

    /**
     * Drain the queue: send pending rows, retry with backoff, dead-letter.
     * @return array stats
     */
    public function processQueue() {
        $maxAttempts = (int)($this->config['max_attempts'] ?? 5);
        $backoff = (int)($this->config['retry_backoff_seconds'] ?? 60);
        $stats = ['sent' => 0, 'failed' => 0, 'dead' => 0, 'skipped' => 0];

        $stmt = $this->db->_db->prepare(
            "SELECT * FROM wpos_whatsapp_queue
             WHERE status IN ('pending','failed') AND attempts < :max
             ORDER BY id ASC LIMIT 100"
        );
        $stmt->bindValue(':max', $maxAttempts, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            // Respect backoff: don't retry a failed row too soon.
            if ($row['status'] === 'failed' && $backoff > 0) {
                $updated = strtotime($row['updated_at']);
                if (time() - $updated < $backoff * ($row['attempts'])) {
                    $stats['skipped']++;
                    continue;
                }
            }

            $this->bumpAttempts($row['id']);
            $ok = $this->send($row['recipient'], $row['message']);
            if ($ok === true) {
                $this->setStatus($row['id'], 'sent', null);
                $stats['sent']++;
            } else {
                $attempts = $row['attempts'] + 1;
                if ($attempts >= $maxAttempts) {
                    $this->setStatus($row['id'], 'dead', $ok);
                    $stats['dead']++;
                } else {
                    $this->setStatus($row['id'], 'failed', $ok);
                    $stats['failed']++;
                }
            }
        }
        return $stats;
    }

    /**
     * Send via the configured provider. Returns true on success or an error string.
     */
    private function send($recipient, $message) {
        $provider = $this->config['provider'] ?? 'callmebot';
        try {
            if ($provider === 'webhook') {
                return $this->sendViaWebhook($recipient, $message);
            }
            return $this->sendViaCallMeBot($recipient, $message);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function sendViaCallMeBot($recipient, $message) {
        $apikey = $this->config['callmebot']['apikey'] ?? '';
        if (empty($apikey)) return "CallMeBot apikey not configured";
        $phone = preg_replace('/[^0-9]/', '', $recipient);
        $url = "https://api.callmebot.com/whatsapp.php?phone=" . urlencode($phone)
             . "&text=" . urlencode($message) . "&apikey=" . urlencode($apikey);
        $resp = $this->httpGet($url);
        if ($resp === false) return "CallMeBot request failed";
        // CallMeBot returns "Message Queued" on success; anything else is an error/limit.
        if (stripos($resp, 'queued') !== false || stripos($resp, 'sent') !== false) {
            return true;
        }
        return "CallMeBot: " . substr(strip_tags($resp), 0, 200);
    }

    private function sendViaWebhook($recipient, $message) {
        $cfg = $this->config['webhook'] ?? [];
        if (empty($cfg['url'])) return "Webhook url not configured";
        $body = $cfg['body_template'] ?? json_encode(['phone' => $recipient, 'message' => $message]);
        $body = str_replace(['{{phone}}', '{{message}}'], [$recipient, $message], $body);
        $ch = curl_init($cfg['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $this->headerArray($cfg['headers'] ?? [])),
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $code >= 400) return "Webhook HTTP $code";
        return true;
    }

    private function headerArray($headers) {
        $out = [];
        foreach ($headers as $k => $v) $out[] = "$k: $v";
        return $out;
    }

    private function httpGet($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => true]);
            $r = curl_exec($ch);
            curl_close($ch);
            return $r;
        }
        $ctx = stream_context_create(['http' => ['timeout' => 15], 'https' => ['timeout' => 15]]);
        return @file_get_contents($url, false, $ctx);
    }

    private function formatOrderMessage($saleObj, $channel) {
        if (!is_object($saleObj)) return null;
        $lines = [];
        $lines[] = "🛒 NEW ORDER — " . strtoupper($channel);
        if (isset($saleObj->ref)) $lines[] = "Ref: " . $saleObj->ref;

        // Order number from orderdata
        if (isset($saleObj->orderdata)) {
            foreach ($saleObj->orderdata as $od) {
                if (isset($od->id)) { $lines[] = "Order #: " . $od->id; break; }
            }
        }
        if (isset($saleObj->order_type)) $lines[] = "Type: " . $saleObj->order_type;
        if (isset($saleObj->table_number) && $saleObj->table_number) $lines[] = "Table: " . $saleObj->table_number;

        // Customer
        if (isset($saleObj->customer) && is_object($saleObj->customer)) {
            $c = $saleObj->customer;
            $cname = $c->name ?? '';
            $cphone = $c->phone ?? ($c->mobile ?? '');
            if ($cname || $cphone) {
                $lines[] = "Customer: " . trim($cname . " " . $cphone);
            }
        }
        if (isset($saleObj->delivery_address) && $saleObj->delivery_address) {
            $lines[] = "Address: " . (is_string($saleObj->delivery_address) ? $saleObj->delivery_address : json_encode($saleObj->delivery_address));
        }
        if (isset($saleObj->table_number) && $saleObj->table_number) {
            $lines[] = "Table: " . $saleObj->table_number;
        }

        // Items
        $lines[] = "Items:";
        if (isset($saleObj->items) && is_object($saleObj->items)) {
            foreach ($saleObj->items as $it) {
                $qty = $it->qty ?? 1;
                $name = $it->name ?? 'Item';
                $price = isset($it->price) ? (float)$it->price : 0;
                $lines[] = "  " . $qty . "x " . $name . " - KES " . number_format($price * $qty, 2);
                if (!empty($it->notes)) $lines[] = "     (" . $it->notes . ")";
            }
        }
        if (isset($saleObj->total)) $lines[] = "Total: KES " . number_format((float)$saleObj->total, 2);
        if (isset($saleObj->notes) && $saleObj->notes) $lines[] = "Notes: " . $saleObj->notes;

        return implode("\n", $lines);
    }

    private function bumpAttempts($id) {
        $stmt = $this->db->_db->prepare("UPDATE wpos_whatsapp_queue SET attempts = attempts + 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function setStatus($id, $status, $error) {
        $stmt = $this->db->_db->prepare("UPDATE wpos_whatsapp_queue SET status = :s, last_error = :e, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':s' => $status, ':e' => $error, ':id' => $id]);
    }
}
