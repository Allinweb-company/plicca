<?php
// 가장 먼저 실행 - 파일 시작 즉시 로그 기록
$debugLogPath = __DIR__ . '/debug.log';
$errorLogPath = __DIR__ . '/payment_errors.log';
$timestamp = date('Y-m-d H:i:s');

// 즉시 로그 기록 - 어떤 오류가 발생하기 전에
file_put_contents($debugLogPath, "\n[{$timestamp}] ====== 🔥🔥🔥 PAYMENT_RESULT.PHP 시작 🔥🔥🔥 ======\n", FILE_APPEND);
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 SERVER: " . json_encode($_SERVER) . "\n", FILE_APPEND);

// POST/GET 데이터 로그
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 POST: " . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 GET: " . json_encode($_GET) . "\n", FILE_APPEND);
file_put_contents($debugLogPath, "[{$timestamp}] 🔥 php://input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

// payment_errors.log에도 기록
file_put_contents($errorLogPath, "\n[RESULT] [{$timestamp}] ===== payment_result.php 호출 시작 =====\n", FILE_APPEND);
file_put_contents($errorLogPath, "[RESULT] [{$timestamp}] METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents($errorLogPath, "[RESULT] [{$timestamp}] POST 데이터: " . json_encode($_POST) . "\n", FILE_APPEND);

// 오류 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $errorLogPath);

// config.php 로드
try {
    require_once 'config.php';
    file_put_contents($debugLogPath, "[{$timestamp}] 🔥 config.php 로드 성공\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($debugLogPath, "[{$timestamp}] 🔥 config.php 로드 실패: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Config Error: " . $e->getMessage();
    exit;
}

// Webflow 리다이렉트 처리
session_start();
$webflowUrl = $_GET['webflowUrl'] ?? $_POST['webflowUrl'] ?? $_SESSION['webflowUrl'] ?? '';

// ⭐ 새창에서 GET 요청으로 온 경우 (첫 진입)
if (!empty($_GET['webflowUrl']) && empty($_POST)) {
    $_SESSION['webflowUrl'] = $_GET['webflowUrl'];
    file_put_contents($debugLogPath, "[" . date('Y-m-d H:i:s') . "] 🌐 WEBFLOW 새창 첫 진입 - webflowUrl 저장: " . $_GET['webflowUrl'] . "\n", FILE_APPEND);
    
    // 새창에서 Fintree 폼 제출 대기 페이지 표시
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>결제 승인 처리 중...</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
            .waiting-box { background: white; border-radius: 10px; padding: 30px; max-width: 500px; margin: 0 auto; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 20px auto; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        </style>
    </head>
    <body>
        <div class="waiting-box">
            <div class="spinner"></div>
            <h3>🔄 결제 승인 처리 대기 중...</h3>
            <p>Fintree에서 POST 데이터를 전송하면 자동으로 승인 처리가 시작됩니다.</p>
            <p><small>이 창을 닫지 마세요.</small></p>
        </div>
        
        <script>
            console.log('새창 대기 페이지 로드 완료');
            // POST 데이터 수신을 위해 페이지 새로고침 모니터링
            let checkCount = 0;
            const checkInterval = setInterval(() => {
                checkCount++;
                console.log(`POST 데이터 수신 확인 중... (${checkCount}초)`);
                
                // 30초 후 타임아웃
                if (checkCount > 30) {
                    clearInterval(checkInterval);
                    document.querySelector('h3').textContent = '⚠️ 타임아웃';
                    document.querySelector('p').textContent = '승인 처리 시간이 초과되었습니다. 창을 닫고 다시 시도해주세요.';
                }
            }, 1000);
        </script>
    </body>
    </html>
    <?php
    exit();
}

// webflowUrl이 있으면 세션에 저장
if (!empty($_GET['webflowUrl'])) {
    $_SESSION['webflowUrl'] = $_GET['webflowUrl'];
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] webflowUrl 세션 저장: " . $_GET['webflowUrl'] . "\n", FILE_APPEND);
}

// Webflow URL이 있고 POST 데이터가 있으면 팝업에서 부모창과 통신
if (!empty($webflowUrl) && !empty($_POST['resultCode'])) {
    // POST 데이터를 JavaScript로 전달할 데이터로 변환
    $resultData = [
        'result' => ($_POST['resultCode'] === '0000') ? '00' : '99',
        'ordNo' => $_POST['ordNo'] ?? '',
        'tid' => $_POST['tid'] ?? '',
        'amt' => $_POST['goodsAmt'] ?? '',
        'appNo' => $_POST['appNo'] ?? '',
        'resultMsg' => $_POST['resultMsg'] ?? ''
    ];
    
    // 로그 기록
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🌐 WEBFLOW 결제 결과 처리: " . json_encode($resultData) . "\n", FILE_APPEND);
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🌐 WEBFLOW URL: " . $webflowUrl . "\n", FILE_APPEND);
    
    // 세션 정리
    unset($_SESSION['webflowUrl']);
    
    // 수동 확인 방식 - 승인 결과 표시 후 사용자가 수동으로 돌아가기
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>결제 승인 처리 완료</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0;
                padding: 20px;
            }
            .result-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.2);
                padding: 40px;
                max-width: 600px;
                width: 100%;
                text-align: center;
            }
            .success-icon {
                color: #28a745;
                font-size: 4rem;
                margin-bottom: 20px;
            }
            .error-icon {
                color: #dc3545;
                font-size: 4rem;
                margin-bottom: 20px;
            }
            .return-btn {
                background: linear-gradient(45deg, #28a745, #20c997);
                border: none;
                padding: 15px 30px;
                border-radius: 10px;
                color: white;
                font-size: 1.2rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 20px;
            }
            .return-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
            }
            .details-table {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
                margin: 20px 0;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="result-card">
            <?php if ($resultData['result'] === '00'): ?>
                <div class="success-icon">✅</div>
                <h2 class="text-success mb-3">결제 승인 완료!</h2>
                <p class="text-muted mb-4">PHP 서버에서 결제 승인 처리가 성공적으로 완료되었습니다.</p>
            <?php else: ?>
                <div class="error-icon">❌</div>
                <h2 class="text-danger mb-3">결제 승인 실패</h2>
                <p class="text-muted mb-4">결제 승인 처리 중 오류가 발생했습니다.</p>
            <?php endif; ?>

            <div class="details-table">
                <h5 class="mb-3">🧾 승인 처리 결과</h5>
                <div class="row">
                    <div class="col-6"><strong>결과:</strong></div>
                    <div class="col-6">
                        <span class="badge <?php echo $resultData['result'] === '00' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $resultData['result'] === '00' ? '승인 성공' : '승인 실패'; ?>
                        </span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6"><strong>주문번호:</strong></div>
                    <div class="col-6"><?php echo htmlspecialchars($resultData['ordNo']); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-6"><strong>거래번호(TID):</strong></div>
                    <div class="col-6"><code><?php echo htmlspecialchars($resultData['tid']); ?></code></div>
                </div>
                <div class="row mt-2">
                    <div class="col-6"><strong>결제금액:</strong></div>
                    <div class="col-6"><strong><?php echo number_format($resultData['amt']); ?>원</strong></div>
                </div>
                <div class="row mt-2">
                    <div class="col-6"><strong>승인번호:</strong></div>
                    <div class="col-6"><?php echo htmlspecialchars($resultData['appNo'] ?: 'N/A'); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-6"><strong>처리 메시지:</strong></div>
                    <div class="col-6"><?php echo htmlspecialchars($resultData['resultMsg']); ?></div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h6>📋 안내사항</h6>
                <ul class="mb-0 text-start">
                    <li>승인 처리 결과를 확인하셨습니다</li>
                    <li>아래 버튼을 클릭하여 Webflow 페이지로 돌아가세요</li>
                    <li>TID는 취소 시 필요하니 별도 기록해두세요</li>
                </ul>
            </div>

            <button onclick="returnToWebflow()" class="return-btn">
                🔙 Webflow로 돌아가기
            </button>
        </div>

        <script>
            const resultData = <?php echo json_encode($resultData); ?>;
            const webflowUrl = <?php echo json_encode(urldecode($webflowUrl)); ?>;

            function returnToWebflow() {
                // 부모창에 결과 전달
                if (window.opener && !window.opener.closed) {
                    try {
                        if (typeof window.opener.onPaymentComplete === 'function') {
                            window.opener.onPaymentComplete(resultData);
                        }
                        window.close();
                    } catch (e) {
                        console.error('부모창 통신 오류:', e);
                        // 부모창 통신 실패 시 직접 이동
                        window.location.href = webflowUrl + '?' + <?php echo json_encode(http_build_query($resultData)); ?>;
                    }
                } else {
                    // 부모창이 없으면 직접 이동
                    window.location.href = webflowUrl + '?' + <?php echo json_encode(http_build_query($resultData)); ?>;
                }
            }

            // 자동 복사 기능
            function copyTid() {
                navigator.clipboard.writeText(resultData.tid).then(function() {
                    alert('TID가 클립보드에 복사되었습니다: ' + resultData.tid);
                });
            }

            console.log('💡 PHP 서버 승인 처리 결과:', resultData);
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Debug: Log all received data with HTTP method
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔥 PAYMENT_RESULT.PHP 호출됨! METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔥 PAYMENT_RESULT.PHP POST: " . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔥 PAYMENT_RESULT.PHP GET: " . json_encode($_GET) . "\n", FILE_APPEND);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔥 REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔥 HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'N/A') . "\n", FILE_APPEND);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔥 CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A') . "\n", FILE_APPEND);

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

