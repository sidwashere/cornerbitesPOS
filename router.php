<?php
/**
 * Dev router for `php -S localhost:8000 router.php`
 * Emulates the Apache .htaccess rewrites for api/ and customerapi/,
 * and blocks direct access to library/, docs/ and installer views.
 */
$root = $_SERVER['DOCUMENT_ROOT'];
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API calls -> api/wpos.php?a=<action>  (see api/.htaccess: a = full path after /api/)
if (preg_match('#^/api/(.+)$#', $uri, $m)) {
    $_SERVER['APP_ROOT'] = "/";
    $_GET['a']  = $m[1];
    $_REQUEST['a'] = $m[1];
    require($root . '/api/wpos.php');
    return true;
}

// Customer API calls -> customerapi/customerapi.php?a=<action>
if (preg_match('#^/customerapi/(.+)$#', $uri, $m)) {
    $_SERVER['APP_ROOT'] = "/";
    $_GET['a']  = $m[1];
    $_REQUEST['a'] = $m[1];
    require($root . '/customerapi/customerapi.php');
    return true;
}

// Deny direct access to protected dirs and sensitive files
if (preg_match('#^/(library|node_modules|docs)/(?!template)#', $uri)) {
    http_response_code(403);
    return true;
}
if (preg_match('#(^|/)(\.dbconfig\.json|\.config\.json|composer\.(json|lock)|package(-lock)?\.json|Procfile|\.buildpacks|apt-packages|nginx\.conf\.sigil|install\.enabled)$#', $uri)) {
    http_response_code(403);
    return true;
}

// Serve static files as-is when they exist
$file = $root . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

return false;
