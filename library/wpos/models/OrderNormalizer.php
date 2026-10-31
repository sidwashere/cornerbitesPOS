<?php
/**
 * OrderNormalizer - converts platform-specific webhook payloads into the WPOS
 * order format (items map + orderdata + orderstatus + ms processdt).
 *
 * Shared by api/webhooks.php (live ingest) and api/process_inbox.php (replay).
 */
class OrderNormalizer {
    public static function normalize($platform, $data) {
        $out = new stdClass();
        $out->source = $platform;
        $out->processdt = round(microtime(true) * 1000); // milliseconds (frontend expects ms)

        switch ($platform) {
            case 'website':
                $out->external_ref = self::pick($data, ['external_ref', 'order_id', 'id', 'orderId']);
                $out->order_type = self::pick($data, ['order_type', 'orderType'], 'pickup');
                $out->customer = self::pick($data, ['customer'], null);
                $out->delivery_address = self::pick($data, ['delivery_address', 'deliveryAddress'], null);
                if (isset($data->table_number) && $data->table_number) {
                    $out->table_number = (int)$data->table_number;
                }
                $out->notes = self::pick($data, ['special_instructions', 'notes', 'instructions'], null);
                $out->priority = (int)self::pick($data, ['priority'], 0);
                $rawItems = self::pick($data, ['items'], []);
                break;

            case 'glovo':
                $out->external_ref = self::pick($data, ['order_id', 'id', 'orderId']);
                $out->order_type = 'delivery';
                $out->customer = self::pick($data, ['customer'], null);
                $out->delivery_address = self::pick($data, ['delivery_address', 'deliveryAddress', 'address'], null);
                $out->notes = self::pick($data, ['comment', 'special_instructions', 'notes'], null);
                $out->priority = 5;
                $out->platform_metadata = $data;
                $rawItems = self::pick($data, ['products', 'items', 'cart'], []);
                break;

            case 'ubereats':
                $out->external_ref = self::pick($data, ['order_id', 'id', 'orderId']);
                $out->order_type = strtolower(self::pick($data, ['fulfillment_type', 'fulfillmentType'], 'delivery'));
                $out->customer = self::pick($data, ['customer'], null);
                $out->delivery_address = self::pick($data, ['delivery_address', 'deliveryAddress'], null);
                $out->notes = self::pick($data, ['special_instructions', 'notes', 'instructions'], null);
                $out->priority = 5;
                $out->platform_metadata = $data;
                $rawItems = self::pick($data, ['items', 'cart'], []);
                if (empty($rawItems) && isset($data->cart) && isset($data->cart->items)) {
                    $rawItems = $data->cart->items;
                }
                break;

            case 'phone':
                $out->external_ref = self::pick($data, ['ref', 'order_id', 'id'], null);
                $out->order_type = self::pick($data, ['order_type', 'orderType'], 'pickup');
                $out->customer = self::pick($data, ['customer'], null);
                $out->delivery_address = self::pick($data, ['delivery_address', 'deliveryAddress'], null);
                $out->notes = self::pick($data, ['special_instructions', 'notes', 'instructions'], null);
                $out->priority = (int)self::pick($data, ['priority'], 0);
                $rawItems = self::pick($data, ['items'], []);
                break;

            default:
                $out->error = "Unsupported platform";
                return $out;
        }

        $common = [];
        foreach ((array)$rawItems as $ri) {
            $ci = self::extractItem($ri);
            if ($ci === null) continue;
            $common[] = $ci;
        }
        if (count($common) === 0) {
            $out->error = "No valid items found in payload";
            return $out;
        }

        $itemsMap = new stdClass();
        $orderItemKeys = [];
        $idx = 0;
        foreach ($common as $ci) {
            $key = (string)$idx;
            $itemsMap->$key = (object)[
                'qty'   => (int)$ci['qty'],
                'name'  => $ci['name'],
                'price' => (float)$ci['price'],
                'notes' => $ci['notes'],
                'mod'   => (object)['items' => []],
            ];
            $orderItemKeys[] = $key;
            $idx++;
        }

        $orderSeq = (int)date('Gis');
        if (!empty($out->external_ref) && ctype_digit((string)$out->external_ref)) {
            $orderSeq = (int)$out->external_ref;
        }

        $orderObj = (object)[
            'id'        => $orderSeq,
            'items'     => $orderItemKeys,
            'processdt' => $out->processdt,
            'received'  => false,
            'tablenum'  => 0,
        ];
        $orderdata = new stdClass();
        $orderdata->$orderSeq = $orderObj;

        $out->items = $itemsMap;
        $out->orderdata = $orderdata;
        $out->orderstatus = 'queued';

        return $out;
    }

    public static function extractItem($ri) {
        if (!is_object($ri) && !is_array($ri)) return null;
        $ri = (object)$ri;

        $name = self::pick($ri, ['name', 'product_name', 'title', 'item', 'description']);
        if ($name === null || $name === '') return null;

        $qty = self::pick($ri, ['qty', 'quantity', 'count', 'number', 'num']);
        $qty = $qty === null ? 1 : (int)$qty;
        if ($qty < 1) $qty = 1;

        $price = self::pick($ri, ['price', 'unit_price', 'unitPrice', 'amount', 'total_price', 'value']);
        if (is_object($price) || is_array($price)) {
            $price = self::pick((object)$price, ['amount', 'value', 'total']);
        }
        $price = $price === null ? 0 : (float)$price;

        $notes = self::pick($ri, ['notes', 'comment', 'special_instructions', 'instructions', 'modifiers']);
        if (is_object($notes) || is_array($notes)) $notes = json_encode($notes);

        return [
            'name'  => (string)$name,
            'qty'   => $qty,
            'price' => $price,
            'notes' => $notes ? (string)$notes : '',
        ];
    }

    public static function pick($obj, $keys, $default = null) {
        if (!is_object($obj) && !is_array($obj)) return $default;
        foreach ($keys as $k) {
            if (is_object($obj) && property_exists($obj, $k) && $obj->$k !== null && $obj->$k !== '') {
                return $obj->$k;
            }
            if (is_array($obj) && array_key_exists($k, $obj) && $obj[$k] !== null && $obj[$k] !== '') {
                return $obj[$k];
            }
        }
        return $default;
    }
}
