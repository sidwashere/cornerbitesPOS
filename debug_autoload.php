<?php
$_SERVER['APP_ROOT'] = "/";
$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "File exists: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . '/library/wpos/models/WposPosSale.php') ? 'YES' : 'NO') . "\n";
echo "WposPosSale defined: " . (class_exists('WposPosSale', false) ? 'YES' : 'NO') . "\n";
$loaded = class_exists('WposPosSale');
echo "WposPosSale autoloaded: " . ($loaded ? 'YES' : 'NO') . "\n";
