<?php
require_once 'config.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$product = null;

// Find selected product
foreach ($products as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문하기 - <?php echo htmlspecialchars($product['name']); ?></title>
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
                            <i class="fas fa-shopping-cart me-2"></i>
                            주문 정보 입력
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Product Info -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-dark border-0">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 text-center">
                                                <i class="fas fa-box fa-3x text-secondary"></i>
                                            </div>
                                            <div class="col-md-10">
                                                <h5 class="card-title text-info mb-2">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </h5>
                                                <p class="card-text text-muted mb-2">
                                                    <?php echo htmlspecialchars($product['description']); ?>
                                                </p>
                                                <span class="h5 text-success mb-0">
                                                    단가: <?php echo formatPrice($product['price']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Form -->
                        <form action="payment.php" method="POST" id="orderForm" novalidate>
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity" class="form-label">
                                        <i class="fas fa-sort-numeric-up me-2"></i>수량 <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="1" min="1" max="99" required onchange="updateTotal()">
                                    <div class="invalid-feedback">
                                        수량을 선택해주세요 (1-99)
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calculator me-2"></i>총 결제금액
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="fas fa-won-sign"></i>
                                        </span>
                                        <div class="form-control bg-secondary border-0 fw-bold" id="totalAmount">
                                            <?php echo formatPrice($product['price']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">
                                <i class="fas fa-user me-2 text-info"></i>구매자 정보
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ordNm" class="form-label">
                                        구매자명 <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="ordNm" name="ordNm" 
                                           required maxlength="30" placeholder="홍길동">
                                    <div class="invalid-feedback">
                                        구매자명을 입력해주세요
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ordTel" class="form-label">
                                        휴대폰번호 <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="ordTel" name="ordTel" 
                                           required maxlength="20" placeholder="01012345678">
                                    <div class="invalid-feedback">
                                        올바른 휴대폰번호를 입력해주세요 (숫자만 10-11자리)
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="ordEmail" class="form-label">
                                    이메일 주소 <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="ordEmail" name="ordEmail" 
                                       required placeholder="buyer@example.com">
                                <div class="invalid-feedback">
                                    올바른 이메일 주소를 입력해주세요
                                </div>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>결제 안내
                                </h6>
                                <ul class="mb-0">
                                    <li>실제 결제가 진행되는 테스트입니다</li>
                                    <li>결제 완료 후 당일 취소가 가능합니다</li>
                                    <li>팝업 차단을 해제해주세요</li>
                                </ul>
                            </div>

                            <div class="row mt-4">
                                <div class="col-6">
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-arrow-left me-2"></i>상품 목록
                                    </a>
                                </div>
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-credit-card me-2"></i>결제하기
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
        const basePrice = <?php echo $product['price']; ?>;
        
        function updateTotal() {
            const quantity = document.getElementById('quantity').value;
            const total = basePrice * quantity;
            document.getElementById('totalAmount').textContent = 
                new Intl.NumberFormat('ko-KR').format(total) + '원';
        }
        
        // Form validation
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const form = this;
            
            // Clear previous validation
            form.classList.remove('was-validated');
            
            // Validate phone number
            const ordTel = document.getElementById('ordTel').value.replace(/-/g, '');
            if (!/^[0-9]{10,11}$/.test(ordTel)) {
                document.getElementById('ordTel').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('ordTel').classList.remove('is-invalid');
                document.getElementById('ordTel').classList.add('is-valid');
            }
            
            // Validate email
            const email = document.getElementById('ordEmail').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('ordEmail').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('ordEmail').classList.remove('is-invalid');
                document.getElementById('ordEmail').classList.add('is-valid');
            }
            
            // Validate required fields
            const requiredFields = ['ordNm', 'quantity'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            if (isValid) {
                form.submit();
            } else {
                form.classList.add('was-validated');
            }
        });
    </script>
</body>
</html>
