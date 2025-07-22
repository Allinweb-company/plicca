<?php
// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

// Fintree API Configuration
// 가맹점명: 플리카
// 현재 사용중 TID 사용
define('FINTREE_MERCHANT_ID', 'mimich080m');
define('FINTREE_MERCHANT_KEY', '46wpteaZsZthCTRw0pp7uqrzlwTB3jD5XL8Pbf08HzvrlqtZDouEz+234Ys/HVnYF3iatRYwKM9gomTx3gXtYQ==');
define('FINTREE_API_URL', 'https://api.fintree.kr');
define('FINTREE_JS_URL', 'https://api.fintree.kr/js/v1/pgAsistant.js');

// Sample products for testing
$products = [
    [
        'id' => 1,
        'name' => '노트북 컴퓨터',
        'price' => 100,
        'description' => '고성능 게이밍 노트북'
    ],
    [
        'id' => 2,
        'name' => '무선 마우스',
        'price' => 100,
        'description' => '블루투스 무선 마우스'
    ],
    [
        'id' => 3,
        'name' => '키보드',
        'price' => 100,
        'description' => '기계식 게이밍 키보드'
    ],
    [
        'id' => 4,
        'name' => '모니터',
        'price' => 100,
        'description' => '27인치 4K 모니터'
    ]
];

// Utility function to format price
function formatPrice($price) {
    return number_format($price) . '원';
}

// Utility function to generate order number
function generateOrderNo() {
    return 'ORD' . date('YmdHis') . rand(1000, 9999);
}

// Utility function to generate SHA256 hash
function generateHash($mid, $ediDate, $amount, $merchantKey) {
    return bin2hex(hash('sha256', $mid . $ediDate . $amount . $merchantKey, true));
}

// Utility function to make HTTP POST request
function makeHttpRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Error logging function
function logError($message, $context = []) {
    $logEntry = date('[Y-m-d H:i:s] ') . $message;
    if (!empty($context)) {
        $logEntry .= ' Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($logEntry . PHP_EOL, 3, 'payment_errors.log');
}
?>
