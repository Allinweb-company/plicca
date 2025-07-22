<?php
require_once 'config.php';

/**
 * Fintree Payment Approval Utility
 * 
 * This file provides utility functions for handling payment approval
 * that can be used standalone or included in other payment processing files.
 */

/**
 * Process payment approval request
 * 
 * @param string $tid Transaction ID
 * @param string $mid Merchant ID
 * @param string $goodsAmt Payment amount
 * @param string $ediDate Transaction date
 * @param string $signData Sign data from authentication
 * @return array Approval result
 */
function processPaymentApproval($tid, $mid, $goodsAmt, $ediDate, $signData) {
    $merchantKey = FINTREE_MERCHANT_KEY;
    $encData = generateHash($mid, $ediDate, $goodsAmt, $merchantKey);
    
    $data = [
        'tid' => $tid,
        'mid' => $mid,
        'goodsAmt' => $goodsAmt,
        'ediDate' => $ediDate,
        'charSet' => 'utf-8',
        'encData' => $encData,
        'signData' => $signData
    ];
    
    // Log approval request
    logError("Approval Request", ['data' => $data]);
    
    try {
        $response = makeHttpRequest(FINTREE_API_URL . '/pay.do', $data);
        
        if ($response === false) {
            throw new Exception('API communication failed');
        }
        
        $result = json_decode($response, true);
        
        // Log approval response
        logError("Approval Response", ['response' => $result]);
        
        return $result;
        
    } catch (Exception $e) {
        $errorResult = [
            'resultCode' => '9999',
            'resultMsg' => 'Communication error: ' . $e->getMessage()
        ];
        
        logError("Approval Exception", ['error' => $e->getMessage()]);
        return $errorResult;
    }
}

/**
 * Validate approval request parameters
 * 
 * @param array $params Request parameters
 * @return array Validation result [valid => bool, errors => array]
 */
function validateApprovalParams($params) {
    $errors = [];
    
    $requiredFields = ['tid', 'mid', 'goodsAmt', 'ediDate', 'signData'];
    
    foreach ($requiredFields as $field) {
        if (empty($params[$field])) {
            $errors[] = "Missing required field: {$field}";
        }
    }
    
    // Validate amount format
    if (!empty($params['goodsAmt']) && !is_numeric($params['goodsAmt'])) {
        $errors[] = "Invalid amount format";
    }
    
    // Validate date format
    if (!empty($params['ediDate']) && !preg_match('/^\d{14}$/', $params['ediDate'])) {
        $errors[] = "Invalid date format (should be YmdHis)";
    }
    
    // Validate TID format
    if (!empty($params['tid']) && strlen($params['tid']) > 40) {
        $errors[] = "TID too long (max 40 characters)";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Format approval result for display
 * 
 * @param array $result Approval result
 * @return array Formatted result
 */
function formatApprovalResult($result) {
    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid result format',
            'data' => []
        ];
    }
    
    $success = isset($result['resultCode']) && $result['resultCode'] === '0000';
    
    $formatted = [
        'success' => $success,
        'message' => $result['resultMsg'] ?? 'Unknown result',
        'data' => [
            'resultCode' => $result['resultCode'] ?? '',
            'resultMsg' => $result['resultMsg'] ?? '',
            'tid' => $result['tid'] ?? '',
            'authCd' => $result['authCd'] ?? '',
            'authDate' => $result['authDate'] ?? '',
            'cardCd' => $result['cardCd'] ?? '',
            'cardNm' => $result['cardNm'] ?? '',
            'acquCardCd' => $result['acquCardCd'] ?? '',
            'acquCardNm' => $result['acquCardNm'] ?? '',
            'goodsAmt' => $result['goodsAmt'] ?? '',
            'cardAmt' => $result['cardAmt'] ?? '',
            'instmtMon' => $result['instmtMon'] ?? '',
            'instmtType' => $result['instmtType'] ?? '',
            'pntAmt' => $result['pntAmt'] ?? '',
            'couponAmt' => $result['couponAmt'] ?? '',
            'cardRealAmt' => $result['cardRealAmt'] ?? '',
            'cardInterest' => $result['cardInterest'] ?? ''
        ]
    ];
    
    return $formatted;
}

// If this file is accessed directly via HTTP, show approval form
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'payment_approval.php') {
    ?>
    <!DOCTYPE html>
    <html lang="ko" data-bs-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>결제 승인 도구</title>
        <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-tools me-2"></i>
                                결제 승인 도구 (개발용)
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning border-0" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>개발 도구 안내
                                </h6>
                                <p class="mb-0">이 도구는 개발 및 디버깅 목적으로 사용됩니다. 일반적으로는 정상적인 결제 흐름을 이용해주세요.</p>
                            </div>

                            <form action="payment_approval.php" method="POST" id="approvalForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tid" class="form-label">
                                            <i class="fas fa-receipt me-2"></i>거래번호(TID) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="tid" name="tid" required 
                                               placeholder="예: FINTREE202501010001">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mid" class="form-label">
                                            <i class="fas fa-store me-2"></i>상점ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="mid" name="mid" 
                                               value="<?php echo FINTREE_MERCHANT_ID; ?>" required readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="goodsAmt" class="form-label">
                                            <i class="fas fa-won-sign me-2"></i>결제금액 <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="goodsAmt" name="goodsAmt" required 
                                               placeholder="1004" min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ediDate" class="form-label">
                                            <i class="fas fa-calendar me-2"></i>거래일시 <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="ediDate" name="ediDate" required 
                                               placeholder="<?php echo date('YmdHis'); ?>" maxlength="14">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="signData" class="form-label">
                                        <i class="fas fa-key me-2"></i>서명데이터 <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="signData" name="signData" required rows="3"
                                              placeholder="인증 완료 후 받은 signData 값을 입력하세요"></textarea>
                                </div>

                                <div class="alert alert-info border-0" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>사용 방법
                                    </h6>
                                    <ol class="mb-0">
                                        <li>먼저 정상적인 결제 과정을 통해 인증을 완료하세요</li>
                                        <li>인증 완료 후 받은 TID, 금액, 거래일시, signData를 입력하세요</li>
                                        <li>승인 요청을 클릭하여 수동으로 승인을 진행하세요</li>
                                    </ol>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <a href="index.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-arrow-left me-2"></i>홈으로
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-check me-2"></i>승인 요청
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Auto-fill current date/time
            document.getElementById('ediDate').value = '<?php echo date("YmdHis"); ?>';
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Handle POST request for approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['PHP_SELF']) === 'payment_approval.php') {
    header('Content-Type: application/json; charset=utf-8');
    
    $params = [
        'tid' => $_POST['tid'] ?? '',
        'mid' => $_POST['mid'] ?? '',
        'goodsAmt' => $_POST['goodsAmt'] ?? '',
        'ediDate' => $_POST['ediDate'] ?? '',
        'signData' => $_POST['signData'] ?? ''
    ];
    
    // Validate parameters
    $validation = validateApprovalParams($params);
    
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validation['errors']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Process approval
    $result = processPaymentApproval(
        $params['tid'],
        $params['mid'],
        $params['goodsAmt'],
        $params['ediDate'],
        $params['signData']
    );
    
    $formatted = formatApprovalResult($result);
    
    echo json_encode($formatted, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
?>
