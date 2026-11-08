<?php
/**
 * Migrate: category images (tray/website share one image source).
 * Run: php setup/migrate_category_image.php
 * Idempotent.
 */
$_SERVER['APP_ROOT'] = "/";
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$pdo = (new DbConfig())->_db;
$cols = [];
foreach ($pdo->query("SHOW COLUMNS FROM stored_categories") as $r) $cols[$r['Field']] = true;
if (!isset($cols['image'])) {
    $pdo->exec("ALTER TABLE stored_categories ADD COLUMN image VARCHAR(512) NOT NULL DEFAULT ''");
    echo "image column added\n";
} else {
    echo "already present\n";
}
echo "Done.\n";
