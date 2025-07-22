<?php
/**
 * NOTI 백엔드 알림 테스트
 * Fintree에서 payment_notification.php로 보내는 백엔드 알림을 시뮬레이션
 */

require_once 'config.php';

// 테스트 NOTI 데이터
$ordNo = $_GET['ordNo'] ?? '1234';
$amt = $_GET['amt'] ?? '132';

// TID 생성 (실제 형식)
$tid = FINTREE_MERCHANT_ID . '01012507' . date('Hi') . substr(md5(uniqid()), 0, 12);

// NOTI 데이터 구성
$notiData = [
    'resultCd' => '3001',
    'resultMsg' => '테스트 결제 승인 성공',
    'tid' => $tid,
    'ordNo' => $ordNo,
    'amt' => $amt,
    'appNo' => 'TEST' . date('His'),
    'appDt' => date('Ymd'),
    'appTm' => date('His'),
    'payMethod' => 'CARD'
];

// NOTI URL로 POST 전송
$notiUrl = 'https://w110.winnerit.co.kr/www/chpay/noti/r_pn_051.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $notiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($notiData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// 실행
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 결과 표시
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NOTI 백엔드 알림 테스트</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container py-5">
        <h1>NOTI 백엔드 알림 테스트</h1>
        
        <div class="card bg-dark border-primary mb-4">
            <div class="card-header">
                <h5>테스트 데이터</h5>
            </div>
            <div class="card-body">
                <p><strong>주문번호:</strong> <?php echo htmlspecialchars($ordNo); ?></p>
                <p><strong>금액:</strong> <?php echo number_format($amt); ?>원</p>
                <p><strong>생성된 TID:</strong> <?php echo htmlspecialchars($tid); ?></p>
                <p><strong>NOTI URL:</strong> <?php echo htmlspecialchars($notiUrl); ?></p>
            </div>
        </div>
        
        <div class="card bg-dark border-info mb-4">
            <div class="card-header">
                <h5>전송 데이터</h5>
            </div>
            <div class="card-body">
                <pre><?php echo json_encode($notiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        </div>
        
        <div class="card bg-dark border-<?php echo $httpCode == 200 ? 'success' : 'danger'; ?>">
            <div class="card-header">
                <h5>응답 결과</h5>
            </div>
            <div class="card-body">
                <p><strong>HTTP 상태 코드:</strong> <?php echo $httpCode; ?></p>
                <p><strong>응답:</strong> <?php echo htmlspecialchars($response); ?></p>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="/payment_errors.log" target="_blank" class="btn btn-warning">로그 확인</a>
            <a href="?ordNo=<?php echo time(); ?>&amt=<?php echo rand(1000, 100000); ?>" class="btn btn-primary">새로운 테스트</a>
        </div>
    </div>
</body>
</html>