<?php
/**
 * Seed POS categories & products from the public website menu.
 *
 * Reads the product cards in website/index.html (name, price, category,
 * description and image) and creates/updates them in the existing POS
 * database (library/wpos/.dbconfig.json -> wpos, existing schema). Safe to
 * re-run: items are matched by name and updated in place. Also ensures a
 * stock_levels row exists for the main location (Utawala, id 1) so Admin
 * Stock is not empty; existing stock levels are left untouched.
 *
 * Usage: php setup/seed_website_products.php
 */
$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['APP_ROOT'] = '/';
chdir($root);

$dbconf = json_decode(file_get_contents($root . '/library/wpos/.dbconfig.json'), true);
if (!$dbconf) {
    fwrite(STDERR, "Missing library/wpos/.dbconfig.json\n");
    exit(1);
}

$dsn = "mysql:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['database']};charset=utf8";
$pdo = new PDO($dsn, $dbconf['user'], $dbconf['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$html = file_get_contents($root . '/website/index.html');
if ($html === false) {
    fwrite(STDERR, "Could not read website/index.html\n");
    exit(1);
}

$document = new DOMDocument();
libxml_use_internal_errors(true);
$document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
libxml_clear_errors();

$xpath = new DOMXPath($document);
$cards = $xpath->query(
    "//div[contains(concat(' ', normalize-space(@class), ' '), ' food-item ')]"
);
if ($cards->length === 0) {
    fwrite(STDERR, "No product cards found in website/index.html\n");
    exit(1);
}

// Website category slug => POS category name (also used on the POS till).
$categoryNames = [
    'launch-combos' => 'Launch Offers',
    'mains'         => 'Chicken & Pilau',
    'chips'         => 'Chips & Masala',
    'breakfast'     => 'Uji & Bites',
    'drinks'        => 'Juice & Sodas',
];

$categoryTypes = [
    'launch-combos' => 'food',
    'mains'         => 'food',
    'chips'         => 'food',
    'breakfast'     => 'food',
    'drinks'        => 'beverage',
];

function textOf(DOMXPath $xpath, DOMNode $context, string $query): string
{
    $nodes = $xpath->query($query, $context);
    if (!$nodes || $nodes->length === 0) {
        return '';
    }
    return trim(preg_replace('/\s+/', ' ', $nodes->item(0)->textContent));
}

function stockCode(string $name): string
{
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $name));
    return substr($code !== '' ? $code : 'ITEM', 0, 40);
}

$categoryIds = [];
$insertCategory = $pdo->prepare(
    "INSERT INTO stored_categories (name, dt) VALUES (:name, NOW())"
);
$findCategory = $pdo->prepare(
    "SELECT id FROM stored_categories WHERE name = :name ORDER BY id LIMIT 1"
);
$updateCategory = $pdo->prepare(
    "UPDATE stored_categories SET name = :name WHERE id = :id"
);

$findItemByName = $pdo->prepare(
    "SELECT id FROM stored_items WHERE name = :name ORDER BY id LIMIT 1"
);
$findItemByCode = $pdo->prepare(
    "SELECT id FROM stored_items WHERE code = :code ORDER BY id LIMIT 1"
);
$insertItem = $pdo->prepare(
    "INSERT INTO stored_items (data, supplierid, categoryid, code, name, price)
     VALUES (:data, :supplierid, :categoryid, :code, :name, :price)"
);
$updateItem = $pdo->prepare(
    "UPDATE stored_items
     SET data = :data, supplierid = :supplierid, categoryid = :categoryid,
         code = :code, name = :name, price = :price
     WHERE id = :id"
);

$findStock = $pdo->prepare(
    "SELECT id FROM stock_levels WHERE storeditemid = :itemid AND locationid = :locid LIMIT 1"
);
$insertStock = $pdo->prepare(
    "INSERT INTO stock_levels (storeditemid, locationid, stocklevel, dt) VALUES (:itemid, :locid, 999, NOW())"
);
$stockLocationId = 1; // Main shop: Utawala