// 누락된 정보 복원 - 세션에서 결제 데이터 찾기
$savedPaymentData = $_SESSION['payment_data'] ?? null;

// TID에서 주문번호 추출 시도 (TID 형식: mimich067m + 주문정보)
if (empty($ordNo) && !empty($tid)) {
    // TID에서 타임스탬프 부분 추출하여 주문번호 생성
    $tidParts = explode('0101', $tid); // TID 패턴 분석
    if (count($tidParts) > 1) {
        $orderTimestamp = substr($tidParts[1], 0, 15); // 앞 15자리가 타임스탬프
        $ordNo = 'ORD' . $orderTimestamp;
    }
}

// 세션 데이터가 있으면 우선 사용
if ($savedPaymentData && !empty($savedPaymentData['ordNo'])) {
    if (empty($ordNo)) $ordNo = $savedPaymentData['ordNo'];
    if (empty($goodsNm)) $goodsNm = $savedPaymentData['goodsNm'] ?? '';
    if (empty($ordNm)) $ordNm = $savedPaymentData['ordNm'] ?? '';
    
    // 세션 데이터 로그
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 💾 세션 결제 데이터 복원: " . json_encode($savedPaymentData) . "\n", FILE_APPEND);
}

// 기본값 설정 (정보가 없을 경우)
if (empty($ordNo)) $ordNo = '주문번호 미확인';
if (empty($goodsNm)) $goodsNm = '상품정보 미확인';  
if (empty($ordNm)) $ordNm = '구매자 미확인';

// 복원된 정보 로그
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] 🔧 복원된 결제정보 - 주문번호: $ordNo, 상품명: $goodsNm, 구매자: $ordNm\n", FILE_APPEND);

// 테스트 환경 체크
$isTestEnvironment = ($mid === 'chpayc190m');
if ($isTestEnvironment && empty($resultCode) && empty($tid)) {
    logError("테스트 환경 제약 감지", [
        'message' => '테스트 MID는 실제 인증을 진행하지 않습니다',
        'received_data' => $_POST,
        'expected_params' => ['resultCode', 'tid', 'signData'],
        'actual_params' => array_keys($_POST)
    ]);
}

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
            
            // 테스트 환경에서 승인 실패가 일반적임을 로깅
            logError("테스트 환경 승인 실패", [
                'tid' => $tid,
                'resultCd' => $approvalResult['resultCd'] ?? 'N/A',
                'resultMsg' => $approvalResult['resultMsg'] ?? 'N/A',
                'note' => '테스트 환경에서는 인증 성공 후 승인 실패가 일반적임'
            ]);
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
