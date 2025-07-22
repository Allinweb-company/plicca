<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/config.php';

// Get specific product if ID is provided
$product_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($product_id) {
    // Return specific product
    $product = null;
    foreach ($products as $p) {
        if ($p['id'] == $product_id) {
            $product = $p;
            break;
        }
    }
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
} else {
    // Return all products
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products)
    ]);
}
?>