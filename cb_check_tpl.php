<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$path = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . "docs/templates/receipt.mustache";
echo "path: " . $path . "\n";
echo "exists: " . (file_exists($path) ? "yes" : "no") . "\n";
if (file_exists($path)) {
  $c = file_get_contents($path);
  echo "has Tel phone: " . (strpos($c, "Tel: {{phone}}") !== false ? "yes" : "no") . "\n";
  echo "size: " . strlen($c) . "\n";
}
