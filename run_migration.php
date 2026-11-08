<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
$root = dirname(__FILE__);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['APP_ROOT'] = "/";
chdir($root);

require($root . '/library/wpos/config.php');
require_once($root . '/library/autoload.php');
require($root . '/library/wpos/AutoLoader.php');

$dbconf = json_decode(file_get_contents($root . '/library/wpos/.dbconfig.json'), true);
$dsn = "mysql:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['database']}";
$pdo = new PDO($dsn, $dbconf['user'], $dbconf['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// The migration SQL uses DELIMITER which PDO can't handle directly.
// Split by the DELIMITER lines and execute each statement block.
$sql = file_get_contents($root . '/setup/migrate_all.sql');
// Remove the DELIMITER directives and split
$sql = preg_replace('/^DELIMITER\s+\$\$$/m', '', $sql);
$sql = preg_replace('/^DELIMITER\s*$/m', '', $sql);
// Split on $$ boundaries (procedure blocks) and semicolons
$blocks = preg_split('/\$\$/', $sql);
foreach ($blocks as $block) {
    $block = trim($block);
    if ($block === '') continue;
    // Execute each statement (split by semicolon for non-procedure parts)
    $stmts = array_filter(array_map('trim', explode(';', $block)));
    foreach ($stmts as $stmt) {
        if ($stmt === '' || stripos($stmt, 'SET NAMES') === 0) continue;
        try {
            $pdo->exec($stmt);
            echo "OK: " . substr(preg_replace('/\s+/', ' ', $stmt), 0, 80) . "\n";
        } catch (Exception $e) {
            echo "SKIP/ERR: " . $e->getMessage() . " -- " . substr(preg_replace('/\s+/', ' ', $stmt), 0, 60) . "\n";
        }
    }
}
echo "MIGRATION DONE\n";
PY