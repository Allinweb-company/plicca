<?php
header("Content-Type: text/html; charset=utf-8");

// FINTREE에서 결제 완료 후 백그라운드로 호출하는 NOTI URL
// 이 파일은 사용자가 보는 것이 아니라 서버 간 통신용

// 로그 함수
function writeNotificationLog($message) {
    $logFile = 'payment_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[NOTI] [{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

try {
    // POST 데이터 수신
    $postData = $_POST;
    
    // 로그에 기록
    writeNotificationLog("NOTI 수신 시작");
    writeNotificationLog("수신 데이터: " . json_encode($postData, JSON_UNESCAPED_UNICODE));
    
    // 필수 파라미터 확인
    $resultCd = isset($postData['resultCd']) ? $postData['resultCd'] : '';
    $tid = isset($postData['tid']) ? $postData['tid'] : '';
    $ordNo = isset($postData['ordNo']) ? $postData['ordNo'] : '';
    $amt = isset($postData['amt']) ? $postData['amt'] : '';
    
    if (empty($resultCd) || empty($tid)) {
        writeNotificationLog("ERROR: 필수 파라미터 누락 (resultCd: $resultCd, tid: $tid)");
        http_response_code(400);
        exit('FAIL - Missing required parameters');
    }
    
    // 결제 결과 처리
    if ($resultCd === '3001' || $resultCd === '0000') {
        // 결제 성공
        writeNotificationLog("SUCCESS: 결제 성공 알림 - TID: $tid, 주문번호: $ordNo, 금액: $amt");
        
        // 여기에 실제 비즈니스 로직 추가 가능
        // 예: 데이터베이스 업데이트, 재고 처리, 이메일 발송 등
        
        // FINTREE에 성공 응답
        echo 'OK';
        
    } else {
        // 결제 실패
        $resultMsg = isset($postData['resultMsg']) ? $postData['resultMsg'] : '';
        writeNotificationLog("FAIL: 결제 실패 알림 - TID: $tid, 코드: $resultCd, 메시지: $resultMsg");
        
        // FINTREE에 실패 응답
        echo 'FAIL';
    }
    
    writeNotificationLog("NOTI 처리 완료");
    
} catch (Exception $e) {
    writeNotificationLog("ERROR: 예외 발생 - " . $e->getMessage());
    http_response_code(500);
    echo 'FAIL - Internal error';
}
?>