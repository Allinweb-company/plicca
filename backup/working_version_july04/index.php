<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fintree 결제 테스트 사이트</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-credit-card me-3 text-primary"></i>
                        Fintree 결제 테스트
                    </h1>
                    <p class="lead text-muted">간단한 상품 주문 및 결제 테스트를 진행해보세요</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="text-center mb-3">
                            <i class="fas fa-box fa-3x text-secondary mb-3"></i>
                        </div>
                        <h5 class="card-title text-center">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h5>
                        <p class="card-text text-muted text-center mb-3">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        <div class="mt-auto">
                            <div class="text-center mb-3">
                                <span class="h4 text-success mb-0">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                            </div>
                            <a href="order.php?product_id=<?php echo $product['id']; ?>" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-shopping-cart me-2"></i>
                                주문하기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            테스트 안내
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">✓ 테스트 환경</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Merchant ID: hpftauth1m</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>운영 테스트 환경 사용</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>당일 취소 가능</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>SHA256 암호화 적용</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-warning mb-3">⚠ 주의사항</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>실제 결제가 진행됩니다</li>
                                    <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>테스트 후 당일 취소 필수</li>
                                    <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>소액 결제로 테스트하세요</li>
                                    <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>팝업 차단 해제 필요</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-3 bg-dark rounded">
                            <h6 class="text-info mb-2">
                                <i class="fas fa-code me-2"></i>기술 스펙
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">결제 SDK:</small><br>
                                    <span class="badge bg-secondary">pgAsistant.js</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">암호화:</small><br>
                                    <span class="badge bg-secondary">SHA256</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">통신:</small><br>
                                    <span class="badge bg-secondary">HTTPS/SSL</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 관리 기능 섹션 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>관리 기능
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="d-grid gap-2">
                                    <a href="cancel.php" class="btn btn-warning btn-lg">
                                        <i class="fas fa-undo me-2"></i>💳 결제 취소하기
                                    </a>
                                </div>
                                <div class="text-center mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        최근 결제 내역을 확인하고 당일 취소가 가능합니다
                                    </small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info border-0 mb-0">
                                    <div class="text-center">
                                        <i class="fas fa-clock me-1"></i>
                                        <strong>테스트 안내:</strong> 모든 상품이 100원으로 설정되어 있습니다.
                                        <br>테스트 결제는 당일 내에 반드시 취소해주세요.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 py-4 bg-dark">
        <div class="container">
            <div class="text-center text-muted">
                <p class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Fintree 결제 테스트 사이트 - 개발 및 테스트 목적으로만 사용
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
