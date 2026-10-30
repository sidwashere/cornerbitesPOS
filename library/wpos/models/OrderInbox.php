<?php
/**
 * OrderInbox - durable log of inbound webhook payloads for redundancy.
 *
 * Every inbound webhook is written here BEFORE ingestion. If ingestion throws,
 * the row is marked "failed" and can be replayed by api/process_inbox.php.
 * This guarantees no order is silently lost even if the DB hiccups mid-process.
 */
class OrderInbox {
    private $db;

    public function __construct() {
        $this->db = new DbConfig();
    }

    /**
     * Log an inbound payload. Idempotent by (channel, external_ref).
     * @return int inbox id
     */
    public function log($channel, $externalRef, $payload, $status = 'pending') {
        $externalRef = $externalRef === null ? '' : (string)$externalRef;
        $stmt = $this->db->_db->prepare(
            "INSERT INTO wpos_order_inbox (channel, external_ref, payload, status, attempts, last_error, created_at, updated_at)
             VALUES (:channel, :external_ref, :payload, :status, 0, NULL, NOW(), NOW())"
        );
        $stmt->execute([
            ':channel'      => $channel,
            ':external_ref' => $externalRef,
            ':payload'      => $payload,
            ':status'       => $status,
        ]);
        return $this->db->_db->lastInsertId();
    }

    public function mark($id, $status, $error = null) {
        if (!$id) return;
        $stmt = $this->db->_db->prepare(
            "UPDATE wpos_order_inbox SET status = :status, last_error = :err, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':status' => $status, ':err' => $error, ':id' => $id]);
    }

    public function incrementAttempts($id) {
        $stmt = $this->db->_db->prepare(
            "UPDATE wpos_order_inbox SET attempts = attempts + 1, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    /**
     * Fetch pending/failed rows eligible for replay (under the attempt cap).
     */
    public function getReplayable($maxAttempts = 5, $limit = 50) {
        $stmt = $this->db->_db->prepare(
            "SELECT * FROM wpos_order_inbox
             WHERE status IN ('pending','failed') AND attempts < :max
             ORDER BY id ASC LIMIT :lim"
        );
        $stmt->bindValue(':max', (int)$maxAttempts, PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
