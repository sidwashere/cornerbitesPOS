<?php
/**
 * Corner Bites: sync the website menu into the POS inventory (stored_items + stored_categories).
 * Idempotent: existing categories/items (matched by name) are reused/skipped.
 * Safe to re-run. Deploy to api/ and hit it once over HTTPS.
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/autoload.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

header('Content-Type: application/json');

$MENU = json_decode('{"categories":["Launch Combos","Foods","Fries","Breakfast","Beverages"],"items":[{"name":"Pilau + 1/4 Chicken Special","category":"Launch Combos","price":340},{"name":"Fries Masala + 1/4 Chicken Special","category":"Launch Combos","price":350},{"name":"Uji Power + Free Mandazi","category":"Launch Combos","price":70},{"name":"Pilau (Swahili Beef Pilau)","category":"Foods","price":150},{"name":"Chicken 1/4 (Quarter Chicken)","category":"Foods","price":190},{"name":"Fries Masala","category":"Fries","price":160},{"name":"Fries (Chips)","category":"Fries","price":100},{"name":"Samosa","category":"Breakfast","price":50},{"name":"Uji Power","category":"Breakfast","price":70},{"name":"Mandazi (Andazi)","category":"Breakfast","price":20},{"name":"Fruit Juice","category":"Beverages","price":100},{"name":"Fruit Salad","category":"Beverages","price":50},{"name":"Soda 350ml","category":"Beverages","price":60},{"name":"Soda 500ml","category":"Beverages","price":80}]}', true);

$catMdl = new CategoriesModel();
$itemMdl = new StoredItemsModel();

$existingCats = $catMdl->get();
$catByName = array();
if ($existingCats !== false) {
    foreach ($existingCats as $c) { $catByName[strtolower(trim($c['name']))] = $c['id']; }
}

$catIdMap = array();
$catCreated = 0; $catReused = 0;
foreach ($MENU['categories'] as $catName) {
    $key = strtolower(trim($catName));
    if (isset($catByName[$key])) {
        $catIdMap[$catName] = $catByName[$key];
        $catReused++;
    } else {
        $id = $catMdl->create($catName);
        if (!is_numeric($id)) { echo json_encode(array('error'=>'Failed to create category '.$catName)); exit; }
        $catIdMap[$catName] = $id;
        $catCreated++;
    }
}

$existingItems = $itemMdl->get();
$itemByName = array();
if ($existingItems !== false) {
    foreach ($existingItems as $it) { $itemByName[strtolower(trim($it['name']))] = true; }
}

$itemsCreated = 0; $itemsSkipped = 0;
foreach ($MENU['items'] as $it) {
    $name = $it['name'];
    if (isset($itemByName[strtolower(trim($name))])) { $itemsSkipped++; continue; }
    $obj = new stdClass();
    $obj->code = 'CB' . strtoupper(substr(md5($name . $it['price']), 0, 8));
    $obj->qty = 100;
    $obj->name = $name;
    $obj->description = '';
    $obj->taxid = 1; // default "No Tax" rule; adjust in POS if VAT applies
    $obj->cost = $it['price'];
    $obj->price = $it['price'];
    $obj->type = '';
    $obj->supplierid = 0;
    $obj->categoryid = isset($catIdMap[$it['category']]) ? $catIdMap[$it['category']] : 0;
    $id = $itemMdl->create($obj);
    if (!is_numeric($id)) { echo json_encode(array('error'=>'Failed to create item '.$name.' ('.$id.')')); exit; }
    $itemsCreated++;
}

echo json_encode(array(
    'ok' => true,
    'categories_created' => $catCreated,
    'categories_reused' => $catReused,
    'items_created' => $itemsCreated,
    'items_skipped' => $itemsSkipped
));
