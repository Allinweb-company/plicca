<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/config.php';

// 세션 시작 (결제 정보 저장을 위해)
session_start();

// Get JSON input - handle different input sources
$raw_input = '';

// Try environment variable first (for Flask integration)
if (isset($_ENV['JSON_INPUT'])) {
    $raw_input = $_ENV['JSON_INPUT'];
} else if (getenv('JSON_INPUT')) {
    $raw_input = getenv('JSON_INPUT');
} else {
    // Try php://input
    $php_input = file_get_contents('php://input');
    if ($php_input) {
        $raw_input = $php_input;
    } else if (isset($_POST['json_data'])) {
        // Fallback for form-encoded JSON
        $raw_input = $_POST['json_data'];
    } else if (!empty($_POST)) {
        // If $_POST has data, encode it as JSON
        $raw_input = json_encode($_POST);
    }
}

$input = json_decode($raw_input, true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid JSON input', 
        'debug' => [
            'php_input' => $php_input,
            'post_data' => $_POST,
            'raw_input' => $raw_input
        ]
    ]);
    exit;
}

// Required fields validation for iM Web data
$requiredFields = ['orderNo', 'prodName', 'qty', 'itemPrice', 'receiverName', 'receiverCall'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: {$field}"]);
        exit;
    }
}

// Extract iM Web data
$im_order_no = trim($input['orderNo']);      // 아임웹 주문번호
$product_name = trim($input['prodName']);    // 실제 상품명
$quantity = intval($input['qty']);           // 수량
$item_price = intval($input['itemPrice']);   // 실제 결제금액
$customer_name = trim($input['receiverName']); // 실제 주문자명
$customer_phone = trim($input['receiverCall']); // 실제 연락처

// Use customer email if provided, otherwise generate temp email
$customer_email = isset($input['receiverEmail']) && !empty(trim($input['receiverEmail'])) 
    ? trim($input['receiverEmail']) 
    : 'customer_' . $im_order_no . '@temp.com';

// Additional validation
if ($quantity < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Quantity must be greater than 0']);
    exit;
}

if ($item_price < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Item price must be greater than 0']);
    exit;
}

// Use real iM Web product data (no need to find in database)
$totalAmount = $item_price; // 아임웹에서 이미 계산된 금액

// Generate payment data
$mid = FINTREE_MERCHANT_ID;
$merchantKey = FINTREE_MERCHANT_KEY;
// ORD 접두사 제거 - 주문번호 그대로 사용
$ordNo = $im_order_no;
$goodsNm = $product_name;       // 실제 상품명 사용 (수량 표시 제거)
$ediDate = date("YmdHis");
$encData = generateHash($mid, $ediDate, $totalAmount, $merchantKey);

// URLs for Fintree callback - Webflow + Wized 지원
// pageUrl can be provided in request, default to PHP server
$returnUrl = isset($input['pageUrl']) && !empty(trim($input['pageUrl'])) 
    ? trim($input['pageUrl']) 
    : 'https://simple-payment-portal-gonskykim.replit.app/payment_result.php';
$notiUrl = ''; // NOTI 사용 안함

// Prepare payment form data
$paymentData = [
    'mid' => $mid,
    'trxCd' => '0',
    'goodsNm' => $goodsNm,
    'ordNo' => $ordNo,
    'goodsAmt' => $totalAmount,
    'ordNm' => $customer_name,
    'ordTel' => $customer_phone,
    'ordEmail' => $customer_email,
    'returnUrl' => $returnUrl,
    'notiUrl' => $notiUrl,
    'userIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    'mbsUsrId' => 'api_user_' . date('His'),
    'mbsReserved' => 'APIRequest',
    'charSet' => 'UTF-8',
    'ediDate' => $ediDate,
    'encData' => $encData
];

// 세션에 결제 정보 저장 (payment_result.php에서 복원용)
$_SESSION['payment_data'] = [
    'ordNo' => $ordNo,
    'goodsNm' => $goodsNm,
    'ordNm' => $customer_name,
    'ordTel' => $customer_phone,
    'ordEmail' => $customer_email,
    'goodsAmt' => $totalAmount,
    'quantity' => $quantity,
    'productName' => $product_name
];

// Log the payment request
logError("API Payment Request - iM Web", [
    'imOrderNo' => $im_order_no,
    'ordNo' => $ordNo,
    'goodsNm' => $goodsNm,
    'goodsAmt' => $totalAmount,
    'ordNm' => $customer_name,
    'ordTel' => $customer_phone
]);

// Return payment form data for frontend
http_response_code(200);
echo json_encode([
    'success' => true,
    'ordNo' => $ordNo,
    'paymentData' => $paymentData,
    'fintreeJsUrl' => FINTREE_JS_URL,
    'summary' => [
        'imOrderNo' => $im_order_no,        // 아임웹 원본 주문번호
        'productName' => $product_name,     // 실제 상품명
        'quantity' => $quantity,
        'unitPrice' => $quantity > 0 ? intval($item_price / $quantity) : $item_price,  // 총액 ÷ 수량 = 단가
        'totalAmount' => $totalAmount,      // 실제 총액
        'customerName' => $customer_name,   // 실제 고객명
        'customerPhone' => $customer_phone,  // 실제 연락처
        'customerEmail' => $customer_email   // 실제 이메일
    ]
]);
exit;
?>