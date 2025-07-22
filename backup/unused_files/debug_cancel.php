<?php
// cancel.php 디버깅을 위한 파일
require_once 'config.php';

echo "<h3>Cancel.php 디버깅 정보</h3>";

// 로그 파일 존재 여부 확인
echo "<p><strong>로그 파일 존재:</strong> " . (file_exists('payment_errors.log') ? 'YES' : 'NO') . "</p>";

if (file_exists('payment_errors.log')) {
    echo "<p><strong>로그 파일 크기:</strong> " . filesize('payment_errors.log') . " bytes</p>";
    
    // 로그 내용 샘플 확인
    $logContent = file_get_contents('payment_errors.log');
    $lines = explode("\n", $logContent);
    
    echo "<p><strong>총 로그 라인:</strong> " . count($lines) . "</p>";
    
    // 성공한 결제 패턴 검색
    $successCount = 0;
    $notiSuccessCount = 0;
    
    foreach ($lines as $line) {
        if (strpos($line, '[NOTI]') !== false && strpos($line, 'SUCCESS') !== false) {
            $notiSuccessCount++;
        }
        if (strpos($line, '"resultCd":"3001"') !== false) {
            $successCount++;
        }
    }
    
    echo "<p><strong>NOTI SUCCESS 건수:</strong> " . $notiSuccessCount . "</p>";
    echo "<p><strong>resultCd 3001 건수:</strong> " . $successCount . "</p>";
    
    // 최근 5개 NOTI SUCCESS 라인 표시
    echo "<h4>최근 NOTI SUCCESS 로그:</h4>";
    $count = 0;
    for ($i = count($lines) - 1; $i >= 0 && $count < 5; $i--) {
        if (strpos($lines[$i], '[NOTI]') !== false && strpos($lines[$i], 'SUCCESS') !== false) {
            echo "<pre>" . htmlspecialchars($lines[$i]) . "</pre>";
            $count++;
        }
    }
    
    // 결제 목록 추출 로직 테스트
    $recentPayments = [];
    foreach ($lines as $line) {
        if (strpos($line, '[NOTI]') !== false && strpos($line, 'SUCCESS') !== false) {
            if (preg_match('/TID: ([^,]+).*주문번호: ([^,]+).*금액: ([^,]+)/', $line, $matches)) {
                $recentPayments[] = [
                    'tid' => trim($matches[1]),
                    'ordNo' => trim($matches[2]),
                    'amt' => trim($matches[3])
                ];
            }
        }
    }
    
    echo "<h4>추출된 결제 목록:</h4>";
    echo "<pre>" . print_r($recentPayments, true) . "</pre>";
    
} else {
    echo "<p><strong style='color: red;'>payment_errors.log 파일이 존재하지 않습니다!</strong></p>";
}
?>