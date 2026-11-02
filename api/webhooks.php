<?php
/**
 * webhooks.php - Receives webhooks from external platforms (website, Glovo, Uber Eats, phone)
 *
 * Each platform sends a POST request with a JSON payload (and optionally a signature header).
 * The webhook is verified, normalized into the WPOS order format, durably logged to an inbox
 * for redundancy, ingested as an order, and a WhatsApp notification is enqueued (decoupled).
 *
 * Realtime note: the node.js feed server cannot run on the shared host, so new orders are
 * delivered to the kitchen/admin via the polling endpoint (api/poll.php) instead of websockets.
 */
$_SERVER['APP_ROOT'] = "/";

require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
set_error_handler("errorHandler", E_ERROR | E_PARSE);
set_error_handler("warningHandler", E_WARNING);
set_exception_handler("exceptionHandler");
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

// --- CORS: allow the public website to POST orders cross-origin ---
$allowedOrigins = [];
$_cfgPath = $_SERVER['DOCUMENT_ROOT'] . '/docs/.config.json';
if (file_exists($_cfgPath)) {
    $_cfg = @json_decode(file_get_contents($_cfgPath));
    if (!empty($_cfg->app_url)) {
        $allowedOrigins[] = rtrim($_cfg->app_url, '/');
    }
}
foreach (['https://cornerbites.co.ke', 'https://www.cornerbites.co.ke'] as $_siteOrigin) {
    $allowedOrigins[] = $_siteOrigin;
}
$requestOrigin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';
if ($requestOrigin !== '' && in_array($requestOrigin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $requestOrigin);
    header("Vary: Origin");
}
header("Access-Control-Allow-Methods: OPTIONS, POST");
header("Access-Control-Allow-Headers: Content-Type, X-Webhook-Signature");

// Answer the CORS preflight and stop before touching the database.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$result = ["errorCode" => "OK", "error" => "OK", "data" => ""];

// Determine platform from URL path (?platform= or /webhooks/<platform>)
$platform = isset($_GET['platform']) ? $_GET['platform'] : (isset($_REQUEST['platform']) ? $_REQUEST['platform'] : null);
if (!$platform) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#/webhooks/([^/]+)#', $uri, $m)) {
        $platform = $m[1];
    }
}

$allowedPlatforms = ['website', 'glovo', 'ubereats', 'phone'];
if (!in_array($platform, $allowedPlatforms)) {
    $result['errorCode'] = "invalid_platform";
    $result['error'] = "Unknown platform: " . $platform;
    returnResult($result);
}

// Read raw body
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody);

if (!$data) {
    $result['errorCode'] = "jsondec";
    $result['error'] = "Invalid JSON payload";
    returnResult($result);
}

// Verify webhook signature (if a secret is configured for this platform)
$signature = isset($_SERVER['HTTP_X_WEBHOOK_SIGNATURE']) ? $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] : null;
if ($signature) {
    $db = new DbConfig();
    $stmt = $db->_db->prepare("SELECT webhook_secret FROM platform_configs WHERE platform = :platform AND enabled = 1");
    $stmt->execute([':platform' => $platform]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['webhook_secret'])) {
        $expected = hash_hmac('sha256', $rawBody, $row['webhook_secret']);
        if (!hash_equals($expected, $signature)) {
            $result['errorCode'] = "auth";
            $result['error'] = "Invalid webhook signature";
            returnResult($result);
        }
    }
}

// ---------------------------------------------------------------------------
// Normalize the platform payload into the WPOS order format (items map +
// orderdata + orderstatus + ms processdt) via the shared OrderNormalizer.
// ---------------------------------------------------------------------------
$normalized = OrderNormalizer::normalize($platform, $data);
if (isset($normalized->error)) {
    $result['errorCode'] = "normalize";
    $result['error'] = $normalized->error;
    returnResult($result);
}

// Durable inbox: log the raw payload first so no order is ever lost, even if
// ingestion throws. Status is updated after processing.
$inbox = new OrderInbox();
$inboxId = $inbox->log($platform, $normalized->external_ref, $rawBody, 'pending');

// Ingest (wrapped so a failure is recorded, never silently dropped)
try {
    $sale = new WposPosSale($normalized);
    $result = $sale->ingestOrder($result);

    if (isset($result['error']) && $result['error'] !== "OK") {
        $inbox->mark($inboxId, 'failed', $result['error']);
        http_response_code(422);
        returnResult($result);
    }

    // Success: mark inbox done. (WhatsApp notification is no longer enqueued here —
    // the customer now initiates a direct WhatsApp chat from the website with the
    // order details; the POS still receives the order via this webhook as usual.)
    $inbox->mark($inboxId, 'done', null);

    http_response_code(200);
    returnResult($result);

} catch (Exception $ex) {
    $inbox->mark($inboxId, 'failed', $ex->getMessage());
    $result['errorCode'] = "ingest_exception";
    $result['error'] = "Ingestion failed: " . $ex->getMessage();
    http_response_code(500);
    returnResult($result);
}

// ---------------------------------------------------------------------------
// Shared normalizers live in library/wpos/models/OrderNormalizer.php
// ---------------------------------------------------------------------------

function returnResult($result) {
    if (($resstr = json_encode($result)) === false) {
        echo(json_encode(["error" => "Failed to encode the response data into json"]));
    } else {
        echo($resstr);
    }
    die();
}
