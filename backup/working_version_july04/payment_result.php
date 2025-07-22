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
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] POST: " . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] GET: " . json_encode($_GET) . "\n", FILE_APPEND);

try {
    logError("Payment Result - All POST data", $_POST);
    logError("Payment Result - All GET data", $_GET);
} catch (Exception $e) {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] LogError failed: " . $e->getMessage() . "\n", FILE_APPEND);
}

// 인증 결과 파라미터
$resultCode = $_POST['resultCode'] ?? '';
$resultMsg = $_POST['resultMsg'] ?? '';
$tid = $_POST['tid'] ?? '';
$payMethod = $_POST['payMethod'] ?? '';
$ediDate = $_POST['ediDate'] ?? '';
$mid = $_POST['mid'] ?? '';
$ordNo = $_POST['ordNo'] ?? '';
$goodsAmt = $_POST['goodsAmt'] ?? '';
$goodsNm = $_POST['goodsNm'] ?? '';
$ordNm = $_POST['ordNm'] ?? '';
$mbsReserved = $_POST['mbsReserved'] ?? '';
$signData = $_POST['signData'] ?? '';

try {
    $merchantKey = FINTREE_MERCHANT_KEY;
    $encData = generateHash($mid, $ediDate, $goodsAmt, $merchantKey);
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Hash generated successfully\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Hash generation failed: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Hash Generation Error: " . $e->getMessage();
    exit;
}

$approvalResult = null;
$approvalSuccess = false;
$errorMessage = '';

// Log the authentication result
logError("Authentication Result", [
    'resultCode' => $resultCode,
    'resultMsg' => $resultMsg,
    'tid' => $tid,
    'ordNo' => $ordNo,
    'goodsAmt' => $goodsAmt
]);

