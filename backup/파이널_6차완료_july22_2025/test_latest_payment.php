<?php
// 최신 결제건 NOTI 수동 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

$latestPayment = [
    'resultCd' => '3001',
    'tid' => 'mimich080m01012507162056060198',
    'ordNo' => '1111',
    'amt' => '108',
    'appNo' => '30000740',
    'resultMsg' => '카드 결제 성공',
    'payMethod' => 'CARD',
    'appDtm' => '20250716205700',
    'ordNm' => '김성곤',
    'goodsName' => '상품올인웹'
];

echo "=== 최신 결제건 NOTI 테스트 ===\n";
echo "결제 시각: 20:57 KST\n";
echo "TID: {$latestPayment['tid']}\n";
echo "주문번호: {$latestPayment['ordNo']}\n";
echo "금액: {$latestPayment['amt']}\n";
echo "승인번호: {$latestPayment['appNo']}\n\n";

// payment_notification.php 직접 호출
$_POST = $latestPayment;
ob_start();
include 'payment_notification.php';
$response = ob_get_clean();

echo "NOTI 응답: $response\n\n";

// 최신 로그 확인
echo "=== payment_errors.log 최신 NOTI 기록 ===\n";
$errorLog = file_get_contents('payment_errors.log');
$lines = explode("\n", $errorLog);
$notiLines = array_filter($lines, function($line) {
    return strpos($line, '[NOTI]') !== false;
});
$latestNoti = array_slice($notiLines, -6);
foreach ($latestNoti as $line) {
    echo $line . "\n";
}

echo "\n=== 현재 시각 및 결제 시각 비교 ===\n";
echo "현재 UTC: " . date('Y-m-d H:i:s') . "\n";
echo "현재 KST: " . date('Y-m-d H:i:s', time() + 9*3600) . "\n";
echo "결제 시각 KST: 2025-07-16 20:57:00\n";
echo "경과 시간: " . (time() - strtotime('2025-07-16 11:57:00')) . "초\n";

// 서버 활동 상태 확인
echo "\n=== 서버 활동 상태 ===\n";
echo "debug.log 마지막 수정: " . date('Y-m-d H:i:s', filemtime('debug.log')) . "\n";
echo "payment_errors.log 마지막 수정: " . date('Y-m-d H:i:s', filemtime('payment_errors.log')) . "\n";
?>