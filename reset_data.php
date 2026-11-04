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

// Tables to reset (keep stored_items, stored_suppliers, stored_categories, tax_items, tax_rules, config)
$resetTables = ['auth', 'customers', 'customer_contacts', 'devices', 'device_map', 
    'locations', 'sales', 'platform_configs', 'order_status_history', 'sale_history', 
    'sale_items', 'sale_payments', 'sale_voids', 'stock_history', 'stock_levels'];

foreach ($resetTables as $table) {
    try {
        $pdo->exec("DELETE FROM `$table`");
        echo "Cleared $table\n";
    } catch (Exception $e) {
        echo "Error clearing $table: " . $e->getMessage() . "\n";
    }
}

// Re-seed auth with staff user (admin privileges)
$pdo->exec("INSERT INTO `auth` (`id`, `username`, `name`, `password`, `token`, `uuid`, `admin`, `disabled`, `permissions`) VALUES
(1, 'staff', 'staff', '1562206543da764123c21bd524674f0a8aaf49c8a89744c97352fe677f7e4006', '', '5346788d0a8ae', 1, 0, '{\"sections\":{\"access\":\"admin\",\"dashboard\":\"both\",\"reports\":1,\"graph\":1,\"realtime\":1,\"sales\":2,\"invoices\":2,\"items\":2,\"stock\":2,\"categories\":2,\"suppliers\":2,\"customers\":2},\"apicalls\":[]}')");
echo "Re-seeded staff user\n";

// Re-seed location
$pdo->exec("INSERT INTO `locations` (`id`, `name`, `disabled`) VALUES (1, 'utawala', 0)");
echo "Re-seeded location\n";

// Re-seed device
$pdo->exec("INSERT INTO `devices` (`id`, `name`, `locationid`, `data`, `disabled`) VALUES (1, 'general register', 1, '{"uuid":"483d76551020146d79c50ab68334a3680c84456090812738b252bedbcfb7e5ad"}', 0)");
echo "Re-seeded device\n";

// Re-seed device_map
$pdo->exec("INSERT INTO `device_map` (`deviceid`, `uuid`, `ip`, `useragent`, `dt`) VALUES (1, '483d76551020146d79c50ab68334a3680c84456090812738b252bedbcfb7e5ad', '127.0.0.1', 'POS', NOW())");
echo "Re-seeded device_map\n";

echo "DONE - All data reset except products and pricing\n";