$created = 0;
$updated = 0;
$skipped = 0;
$stockCreated = 0;
$seenCodes = [];

foreach ($cards as $card) {
    $name = textOf($xpath, $card, ".//h3");
    if ($name === '') {
        $skipped++;
        continue;
    }

    $categorySlug = $card->getAttribute('data-category');
    $priceText = textOf($xpath, $card, ".//span[contains(@class, 'font-price-display')]");
    if (!preg_match('/([0-9]+(?:\.[0-9]+)?)/', $priceText, $matches)) {
        $skipped++;
        continue;
    }
    $price = $matches[1];
    $description = textOf($xpath, $card, ".//p");
    $image = '';
    $images = $xpath->query(".//img", $card);
    if ($images && $images->length > 0) {
        $image = $images->item(0)->getAttribute('src');
    }

    $categoryName = $categoryNames[$categorySlug] ?? 'Miscellaneous';
    $itemType = $categoryTypes[$categorySlug] ?? 'food';

    if (!array_key_exists($categorySlug, $categoryIds)) {
        $findCategory->execute([':name' => $categoryName]);
        $category = $findCategory->fetch();
        if ($category) {
            $categoryId = (int)$category['id'];
            $updateCategory->execute([':name' => $categoryName, ':id' => $categoryId]);
        } else {
            $insertCategory->execute([':name' => $categoryName]);
            $categoryId = (int)$pdo->lastInsertId();
        }
        $categoryIds[$categorySlug] = $categoryId;
    }
    $categoryId = $categoryIds[$categorySlug];

    $code = stockCode($name);
    $baseCode = $code;
    $suffix = 2;
    while (isset($seenCodes[$code])) {
        $code = substr($baseCode, 0, 36) . '-' . $suffix;
        $suffix++;
    }
    $seenCodes[$code] = true;

    $data = [
        'code'        => $code,
        'qty'         => 999,
        'name'        => $name,
        'alt_name'    => '',
        'description' => $description,
        'image'       => $image,
        'taxid'       => 1,
        'cost'        => '0.00',
        'price'       => number_format((float)$price, 2, '.', ''),
        'supplierid'  => 0,
        'categoryid'  => $categoryId,
        'type'        => $itemType,
        'modifiers'   => [],
    ];
    $payload = [
        ':data'       => json_encode($data, JSON_UNESCAPED_SLASHES),
        ':supplierid' => 0,
        ':categoryid' => $categoryId,
        ':code'       => $code,
        ':name'       => $name,
        ':price'      => $data['price'],
    ];

    $findItemByName->execute([':name' => $name]);
    $existing = $findItemByName->fetch();
    if (!$existing) {
        $findItemByCode->execute([':code' => $code]);
        $existing = $findItemByCode->fetch();
    }

    if ($existing) {
        $payload[':id'] = (int)$existing['id'];
        $updateItem->execute($payload);
        $updated++;
        echo "Updated: {$name} ({$data['price']})\n";
        $itemId = (int)$existing['id'];
    } else {
        $insertItem->execute($payload);
        $created++;
        echo "Added:   {$name} ({$data['price']})\n";
        $itemId = (int)$pdo->lastInsertId();
    }

    // Ensure stock row for main location; do not override existing stock.
    $findStock->execute([':itemid' => $itemId, ':locid' => $stockLocationId]);
    if (!$findStock->fetch()) {
        $insertStock->execute([':itemid' => $itemId, ':locid' => $stockLocationId]);
        $stockCreated++;
    }
}

$countItems = (int)$pdo->query("SELECT COUNT(*) FROM stored_items")->fetchColumn();
$countCategories = (int)$pdo->query("SELECT COUNT(*) FROM stored_categories")->fetchColumn();

echo "\nDone. Created {$created}, updated {$updated}, skipped {$skipped}. Stock rows created: {$stockCreated}.\n";
echo "Database now has {$countItems} products in {$countCategories} categories.\n";
