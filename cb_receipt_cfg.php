<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$pdo = (new DbConfig())->_db;
foreach (['general','pos'] as $name) {
  $st = $pdo->prepare("SELECT data FROM config WHERE name = :n");
  $st->execute([':n'=>$name]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) { echo "missing $name\n"; continue; }
  $d = json_decode($row['data'], true);
  if (!is_array($d)) { echo "bad json $name\n"; continue; }
  if (isset($d['recfooter'])) $d['recfooter'] = 'For new order call 0185112315';
  if (isset($d['biznumber'])) $d['biznumber'] = '0185 112 315';
  $pdo->prepare("UPDATE config SET data = :d WHERE name = :n")->execute([':d'=>json_encode($d), ':n'=>$name]);
  echo "updated $name: recfooter=" . ($d['recfooter'] ?? '-') . " biznumber=" . ($d['biznumber'] ?? '-') . "\n";
}
echo "DONE\n";
