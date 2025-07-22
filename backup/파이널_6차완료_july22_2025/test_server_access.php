<?php
// 서버 외부 접근 테스트
echo "=== 서버 접근 테스트 ===\n";
echo "현재 시간: " . date('Y-m-d H:i:s') . "\n";
echo "서버 IP: " . $_SERVER['SERVER_ADDR'] ?? 'N/A' . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] ?? 'N/A' . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] ?? 'N/A' . "\n";
echo "\n";

// 로그 테스트
$logMessage = "[" . date('Y-m-d H:i:s') . "] 서버 접근 테스트 - 외부에서 접근 가능";
file_put_contents(__DIR__ . '/debug.log', $logMessage . "\n", FILE_APPEND);

echo "접근 테스트 완료 - 로그 기록됨\n";
?>