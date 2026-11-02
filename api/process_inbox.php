<?php
/**
 * process_inbox.php - replays durable inbound webhook rows that failed or are
 * still pending. Provides redundancy: if a webhook ingest threw, the raw payload
 * is preserved in wpos_order_inbox and replayed here.
 *
 * Intended to be triggered by a cron job (or manually):
 *   curl -s https://pos.cornerbites.co.ke/process_inbox.php
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

$inbox = new OrderInbox();
$rows = $inbox->getReplayable();
$processed = 0;
$errors = 0;
$dead = 0;

foreach ($rows as $row) {
    $inbox->incrementAttempts($row['id']);
    $data = json_decode($row['payload']);
    if (!$data) {
        $inbox->mark($row['id'], 'dead', 'Invalid JSON payload');
        $dead++;
        continue;
    }
    $normalized = OrderNormalizer::normalize($row['channel'], $data);
    if (isset($normalized->error)) {
        $inbox->mark($row['id'], 'failed', $normalized->error);
        $errors++;
        continue;
    }
    try {
        $sale = new WposPosSale($normalized);
        $result = $sale->ingestOrder(['errorCode' => 'OK', 'error' => 'OK', 'data' => '']);
        if (isset($result['error']) && $result['error'] !== 'OK') {
            $inbox->mark($row['id'], 'failed', $result['error']);
            $errors++;
        } else {
            $inbox->mark($row['id'], 'done', null);
            $processed++;
        }
    } catch (Exception $ex) {
        $inbox->mark($row['id'], 'failed', $ex->getMessage());
        $errors++;
    }
}

header('Content-Type: application/json');
echo json_encode(['processed' => $processed, 'errors' => $errors, 'dead' => $dead]);
