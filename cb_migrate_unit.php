<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
echo "config loaded\n";
$pdo = (new DbConfig())->_db;
echo "db connected\n";