if ($resultCode === "0000") {
    // 인증 성공 - 승인 진행
    try {
        $data = [
            'tid' => $tid,
            'mid' => $mid,
            'goodsAmt' => $goodsAmt,
            'ediDate' => $ediDate,
            'charSet' => 'utf-8',
            'encData' => $encData,
            'signData' => $signData
        ];
        
        $response = makeHttpRequest(FINTREE_API_URL . '/pay.do', $data);
        
        if ($response === false) {
            throw new Exception('API 통신 실패');
        }
        
        $approvalResult = json_decode($response, true);
        
        // Log the approval request and response
        logError("Approval Request/Response", [
            'request' => $data,
            'response' => $approvalResult
        ]);
        
        if ($approvalResult && isset($approvalResult['resultCd']) && $approvalResult['resultCd'] === '3001') {
            $approvalSuccess = true;
        } else {
            $errorMessage = $approvalResult['resultMsg'] ?? 'Unknown approval error';
        }
        
    } catch (Exception $e) {
        $errorMessage = '통신실패: ' . $e->getMessage();
        $approvalResult = ['resultCode' => '9999', 'resultMsg' => $errorMessage];
        logError("Approval Exception", ['error' => $e->getMessage()]);
    }
} else {
    $errorMessage = $resultMsg;
}
?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 결과</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow">
                    <div class="card-header <?php echo $approvalSuccess ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <h4 class="mb-0">
                            <?php if ($approvalSuccess): ?>
                                <i class="fas fa-check-circle me-2"></i>
                                결제 완료
                            <?php else: ?>
                                <i class="fas fa-times-circle me-2"></i>
                                결제 실패
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($approvalSuccess): ?>
                            <!-- 성공 메시지 -->
                            <div class="alert alert-success border-0" role="alert">
                                <h5 class="alert-heading">
                                    <i class="fas fa-check-circle me-2"></i>결제가 성공적으로 완료되었습니다!
                                </h5>
                                <p class="mb-0">결제 내역은 이메일로 전송되며, 고객센터에서도 확인하실 수 있습니다.</p>
                            </div>

                            <!-- 결제 상세 정보 -->
                            <div class="card bg-dark border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-3">
                                        <i class="fas fa-receipt me-2"></i>결제 상세 내역
                                    </h5>
                                    <table class="table table-dark table-borderless mb-0">
                                        <tr>
                                            <td width="30%"><i class="fas fa-receipt me-2 text-secondary"></i>거래번호(TID):</td>
                                            <td><strong class="text-info"><?php echo htmlspecialchars($tid); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-list-ol me-2 text-secondary"></i>주문번호:</td>
                                            <td><?php echo htmlspecialchars($ordNo); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-box me-2 text-secondary"></i>상품명:</td>
                                            <td><?php echo htmlspecialchars($goodsNm); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-user me-2 text-secondary"></i>구매자:</td>
                                            <td><?php echo htmlspecialchars($ordNm); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-credit-card me-2 text-secondary"></i>결제수단:</td>
                                            <td><?php echo strtoupper(htmlspecialchars($payMethod)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-won-sign me-2 text-secondary"></i>결제금액:</td>
                                            <td><strong class="text-success h5"><?php echo formatPrice($goodsAmt); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-clock me-2 text-secondary"></i>결제시간:</td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($ediDate)); ?></td>
                                        </tr>
                                        <?php if (isset($approvalResult['authCd']) && !empty($approvalResult['authCd'])): ?>
                                        <tr>
                                            <td><i class="fas fa-key me-2 text-secondary"></i>승인번호:</td>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($approvalResult['authCd']); ?></span></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>

                            <!-- 취소 안내 -->
                            <div class="alert alert-warning border-0" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>테스트 결제 안내
                                </h6>
                                <p class="mb-2">이것은 테스트 결제입니다. 실제 결제가 진행되었으므로:</p>
                                <ul class="mb-0">
                                    <li><strong class="text-warning">당일 취소가 필요합니다</strong></li>
                                    <li>취소는 아래 "결제 취소" 버튼을 이용해주세요</li>
                                    <li>취소하지 않을 경우 실제 결제가 유지됩니다</li>
                                </ul>
                            </div>

                            <!-- 액션 버튼 -->
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <a href="index.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-home me-2"></i>홈으로
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="cancel.php?tid=<?php echo urlencode($tid); ?>&amt=<?php echo urlencode($goodsAmt); ?>" 
                                       class="btn btn-warning w-100">
                                        <i class="fas fa-undo me-2"></i>결제 취소
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <button onclick="window.print()" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-print me-2"></i>영수증 출력
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button onclick="copyTid()" class="btn btn-outline-info w-100">
                                        <i class="fas fa-copy me-2"></i>TID 복사
                                    </button>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- 실패 메시지 -->
                            <div class="alert alert-danger border-0" role="alert">
                                <h5 class="alert-heading">
                                    <i class="fas fa-times-circle me-2"></i>결제에 실패했습니다
                                </h5>
                                <p class="mb-0"><?php echo htmlspecialchars($errorMessage); ?></p>
                            </div>

                            <!-- 오류 상세 정보 -->
                            <div class="card bg-dark border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-danger mb-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>오류 상세 정보
                                    </h5>
                                    <table class="table table-dark table-borderless mb-0">
                                        <tr>
                                            <td width="30%"><i class="fas fa-exclamation-triangle me-2 text-secondary"></i>오류 코드:</td>
                                            <td><span class="badge bg-danger"><?php echo htmlspecialchars($resultCode !== "0000" ? $resultCode : ($approvalResult['resultCode'] ?? 'N/A')); ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-comment me-2 text-secondary"></i>오류 메시지:</td>
                                            <td><?php echo htmlspecialchars($errorMessage); ?></td>
                                        </tr>
                                        <?php if ($tid): ?>
                                        <tr>
                                            <td><i class="fas fa-receipt me-2 text-secondary"></i>거래번호:</td>
                                            <td><?php echo htmlspecialchars($tid); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($ordNo): ?>
                                        <tr>
                                            <td><i class="fas fa-list-ol me-2 text-secondary"></i>주문번호:</td>
                                            <td><?php echo htmlspecialchars($ordNo); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>

                            <!-- 액션 버튼 -->
                            <div class="row">
                                <div class="col-6">
                                    <a href="index.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-home me-2"></i>홈으로
                                    </a>
                                </div>
                                <div class="col-6">
                                    <button onclick="history.back()" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-arrow-left me-2"></i>다시 시도
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Debug 정보 (개발용) -->
                        <?php if ($approvalResult || $resultCode): ?>
                        <div class="mt-4">
                            <button class="btn btn-outline-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                                <i class="fas fa-code me-2"></i>API 응답 정보 (개발용)
                            </button>
                            <div class="collapse mt-2" id="debugInfo">
                                <div class="card bg-dark border-0">
                                    <div class="card-body">
                                        <h6 class="text-info">인증 결과:</h6>
                                        <pre class="text-light small mb-3"><?php 
                                            echo htmlspecialchars(json_encode([
                                                'resultCode' => $resultCode,
                                                'resultMsg' => $resultMsg,
                                                'tid' => $tid,
                                                'payMethod' => $payMethod,
                                                'ediDate' => $ediDate
                                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                                        ?></pre>
                                        <?php if ($approvalResult): ?>
                                        <h6 class="text-info">승인 결과:</h6>
                                        <pre class="text-light small mb-0"><?php echo htmlspecialchars(json_encode($approvalResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyTid() {
            const tid = '<?php echo htmlspecialchars($tid); ?>';
            navigator.clipboard.writeText(tid).then(function() {
                alert('TID가 클립보드에 복사되었습니다: ' + tid);
            }).catch(function() {
                prompt('TID를 복사하세요:', tid);
            });
        }
    </script>
</body>
</html>
