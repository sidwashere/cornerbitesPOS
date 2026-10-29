<?php
/**
 * Headless installer for running WallacePOS under `php -S` (no Apache GUI installer).
 * Equivalent to installer/index.php?install — applies library/installer/schemas/install.sql
 * and seeds the settings version, using library/wpos/.dbconfig.json for DB credentials.
 *
 * Usage: php setup/install_db.php
 */
$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['APP_ROOT'] = "/";
chdir($root);

require($root . '/library/wpos/config.php');
require_once($root . '/library/autoload.php');
require($root . '/library/wpos/AutoLoader.php');

$dbconf = json_decode(file_get_contents($root . '/library/wpos/.dbconfig.json'), true);
$dsn = "mysql:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['database']}";
$pdo = new PDO($dsn, $dbconf['user'], $dbconf['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// skip if already installed
try {
    $pdo->query("SELECT 1 FROM `auth` LIMIT 1");
    echo "Database already installed, skipping schema import.\n";
} catch (Exception $ex) {
    $sql = file_get_contents($root . '/library/installer/schemas/install.sql');
    $pdo->exec($sql);
    echo "Schema installed.\n";
    // default admin:admin, disable the staff user (same as the GUI installer does)
    $pdo->exec("UPDATE `auth` SET `disabled`=1 WHERE `id`=2");
    echo "Staff account disabled (admin/admin remains).\n";
}

// seed the version setting the app expects (config table stores json in `data`)
$settings = json_decode($pdo->query("SELECT `data` FROM `config` WHERE `name`='general' LIMIT 1")->fetch(PDO::FETCH_ASSOC)['data'] ?? '{}', true);
$settings['version'] = '1.4.3';
$stmt = $pdo->prepare("UPDATE `config` SET `data`=? WHERE `name`='general'");
$stmt->execute([json_encode($settings)]);
echo "General settings version set.\n";
echo "DONE\n";
