<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
$root = dirname(__FILE__);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['APP_ROOT'] = "/";
chdir($root);

$dbconf = json_decode(file_get_contents($root . '/library/wpos/.dbconfig.json'), true);
$dsn = "mysql:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['database']}";
$pdo = new PDO($dsn, $dbconf['user'], $dbconf['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "Devices:\n";
foreach ($pdo->query("SELECT * FROM devices") as $row) {
    echo json_encode($row) . "\n";
}
echo "Device_map:\n";
foreach ($pdo->query("SELECT * FROM device_map") as $row) {
    echo json_encode($row) . "\n";
}
echo "Locations:\n";
foreach ($pdo->query("SELECT * FROM locations") as $row) {
    echo json_encode($row) . "\n";
}
