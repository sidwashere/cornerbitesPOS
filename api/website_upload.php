<?php
/**
 * website_upload.php - Auth-guarded image upload for the Website CMS.
 * POST multipart: file (image/*), optional name. Saves to uploads/website/.
 * Returns { ok:true, url:'/uploads/website/<file>' }.
 *
 * Limits are raised via .htaccess (LiteSpeed php_value): upload_max_filesize=20M.
 * The CMS also downsizes big photos client-side before sending, so normal
 * phone photos always get through.
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    // Fallback: accept a persisted auth_hash (from localStorage cb_auth) so uploads
    // still work when the PHP session cookie has gone stale. Mirrors authrenew.
    $u = isset($_REQUEST['username']) ? trim((string)$_REQUEST['username']) : (isset($_POST['username']) ? trim((string)$_POST['username']) : '');
    $h = isset($_REQUEST['auth_hash']) ? trim((string)$_REQUEST['auth_hash']) : (isset($_POST['auth_hash']) ? trim((string)$_POST['auth_hash']) : '');
    $renewed = false;
    if ($u !== '' && $h !== '') {
        try { $renewed = (bool)$auth->renewTokenSession($u, $h); } catch (Exception $e) { $renewed = false; }
    }
    if (!$renewed || !$auth->isLoggedIn()) {
        http_response_code(403); echo json_encode(['error' => 'Unauthorized — please log in again']); exit;
    }
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err = isset($_FILES['file']) ? (int)$_FILES['file']['error'] : -1;
    $msg = 'No valid file uploaded';
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        $msg = 'That photo is too large for the server limit. Try a smaller image (under 20MB) — or use a JPG.';
    } elseif ($err === UPLOAD_ERR_PARTIAL) {
        $msg = 'The upload was interrupted — please try again.';
    } elseif (!isset($_FILES['file'])) {
        $msg = 'No file received — the photo may be larger than the server accepts. Try a smaller image.';
    }
    http_response_code(400); echo json_encode(['error' => $msg, 'php_error' => $err]); exit;
}
$f = $_FILES['file'];
if ($f['size'] > 20 * 1024 * 1024) { http_response_code(400); echo json_encode(['error' => 'Image must be under 20MB']); exit; }

// HEIC/HEIF (iPhone photos): PHP can't process them and browsers can't show them.
$name_lower = strtolower($f['name']);
if (preg_match('/\.(heic|heif)$/', $name_lower) || (function_exists('mime_content_type') && in_array(@mime_content_type($f['tmp_name']), ['image/heic', 'image/heif', 'image/heic-sequence'], true))) {
    http_response_code(400); echo json_encode(['error' => 'iPhone HEIC photos are not supported — in your iPhone camera settings set "Format: Most Compatible", or save/convert the photo to JPG first.']); exit;
}

$check = getimagesize($f['tmp_name']);
if ($check === false || strpos($check['mime'], 'image/') !== 0) {
    http_response_code(400); echo json_encode(['error' => 'Only image files allowed (JPG, PNG, WebP, GIF, SVG)']); exit;
}
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
if (!in_array($ext, $allowed, true)) { http_response_code(400); echo json_encode(['error' => 'Extension not allowed (jpg/png/webp/gif/svg)']); exit; }

$dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/website';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$base = preg_replace('/[^a-z0-9_-]+/i', '-', pathinfo($f['name'], PATHINFO_FILENAME));
$base = trim($base, '-');
if ($base === '') $base = 'image';
$fname = $base . '-' . substr(md5(uniqid(rand(), true)), 0, 8) . '.' . $ext;
$dest = $dir . '/' . $fname;

if (!move_uploaded_file($f['tmp_name'], $dest)) {
    http_response_code(500); echo json_encode(['error' => 'Upload failed on server — check folder permissions']); exit;
}
chmod($dest, 0644);
echo json_encode(['ok' => true, 'url' => '/uploads/website/' . $fname, 'name' => $fname, 'size' => $f['size']]);
