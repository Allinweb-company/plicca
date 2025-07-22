<?php
// NOTI 시스템 수동 테스트
echo "=== NOTI 시스템 수동 테스트 시작 ===\n";

// payment_notification.php 직접 호출
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'resultCd' => '3001',
    'tid' => 'mimich080m01012507171144510987',
    'ordNo' => '1234',
    'amt' => '108',
    'appNo' => '30000762',
    'resultMsg' => '카드 결제 성공',
    'payMethod' => 'CARD',
    'appDtm' => '20250717114542',
    'ordNm' => '김성곤',
    'goodsName' => '올인웹'
];

echo "POST 데이터 설정 완료\n";
echo "TID: " . $_POST['tid'] . "\n";
echo "payment_notification.php 호출 중...\n\n";

// payment_notification.php 실행
ob_start();
include 'payment_notification.php';
$response = ob_get_clean();

echo "응답: " . $response . "\n";
echo "=== 테스트 완료 ===\n";
?>