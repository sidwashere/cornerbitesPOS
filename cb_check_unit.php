<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$pdo = (new DbConfig())->_db;
foreach ($pdo->query("SHOW COLUMNS FROM stock_history") as $c) if ($c['Field']==='unit') echo "unit: " . $c['Type'] . " default=" . $c['Default'] . "\n";
echo "OK\n";
