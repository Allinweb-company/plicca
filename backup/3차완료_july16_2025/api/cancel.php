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
            'raw_input' => $raw_input
        ]
    ]);
    exit;
}

// Required fields validation
$requiredFields = ['tid', 'amount'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: {$field}"]);
        exit;
    }
}

$tid = trim($input['tid']);
$amount = intval($input['amount']);
$reason = isset($input['reason']) ? trim($input['reason']) : '고객 요청';

// Additional validation
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Amount must be greater than 0']);
    exit;
}

// Generate cancellation data
$mid = FINTREE_MERCHANT_ID;
$merchantKey = FINTREE_MERCHANT_KEY;
$canDate = date("YmdHis");
$canAmt = $amount;
$encData = generateHash($mid, $canDate, $canAmt, $merchantKey);

// URLs for Fintree callback
$deploymentDomain = 'simple-payment-portal-gonskykim.replit.app';
$notiUrl = 'https://' . $deploymentDomain . '/cancel_notification.php';

// Prepare cancellation request data
$cancelData = [
    'mid' => $mid,
    'tid' => $tid,
    'canDate' => $canDate,
    'canAmt' => $canAmt,
    'canMsg' => $reason,
    'notiUrl' => $notiUrl,
    'encData' => $encData
];

// Log the cancellation request
logError("API Cancel Request", [
    'tid' => $tid,
    'canAmt' => $canAmt,
    'reason' => $reason
]);

// Make API request to Fintree
$apiUrl = FINTREE_API_URL . '/cancel.do';
$response = makeHttpRequest($apiUrl, $cancelData);

if ($response === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to connect to payment gateway'
    ]);
    exit;
}

// Parse response
$responseData = [];
parse_str($response, $responseData);

// Log the response
logError("API Cancel Response", $responseData);

// Check if cancellation was successful
$success = isset($responseData['resCd']) && $responseData['resCd'] === '0000';

if ($success) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'tid' => $responseData['tid'] ?? $tid,
        'canAmt' => $responseData['canAmt'] ?? $canAmt,
        'canDate' => $responseData['canDate'] ?? $canDate,
        'message' => '결제가 성공적으로 취소되었습니다.',
        'details' => [
            'cancelledAmount' => $canAmt,
            'cancelDate' => $canDate,
            'reason' => $reason,
            'transactionId' => $responseData['tid'] ?? $tid
        ]
    ]);
} else {
    $errorMessage = $responseData['resMsg'] ?? 'Unknown error occurred';
    $errorCode = $responseData['resCd'] ?? 'UNKNOWN';
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $errorMessage,
        'errorCode' => $errorCode,
        'details' => [
            'tid' => $tid,
            'requestedAmount' => $canAmt,
            'reason' => $reason
        ]
    ]);
}
?>