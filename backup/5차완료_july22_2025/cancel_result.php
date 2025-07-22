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
        
        // notiUrl 비활성화 (NOTI 사용 안함)
        $notiUrl = "";
        
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
                            </div>

                            <!-- 취소 상세 정보 -->
                            <div class="card bg-dark border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-3">
                                        <i class="fas fa-receipt me-2"></i>취소 상세 내역
                                    </h5>
                                    <table class="table table-dark table-borderless mb-0">
                                        <tr>
                                            <td width="30%"><i class="fas fa-receipt me-2 text-secondary"></i>거래번호(TID): <strong class="text-info"><?php echo htmlspecialchars($cancelResult['oTid'] ?? $tid); ?></strong></td>
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
                                            <td width="30%"><i class="fas fa-receipt me-2 text-secondary"></i>거래번호(TID): <strong class="text-info"><?php echo htmlspecialchars($tid); ?></strong></td>
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
    
    <script>
        // 취소 요청/응답 데이터를 콘솔에 출력
        <?php if (!empty($data)): ?>
        console.log('🔄 취소 요청 데이터:', <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?>);
        <?php endif; ?>
        
        <?php if (!empty($cancelResult)): ?>
        console.log('📨 취소 응답 데이터:', <?php echo json_encode($cancelResult, JSON_UNESCAPED_UNICODE); ?>);
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
        console.log('❌ 취소 오류:', '<?php echo addslashes($errorMessage); ?>');
        <?php endif; ?>
        
        console.log('📊 취소 처리 상태:', {
            success: <?php echo $cancelSuccess ? 'true' : 'false'; ?>,
            tid: '<?php echo addslashes($tid); ?>',
            ordNo: '<?php echo addslashes($ordNo); ?>',
            canAmt: '<?php echo addslashes($canAmt); ?>'
        });
    </script>
    
    <script>
        // URL 파라미터 텍스트 완전 제거
        document.addEventListener('DOMContentLoaded', function() {
            function removeUrlParameters() {
                // 페이지의 모든 텍스트 내용 검사
                const walker = document.createTreeWalker(
                    document.body,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );

                let node;
                const patterns = [
                    /resultCode=[^&\s]*(&[^&\s]*=[^&\s]*)*\s*/g,
                    /[a-zA-Z0-9_]+=.*&.*=/g,
                    /Method=CARD[^<\s]*/g,
                    /encData=[a-zA-Z0-9+/=]{10,}/g,
                    /signData=[a-zA-Z0-9+/=]{10,}/g,
                    /mid=mimich067m[^<\s]*/g,
                    /tid=[a-zA-Z0-9]{10,}/g,
                    /payMethod=[^&\s]*/g,
                    /ediDate=[^&\s]*/g,
                    /goodsAmt=[^&\s]*/g,
                    /canAmt=[^&\s]*/g,
                    /ordNo=[^&\s]*/g
                ];
                
                while (node = walker.nextNode()) {
                    let originalText = node.textContent;
                    let cleanedText = originalText;
                    
                    // 모든 패턴에 대해 제거 작업 수행
                    patterns.forEach(pattern => {
                        cleanedText = cleanedText.replace(pattern, '');
                    });
                    
                    // 텍스트가 변경되었다면 업데이트
                    if (cleanedText !== originalText) {
                        node.textContent = cleanedText.trim();
                        console.log('URL 파라미터 제거 완료');
                    }
                }
                
                // body 끝에 있는 잔여 텍스트도 제거
                const bodyText = document.body.innerHTML;
                let cleanBodyText = bodyText;
                patterns.forEach(pattern => {
                    cleanBodyText = cleanBodyText.replace(pattern, '');
                });
                
                if (cleanBodyText !== bodyText) {
                    document.body.innerHTML = cleanBodyText;
                }
            }

            // 페이지 로드 후 실행
            removeUrlParameters();
            
            // 추가 실행으로 확실하게 제거
            setTimeout(removeUrlParameters, 100);
            setTimeout(removeUrlParameters, 500);
        });
    </script>

    <style>
        /* 추가 보안: 특정 클래스로 숨김 */
        .url-param-text {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
        }
        
        /* 페이지 하단 URL 파라미터 숨김 */
        body::after {
            content: "";
            display: block;
            height: 50px;
            background: #1a1a1a;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        /* 페이지 하단 여백 추가 */
        body {
            padding-bottom: 70px !important;
        }
    </style>
</body>
</html>