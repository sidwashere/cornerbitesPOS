<?php
/**
 * menu.php - Public endpoint returning menu items from POS for the website.
 * Returns items grouped by category. Only enabled items are included.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, must-revalidate');

$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

try {
    $db = new DbConfig();
    $pdo = $db->_db;

    // Get categories
    $stmt = $pdo->query("SELECT id, name FROM stored_categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get items
    $stmt = $pdo->query("SELECT id, name, price, categoryid, data FROM stored_items ORDER BY id");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $menu = [];
    foreach ($categories as $cat) {
        $catItems = [];
        foreach ($items as $item) {
            if ($item['categoryid'] == $cat['id']) {
                $data = json_decode($item['data'], true);
                // Skip disabled items
                if (isset($data['disabled']) && $data['disabled']) continue;
                $catItems[] = [
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'price' => (float)$item['price'],
                    'category' => $cat['name'],
                    'image' => isset($data['image']) ? $data['image'] : '',
                    'description' => isset($data['description']) ? $data['description'] : '',
                ];
            }
        }
        if (count($catItems) > 0) {
            $menu[] = [
                'category' => $cat['name'],
                'items' => $catItems,
            ];
        }
    }

    echo json_encode(['ok' => true, 'menu' => $menu]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}