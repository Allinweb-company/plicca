<?php
// 새로운 결제건 NOTI 수동 테스트
$newPayment = [
    'resultCd' => '3001',
    'tid' => 'mimich080m01012507162101460224',
    'ordNo' => '202507167957238',
    'amt' => '200',
    'appNo' => '30000751',
    'resultMsg' => '카드 결제 성공',
    'payMethod' => 'CARD',
    'appDtm' => '20250716210240',
    'ordNm' => '김성곤',
    'goodsName' => '결제샘플상품'
];

echo "=== 새로운 결제건 NOTI 테스트 ===\n";
echo "결제 시각: 21:02 KST\n";
echo "TID: {$newPayment['tid']}\n";
echo "주문번호: {$newPayment['ordNo']}\n";
echo "금액: {$newPayment['amt']}\n";
echo "승인번호: {$newPayment['appNo']}\n\n";

// payment_notification.php 직접 호출
$_POST = $newPayment;
ob_start();
include 'payment_notification.php';
$response = ob_get_clean();

echo "NOTI 응답: $response\n\n";

echo "=== payment_errors.log 최신 NOTI 기록 ===\n";
$errorLog = file_get_contents('payment_errors.log');
$lines = explode("\n", $errorLog);
$notiLines = array_filter($lines, function($line) {
    return strpos($line, '[NOTI]') !== false;
});
$latestNoti = array_slice($notiLines, -3);
foreach ($latestNoti as $line) {
    echo $line . "\n";
}
?>