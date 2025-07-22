<?php
require_once 'config.php';

// 최근 결제 목록을 표시할 수 있도록 로그에서 읽어오기
$recentPayments = [];
if (file_exists('payment_errors.log')) {
    $logContent = file_get_contents('payment_errors.log');
    $lines = explode("\n", $logContent);
    
    foreach ($lines as $line) {
        if (strpos($line, 'Approval Request/Response') !== false && strpos($line, '"resultCd":"3001"') !== false) {
            if (preg_match('/Context: (.+)$/', $line, $matches)) {
                $data = json_decode($matches[1], true);
                if ($data && isset($data['response'])) {
                    $payment = $data['response'];
                    $recentPayments[] = [
                        'tid' => $payment['tid'] ?? '',
                        'ordNo' => $payment['ordNo'] ?? '',
                        'amt' => $payment['amt'] ?? '',
                        'goodsName' => $payment['goodsName'] ?? '',
                        'ordNm' => $payment['ordNm'] ?? '',
                        'appDtm' => $payment['appDtm'] ?? ''
                    ];
                }
            }
        }
    }
}

// 최신 결제부터 표시
$recentPayments = array_reverse($recentPayments);
$recentPayments = array_slice($recentPayments, 0, 10); // 최근 10개만
?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 취소</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card border-0 shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-undo me-2"></i>결제 취소
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($recentPayments)): ?>
                            <div class="alert alert-info border-0" role="alert">
                                <h5 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>취소 가능한 결제가 없습니다
                                </h5>
                                <p class="mb-0">최근 결제 내역이 없습니다. 먼저 결제를 진행해주세요.</p>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>상품 목록으로
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning border-0" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>결제 취소 안내
                                </h6>
                                <p class="mb-0">테스트 결제는 당일 내에만 취소가 가능합니다. 실 결제 시에는 취소 정책을 확인해주세요.</p>
                            </div>

                            <h5 class="mb-3">
                                <i class="fas fa-list me-2"></i>최근 결제 내역
                            </h5>

                            <!-- 데스크톱용 테이블 -->
                            <div class="d-none d-md-block">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover">
                                        <thead>
                                            <tr>
                                                <th width="20%">거래번호</th>
                                                <th width="15%">주문번호</th>
                                                <th width="20%">상품명</th>
                                                <th width="15%">주문자</th>
                                                <th width="10%">금액</th>
                                                <th width="15%">결제시간</th>
                                                <th width="5%">취소</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentPayments as $payment): ?>
                                            <tr>
                                                <td class="text-info">
                                                    <small><?php echo htmlspecialchars($payment['tid']); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($payment['ordNo']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($payment['goodsName']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['ordNm']); ?></td>
                                                <td class="text-warning">
                                                    <strong><?php echo number_format($payment['amt']); ?>원</strong>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($payment['appDtm']); ?></small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="cancelPayment('<?php echo htmlspecialchars($payment['tid']); ?>', '<?php echo htmlspecialchars($payment['ordNo']); ?>', '<?php echo htmlspecialchars($payment['amt']); ?>')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 모바일용 카드 레이아웃 -->
                            <div class="d-md-none">
                                <?php foreach ($recentPayments as $index => $payment): ?>
                                <div class="card bg-dark border-secondary mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title text-warning mb-0">
                                                <?php echo htmlspecialchars($payment['goodsName']); ?>
                                            </h6>
                                            <span class="badge bg-success"><?php echo number_format($payment['amt']); ?>원</span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">주문자:</small>
                                            <span class="text-white"><?php echo htmlspecialchars($payment['ordNm']); ?></span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">거래번호:</small>
                                            <span class="text-info text-break"><?php echo htmlspecialchars($payment['tid']); ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">결제시간:</small>
                                            <span class="text-white"><?php echo htmlspecialchars($payment['appDtm']); ?></span>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button class="btn btn-danger" 
                                                    onclick="cancelPayment('<?php echo htmlspecialchars($payment['tid']); ?>', '<?php echo htmlspecialchars($payment['ordNo']); ?>', '<?php echo htmlspecialchars($payment['amt']); ?>')">
                                                <i class="fas fa-times me-2"></i>이 결제 취소하기
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="text-center mt-4">
                                <a href="index.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-2"></i>돌아가기
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 취소 확인 모달 -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>결제 취소 확인
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>정말로 이 결제를 취소하시겠습니까?</p>
                    <div class="alert alert-secondary border-0">
                        <small>
                            <strong>거래번호:</strong> <span id="modalTid"></span><br>
                            <strong>주문번호:</strong> <span id="modalOrdNo"></span><br>
                            <strong>취소금액:</strong> <span id="modalAmt"></span>원
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">아니오</button>
                    <button type="button" class="btn btn-danger" onclick="confirmCancel()">
                        <i class="fas fa-check me-2"></i>네, 취소합니다
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentTid = '';
        let currentOrdNo = '';
        let currentAmt = '';

        function cancelPayment(tid, ordNo, amt) {
            currentTid = tid;
            currentOrdNo = ordNo;
            currentAmt = amt;
            
            document.getElementById('modalTid').textContent = tid;
            document.getElementById('modalOrdNo').textContent = ordNo;
            document.getElementById('modalAmt').textContent = parseInt(amt).toLocaleString();
            
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }

        function confirmCancel() {
            // 폼 생성 및 제출
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cancel_result.php';
            
            const tidInput = document.createElement('input');
            tidInput.type = 'hidden';
            tidInput.name = 'tid';
            tidInput.value = currentTid;
            
            const ordNoInput = document.createElement('input');
            ordNoInput.type = 'hidden';
            ordNoInput.name = 'ordNo';
            ordNoInput.value = currentOrdNo;
            
            const amtInput = document.createElement('input');
            amtInput.type = 'hidden';
            amtInput.name = 'canAmt';
            amtInput.value = currentAmt;
            
            form.appendChild(tidInput);
            form.appendChild(ordNoInput);
            form.appendChild(amtInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>