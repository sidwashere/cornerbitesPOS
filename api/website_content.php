<?php
/**
 * website_content.php - Returns website content as JSON for the website to consume.
 * Public endpoint (no auth required) so the website can fetch it.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, must-revalidate');

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/website_content.json';
if (file_exists($path)) {
    echo file_get_contents($path);
} else {
    echo json_encode(['offers' => [], 'prices' => [], 'ads' => [], 'general' => []]);
}