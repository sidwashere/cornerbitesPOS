<?php
/**
 * website_upload.php - Auth-guarded image upload for the Website CMS.
 * POST multipart: file (image/*), optional name. Saves to uploads/website/.
 * Returns { ok:true, url:'/uploads/website/<file>' }.
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
    http_response_code(400); echo json_encode(['error' => 'No valid file uploaded']); exit;
}
$f = $_FILES['file'];
if ($f['size'] > 5 * 1024 * 1024) { http_response_code(400); echo json_encode(['error' => 'Image must be under 5MB']); exit; }

$check = getimagesize($f['tmp_name']);
if ($check === false || strpos($check['mime'], 'image/') !== 0) {
    http_response_code(400); echo json_encode(['error' => 'Only image files allowed']); exit;
}
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
if (!in_array($ext, $allowed, true)) { http_response_code(400); echo json_encode(['error' => 'Extension not allowed (jpg/png/webp/gif/svg)']); exit; }
if ($ext === 'svg') { $ext = 'svg'; } // keep; getimagesize validates svg variants loosely — extension check above covers intent

$dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/website';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$base = preg_replace('/[^a-z0-9_-]+/i', '-', pathinfo($f['name'], PATHINFO_FILENAME));
$base = trim($base, '-');
if ($base === '') $base = 'image';
$fname = $base . '-' . substr(md5(uniqid(rand(), true)), 0, 8) . '.' . $ext;
$dest = $dir . '/' . $fname;

if (!move_uploaded_file($f['tmp_name'], $dest)) {
    http_response_code(500); echo json_encode(['error' => 'Upload failed on server']); exit;
}
chmod($dest, 0644);
echo json_encode(['ok' => true, 'url' => '/uploads/website/' . $fname, 'name' => $fname, 'size' => $f['size']]);
