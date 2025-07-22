<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$ordNm = trim($_POST['ordNm']);
$ordTel = trim($_POST['ordTel']);
$ordEmail = trim($_POST['ordEmail']);

// Validate inputs
if (empty($ordNm) || empty($ordTel) || empty($ordEmail) || $quantity < 1) {
    header('Location: order.php?product_id=' . $product_id . '&error=invalid_input');
    exit;
}

// Find product
$product = null;
foreach ($products as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

if (!$product || $quantity < 1) {
    header('Location: index.php');
    exit;
}

// Calculate total amount
$totalAmount = $product['price'] * $quantity;

// Generate payment data
$mid = FINTREE_MERCHANT_ID;
$merchantKey = FINTREE_MERCHANT_KEY;
$ordNo = generateOrderNo();
$goodsNm = $product['name'];
if ($quantity > 1) {
    $goodsNm .= ' x' . $quantity;
}
$ediDate = date("YmdHis");
$encData = generateHash($mid, $ediDate, $totalAmount, $merchantKey);
// Get the full URL for returnUrl - Use deployment server URL
$deploymentDomain = 'simple-payment-portal-gonskykim.replit.app';
$returnUrl = 'https://' . $deploymentDomain . '/payment_result.php';
$notiUrl = 'https://' . $deploymentDomain . '/payment_notification.php';
?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 진행</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="<?php echo FINTREE_JS_URL; ?>"></script>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            결제 정보 확인
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Payment Summary -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-dark border-0">
                                    <div class="card-body">
                                        <h5 class="card-title text-info mb-3">
                                            <i class="fas fa-file-invoice me-2"></i>결제 요약
                                        </h5>
                                        <table class="table table-dark table-borderless mb-0">
                                            <tr>
                                                <td width="30%"><i class="fas fa-box me-2 text-secondary"></i>상품명:</td>
                                                <td><?php echo htmlspecialchars($goodsNm); ?></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-sort-numeric-up me-2 text-secondary"></i>수량:</td>
                                                <td><?php echo $quantity; ?>개</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-user me-2 text-secondary"></i>구매자:</td>
                                                <td><?php echo htmlspecialchars($ordNm); ?></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-phone me-2 text-secondary"></i>연락처:</td>
                                                <td><?php echo htmlspecialchars($ordTel); ?></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-envelope me-2 text-secondary"></i>이메일:</td>
                                                <td><?php echo htmlspecialchars($ordEmail); ?></td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong><i class="fas fa-won-sign me-2 text-success"></i>결제 금액:</strong></td>
                                                <td><strong class="text-success h5"><?php echo formatPrice($totalAmount); ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Info -->
                        <div class="alert alert-info mb-4" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-shield-alt me-2"></i>보안 결제
                            </h6>
                            <p class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>SHA256 암호화 적용
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>HTTPS/SSL 보안 통신
                            </p>
                        </div>

                        <!-- Payment Form (Hidden) -->
                        <form name="payInit" method="post" action="<?php echo $returnUrl; ?>" style="display: none;">
                            <input type="hidden" name="payMethod" value="card">
                            <input type="hidden" name="mid" value="<?php echo $mid; ?>">
                            <input type="hidden" name="trxCd" value="0">
                            <input type="hidden" name="goodsNm" value="<?php echo htmlspecialchars($goodsNm); ?>">
                            <input type="hidden" name="ordNo" value="<?php echo $ordNo; ?>">
                            <input type="hidden" name="goodsAmt" value="<?php echo $totalAmount; ?>">
                            <input type="hidden" name="ordNm" value="<?php echo htmlspecialchars($ordNm); ?>">
                            <input type="hidden" name="ordTel" value="<?php echo htmlspecialchars($ordTel); ?>">
                            <input type="hidden" name="ordEmail" value="<?php echo htmlspecialchars($ordEmail); ?>">
                            <input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>">
                            <input type="hidden" name="notiUrl" value="<?php echo $notiUrl; ?>">
                            <input type="hidden" name="userIp" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
                            <input type="hidden" name="mbsUsrId" value="user1234">
                            <input type="hidden" name="mbsReserved" value="MallReserved">
                            <input type="hidden" name="charSet" value="UTF-8">
                            <input type="hidden" name="ediDate" value="<?php echo $ediDate; ?>">
                            <input type="hidden" name="encData" value="<?php echo $encData; ?>">
                        </form>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="history.back()">
                                    <i class="fas fa-arrow-left me-2"></i>이전으로
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-primary w-100" onclick="doPaySubmit()" id="payButton">
                                    <i class="fas fa-credit-card me-2"></i>결제 진행
                                </button>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center mt-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">결제창을 여는 중입니다...</p>
                            <p class="small text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                팝업이 차단되었다면 브라우저 설정에서 팝업을 허용해주세요
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function doPaySubmit() {
            const payButton = document.getElementById('payButton');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            // Disable button and show loading
            payButton.disabled = true;
            loadingIndicator.style.display = 'block';
            
            try {
                SendPay(document.payInit);
            } catch (error) {
                console.error('결제 오류:', error);
                alert('결제 창을 여는데 실패했습니다. 팝업 차단을 해제하고 다시 시도해주세요.');
                
                // Re-enable button and hide loading
                payButton.disabled = false;
                loadingIndicator.style.display = 'none';
            }
        }

        // 결제창 return 함수 (이름 변경 불가)
        function pay_result_submit() {
            document.getElementById('loadingIndicator').style.display = 'none';
            payResultSubmit();
        }

        // 결제창 종료 함수 (이름 변경 불가)
        function pay_result_close() {
            const payButton = document.getElementById('payButton');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            payButton.disabled = false;
            loadingIndicator.style.display = 'none';
            
            alert('결제를 취소하였습니다.');
        }

        // 페이지 로드 완료 후 자동 포커스
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('payButton').focus();
        });
    </script>
</body>
</html>
