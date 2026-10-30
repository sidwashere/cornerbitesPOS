<?php
/**
 * OrderStatusHistoryModel is part of Wallace Point of Sale system (WPOS) API
 *
 * Tracks order status transitions for audit and analytics.
 */
class OrderStatusHistoryModel extends DbConfig
{
    protected $_columns = ['id', 'sale_id', 'from_status', 'to_status', 'changed_by', 'changed_at', 'notes'];

    public function __construct()
    {
        parent::__construct();
    }

    public function log($saleId, $fromStatus, $toStatus, $changedBy = null, $notes = null)
    {
        $sql = "INSERT INTO order_status_history (sale_id, from_status, to_status, changed_by, changed_at, notes) VALUES (:sale_id, :from_status, :to_status, :changed_by, '".date("Y-m-d H:i:s")."', :notes)";
        $placeholders = [
            ':sale_id'     => $saleId,
            ':from_status' => $fromStatus,
            ':to_status'   => $toStatus,
            ':changed_by'  => $changedBy,
            ':notes'       => $notes
        ];
        return $this->insert($sql, $placeholders);
    }

    public function getBySaleId($saleId)
    {
        $sql = 'SELECT * FROM order_status_history WHERE sale_id = :sale_id ORDER BY changed_at ASC';
        $placeholders = [':sale_id' => $saleId];
        return $this->select($sql, $placeholders);
    }
}
