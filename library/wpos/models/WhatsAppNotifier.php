<?php
/**
 * WhatsAppNotifier - NO-OP stub.
 *
 * WhatsApp notifications are no longer sent server-side.
 * Instead, when a customer places an order on the website, the browser opens
 * a direct WhatsApp chat (wa.me/<business_number>) with a pre-filled order
 * message. The customer sends it themselves; the POS still receives the order
 * via the webhook as usual.
 *
 * This class is retained as a no-op so any legacy callers don't break.
 */
class WhatsAppNotifier {
    public function __construct() {}
    public function enqueueOrder($saleObj, $channel) { return; }
    public function processQueue() { return ['sent' => 0, 'failed' => 0, 'dead' => 0, 'skipped' => 0]; }
}