<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

try {
    require_once 'config.php';
} catch (Exception $e) {
    echo "Config Error: " . $e->getMessage();
    exit;
}

// Debug: Log all received data
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Cancel POST: " . json_encode($_POST) . "\n", FILE_APPEND);

try {
    logError("Cancel Request - All POST data", $_POST);
} catch (Exception $e) {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] LogError failed: " . $e->getMessage() . "\n", FILE_APPEND);
}

// 취소 요청 파라미터
$tid = $_POST['tid'] ?? '';
$ordNo = $_POST['ordNo'] ?? '';
$canAmt = $_POST['canAmt'] ?? '';

$cancelSuccess = false;
$cancelResult = null;
$errorMessage = '';

if (empty($tid) || empty($canAmt)) {
    $errorMessage = '필수 파라미터가 누락되었습니다.';
} else {
    try {
        $mid = FINTREE_MERCHANT_ID;
        $merchantKey = FINTREE_MERCHANT_KEY;
        $ediDate = date("YmdHis");
        $encData = generateHash($mid, $ediDate, $canAmt, $merchantKey);
        
        // notiUrl 설정
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $notiUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/cancel_notification.php';
        
        // 취소 요청 데이터
        $data = [
            'tid' => $tid,
            'ordNo' => $ordNo,
            'canAmt' => $canAmt,
            'canId' => 'testAdmin',
            'canNm' => '관리자',
            'canMsg' => '테스트 취소',
            'canIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'partCanFlg' => '0', // 전체취소
            'notiUrl' => $notiUrl,
            'ediDate' => $ediDate,
            'encData' => $encData,
            'charset' => 'UTF-8'
        ];
        
        // Log the cancel request
        logError("Cancel Request Data", $data);
        
        $response = makeHttpRequest(FINTREE_API_URL . '/payment.cancel', $data);
        
        if ($response === false) {
            throw new Exception('API 통신 실패');
        }
        
        $cancelResult = json_decode($response, true);
        
        // Log the cancel response
        logError("Cancel Response", $cancelResult);
        
        if ($cancelResult && isset($cancelResult['resultCd']) && $cancelResult['resultCd'] === '0000') {
            $cancelSuccess = true;
        } else {
            $errorMessage = $cancelResult['resultMsg'] ?? 'Unknown cancel error';
        }
        
    } catch (Exception $e) {
        $errorMessage = '통신실패: ' . $e->getMessage();
        $cancelResult = ['resultCd' => '9999', 'resultMsg' => $errorMessage];
        logError("Cancel Exception", ['error' => $e->getMessage()]);
    }
}
?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>취소 결과</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow">
                    <div class="card-header <?php echo $cancelSuccess ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <h4 class="mb-0">
                            <?php if ($cancelSuccess): ?>
                                <i class="fas fa-check-circle me-2"></i>
                                취소 완료
                            <?php else: ?>
                                <i class="fas fa-times-circle me-2"></i>
                                취소 실패
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($cancelSuccess): ?>
                            <!-- 성공 메시지 -->
                            <div class="alert alert-success border-0" role="alert">
                                <h5 class="alert-heading">
                                    <i class="fas fa-check-circle me-2"></i>결제 취소가 성공적으로 완료되었습니다!
                                </h5>
                                <p class="mb-0">취소 내역은 이메일로 전송되며, 고객센터에서도 확인하실 수 있습니다.</p>
                            </div>

                            <!-- 취소 상세 정보 -->
                            <div class="card bg-dark border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-3">
                                        <i class="fas fa-receipt me-2"></i>취소 상세 내역
                                    </h5>
                                    <table class="table table-dark table-borderless mb-0">
                                        <tr>
                                            <td width="30%"><i class="fas fa-receipt me-2 text-secondary"></i>원거래번호(TID):</td>
                                            <td><strong class="text-info"><?php echo htmlspecialchars($cancelResult['oTid'] ?? $tid); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-receipt me-2 text-secondary"></i>취소거래번호:</td>
                                            <td><strong class="text-info"><?php echo htmlspecialchars($cancelResult['tid'] ?? ''); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-shopping-bag me-2 text-secondary"></i>주문번호:</td>
                                            <td><?php echo htmlspecialchars($cancelResult['ordNo'] ?? $ordNo); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-won-sign me-2 text-secondary"></i>취소금액:</td>
                                            <td><strong class="text-warning"><?php echo number_format($cancelResult['amt'] ?? $canAmt); ?>원</strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-clock me-2 text-secondary"></i>취소일시:</td>
                                            <td><?php echo htmlspecialchars($cancelResult['appDtm'] ?? date('YmdHis')); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-check me-2 text-secondary"></i>취소상태:</td>
                                            <td><span class="badge bg-success">취소완료</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- 실패 메시지 -->
                            <div class="alert alert-danger border-0" role="alert">
                                <h5 class="alert-heading">
                                    <i class="fas fa-times-circle me-2"></i>결제 취소에 실패했습니다
                                </h5>
                                <p class="mb-0">
                                    <?php echo htmlspecialchars($errorMessage); ?>
                                </p>
                            </div>

                            <!-- 실패 상세 정보 -->
                            <div class="card bg-dark border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-danger mb-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>오류 상세 내역
                                    </h5>
                                    <table class="table table-dark table-borderless mb-0">
                                        <tr>
                                            <td width="30%"><i class="fas fa-receipt me-2 text-secondary"></i>거래번호(TID):</td>
                                            <td><strong class="text-info"><?php echo htmlspecialchars($tid); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-shopping-bag me-2 text-secondary"></i>주문번호:</td>
                                            <td><?php echo htmlspecialchars($ordNo); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-won-sign me-2 text-secondary"></i>요청금액:</td>
                                            <td><strong class="text-warning"><?php echo number_format($canAmt); ?>원</strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-exclamation-circle me-2 text-secondary"></i>오류코드:</td>
                                            <td><span class="text-danger"><?php echo htmlspecialchars($cancelResult['resultCd'] ?? '9999'); ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-info-circle me-2 text-secondary"></i>오류메시지:</td>
                                            <td><span class="text-danger"><?php echo htmlspecialchars($errorMessage); ?></span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 액션 버튼 -->
                        <div class="text-center">
                            <a href="cancel.php" class="btn btn-warning me-2">
                                <i class="fas fa-list me-2"></i>취소 목록
                            </a>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>홈으로
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>