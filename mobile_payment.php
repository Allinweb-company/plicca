<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fintree 결제 테스트 메인</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .api-section {
            background: linear-gradient(135deg, #2d1b69 0%, #1e293b 100%);
            border: 1px solid #475569;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .payment-form {
            background: linear-gradient(135deg, #1a1d23 0%, #2d3748 100%);
            border: 1px solid #4a5568;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .form-control {
            background: #2d3748;
            border: 1px solid #4a5568;
            color: #fff;
        }
        .form-control:focus {
            background: #2d3748;
            border-color: #38a169;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(56, 161, 105, 0.25);
        }
        .btn-api {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            border: none;
            font-size: 1.1rem;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }
        .btn-api:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        .btn-payment {
            background: linear-gradient(45deg, #38a169, #2f855a);
            border: none;
            font-size: 1.2rem;
            padding: 15px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(56, 161, 105, 0.3);
        }
        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(56, 161, 105, 0.4);
        }
        .btn-open-payment {
            background: linear-gradient(45deg, #dc2626, #b91c1c);
            border: none;
            font-size: 1.1rem;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.3);
        }
        .btn-open-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }
        .total-amount {
            background: #2d3748;
            border: 2px solid #38a169;
            border-radius: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #38a169;
        }
        .api-response {
            background: #1e293b;
            border: 1px solid #475569;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            max-height: 400px;
            overflow-y: auto;
        }
        .api-loading {
            display: none;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1 class="text-info">
                <i class="fas fa-credit-card me-2"></i>
                Fintree 결제 테스트 시스템
            </h1>
            <p class="text-muted">API 테스트와 실제 결제를 테스트할 수 있습니다</p>
            
            <!-- PC 결제 문제 진단 링크 -->
            <div class="mt-3">
                <a href="/pc_payment_test.html" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-desktop me-2"></i>
                    PC 결제 문제 진단
                </a>
                <span class="text-warning ms-2">← PC에서 결제가 안되면 클릭하세요</span>
            </div>
        </div>

        <div class="row">
            <!-- 왼쪽: API 테스트 영역 -->
            <div class="col-lg-6 mb-4">
                <div class="api-section p-4 h-100">
                    <div class="text-center mb-4">
                        <h3 class="text-primary">
                            <i class="fas fa-cogs me-2"></i>
                            API 결제준비 테스트
                        </h3>
                        <p class="text-muted">결제 API를 테스트하고 응답 데이터를 확인하세요</p>
                    </div>

                    <!-- 오른쪽 데이터 표시 (읽기 전용) -->
                    <div class="mb-4">
                        <h5 class="text-warning mb-3">
                            <i class="fas fa-database me-2"></i>
                            API 요청 데이터 (오른쪽 상품정보에서 가져옴)
                        </h5>
                        <div class="data-display p-3" style="background: #2d3748; border-radius: 10px; border: 1px solid #4a5568;">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>주문번호:</strong> <span id="leftOrderNo" class="text-info">-</span></p>
                                    <p><strong>상품명:</strong> <span id="leftProdName" class="text-success">-</span></p>
                                    <p><strong>수량:</strong> <span id="leftQty" class="text-warning">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>결제금액:</strong> <span id="leftAmount" class="text-danger">-</span>원</p>
                                    <p><strong>구매자명:</strong> <span id="leftCustomerName" class="text-primary">-</span></p>
                                    <p><strong>전화번호:</strong> <span id="leftCustomerPhone" class="text-secondary">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button id="apiTestBtn" class="btn btn-api w-100" type="button">
                        <i class="fas fa-play me-2"></i>
                        API 결제준비요청
                    </button>
                    
                    <div id="apiLoading" class="api-loading text-center mt-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">로딩...</span>
                        </div>
                        <p class="mt-2">API 요청 중...</p>
                    </div>

                    <!-- API 응답 표시 -->
                    <div>
                        <h5 class="text-warning mb-3">
                            <i class="fas fa-server me-2"></i>
                            API 응답 데이터
                        </h5>
                        <div id="apiResponse" class="api-response p-3">
                            <div class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                API 테스트 버튼을 클릭하여 응답 데이터를 확인하세요
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 오른쪽: 결제 폼 영역 -->
            <div class="col-lg-6 mb-4">
                <div class="payment-form p-4 h-100">
                    <div class="text-center mb-4">
                        <h3 class="text-success">
                            <i class="fas fa-shopping-cart me-2"></i>
                            결제 테스트
                        </h3>
                        <p class="text-muted">상품 정보와 구매자 정보를 입력하세요</p>
                    </div>

                    <form id="paymentForm">
                        <!-- 상품 정보 -->
                        <div class="mb-4">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-box me-2"></i>
                                상품 정보
                            </h5>
                            
                            <div class="mb-3">
                                <label for="orderNo" class="form-label">주문번호</label>
                                <input type="text" class="form-control" id="orderNo" name="orderNo" 
                                       value="" placeholder="자동생성">
                            </div>
                            
                            <div class="mb-3">
                                <label for="prodName" class="form-label">상품명</label>
                                <input type="text" class="form-control" id="prodName" name="prodName" 
                                       value="올인웹" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="unitPrice" class="form-label">단가 (원)</label>
                                    <input type="number" class="form-control" id="unitPrice" name="unitPrice" 
                                           value="100000" min="1" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="qty" class="form-label">수량</label>
                                    <input type="number" class="form-control" id="qty" name="qty" 
                                           value="1" min="1" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="totalAmountDisplay" class="form-label">결제금액 (원)</label>
                                    <input type="text" class="form-control" id="totalAmountDisplay" name="totalAmountDisplay" 
                                           readonly style="background-color: #374151;">
                                </div>
                            </div>
                        </div>

                        <!-- 구매자 정보 -->
                        <div class="mb-4">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-user me-2"></i>
                                구매자 정보
                            </h5>
                            
                            <div class="mb-3">
                                <label for="customerName" class="form-label">구매자명</label>
                                <input type="text" class="form-control" id="customerName" name="customerName" 
                                       value="김성곤" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customerPhone" class="form-label">휴대폰 번호</label>
                                <input type="tel" class="form-control" id="customerPhone" name="customerPhone" 
                                       value="01022691000" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customerEmail" class="form-label">이메일</label>
                                <input type="email" class="form-control" id="customerEmail" name="customerEmail" 
                                       value="customer@temp.com" required>
                            </div>
                        </div>



                        <!-- 버튼 영역 -->
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-payment">
                                <i class="fas fa-arrow-right me-2"></i>
                                결제하기 (payment_prepare.html로 이동)
                            </button>
                            
                            <button type="button" id="directPaymentBtn" class="btn btn-open-payment">
                                <i class="fas fa-credit-card me-2"></i>
                                결제창 오픈 (직접 결제)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 숨겨진 Fintree 폼 -->
    <form id="fintreeForm" name="payInit" method="post" style="display:none">
        <input type="hidden" name="payMethod">
        <input type="hidden" name="mid">
        <input type="hidden" name="trxCd">
        <input type="hidden" name="goodsNm">
        <input type="hidden" name="ordNo">
        <input type="hidden" name="goodsAmt">
        <input type="hidden" name="ordNm">
        <input type="hidden" name="ordTel">
        <input type="hidden" name="ordEmail">
        <input type="hidden" name="returnUrl">
        <input type="hidden" name="notiUrl">
        <input type="hidden" name="userIp">
        <input type="hidden" name="mbsUsrId">
        <input type="hidden" name="mbsReserved">
        <input type="hidden" name="charSet">
        <input type="hidden" name="ediDate">
        <input type="hidden" name="encData">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPaymentData = null;

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            // 주문번호 자동 생성
            if (!document.getElementById('orderNo').value) {
                document.getElementById('orderNo').value = 'ORD' + Date.now();
            }
            
            // 오른쪽 → 왼쪽 데이터 연동 함수
            function syncRightToLeft() {
                const orderNo = document.getElementById('orderNo').value || '-';
                const prodName = document.getElementById('prodName').value || '-';
                const qty = document.getElementById('qty').value || '-';
                const unitPrice = parseInt(document.getElementById('unitPrice').value) || 0;
                const totalAmount = unitPrice * parseInt(qty || 0);
                const customerName = document.getElementById('customerName').value || '-';
                const customerPhone = document.getElementById('customerPhone').value || '-';
                
                // 왼쪽 표시 업데이트
                document.getElementById('leftOrderNo').textContent = orderNo;
                document.getElementById('leftProdName').textContent = prodName;
                document.getElementById('leftQty').textContent = qty;
                document.getElementById('leftAmount').textContent = totalAmount.toLocaleString();
                document.getElementById('leftCustomerName').textContent = customerName;
                document.getElementById('leftCustomerPhone').textContent = customerPhone;
                
                // 오른쪽 결제금액 표시 업데이트
                document.getElementById('totalAmountDisplay').value = totalAmount.toLocaleString();
            }
            
            // 오른쪽 폼 입력 이벤트 리스너
            ['orderNo', 'prodName', 'unitPrice', 'qty', 'customerName', 'customerPhone'].forEach(function(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', syncRightToLeft);
                }
            });
            
            // 초기 동기화
            syncRightToLeft();

            // API 테스트 버튼 이벤트
            document.getElementById('apiTestBtn').addEventListener('click', function() {
                testApiPayment();
            });

            // 결제 폼 제출 이벤트 (payment_prepare.html로 이동)
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // 왼쪽 API ordNo 확인
                const apiOrderNo = sessionStorage.getItem('apiOrderNo');
                if (apiOrderNo) {
                    console.log('API ordNo와 연동하여 결제 진행:', apiOrderNo);
                } else {
                    console.log('API ordNo 없음, 새 주문번호로 결제 진행');
                }
                
                // 폼 데이터를 세션스토리지에 저장 (orderNo 추가)
                const data = {
                    orderNo: document.getElementById('orderNo').value,
                    prodName: document.getElementById('prodName').value,
                    unitPrice: parseInt(document.getElementById('unitPrice').value),
                    qty: parseInt(document.getElementById('qty').value),
                    customerName: document.getElementById('customerName').value,
                    customerPhone: document.getElementById('customerPhone').value,
                    customerEmail: document.getElementById('customerEmail').value
                };
                
                sessionStorage.setItem('paymentFormData', JSON.stringify(data));
                window.location.href = '/payment_prepare.html';
            });

            // 직접 결제 버튼 이벤트
            document.getElementById('directPaymentBtn').addEventListener('click', function() {
                if (currentPaymentData) {
                    openPaymentWindow(currentPaymentData);
                } else {
                    alert('먼저 API 결제준비요청을 실행해주세요.');
                }
            });
        });

        // API 결제준비요청 함수
        async function testApiPayment() {
            const apiBtn = document.getElementById('apiTestBtn');
            const loading = document.getElementById('apiLoading');
            const responseDiv = document.getElementById('apiResponse');
            
            // 로딩 시작
            apiBtn.disabled = true;
            loading.style.display = 'block';
            responseDiv.innerHTML = '<div class="text-center text-warning"><i class="fas fa-spinner fa-spin me-2"></i>API 요청 중...</div>';

            try {
                // 오른쪽 폼에서 데이터 수집
                const orderNo = document.getElementById('orderNo').value || Date.now().toString();
                const prodName = document.getElementById('prodName').value;
                const qty = parseInt(document.getElementById('qty').value);
                const unitPrice = parseInt(document.getElementById('unitPrice').value);
                const totalAmount = unitPrice * qty;
                const receiverName = document.getElementById('customerName').value;
                const receiverCall = document.getElementById('customerPhone').value;
                
                const data = {
                    orderNo: orderNo,
                    prodName: prodName,
                    qty: qty,
                    itemPrice: totalAmount, // 계산된 총 금액 (단가 × 수량)
                    receiverName: receiverName,
                    receiverCall: receiverCall,
                    receiverEmail: document.getElementById('customerEmail').value // 사용자 입력 이메일 추가
                };

                console.log('API 요청 데이터:', data);

                // API 호출
                const response = await fetch('/api/payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                console.log('API 응답:', result);

                // 응답 데이터 표시
                if (result.success) {
                    currentPaymentData = result;
                    
                    // API 응답의 ordNo를 sessionStorage에 저장 (오른쪽 폼과 연동)
                    sessionStorage.setItem('apiOrderNo', result.ordNo);
                    console.log('API ordNo 저장됨:', result.ordNo);
                    
                    // API 응답 전체 데이터를 localStorage에 저장 (payment_prepare.html에서 사용)
                    localStorage.setItem('paymentApiData', JSON.stringify(result));
                    console.log('API 전체 데이터 localStorage에 저장 완료');
                    
                    responseDiv.innerHTML = `
                        <div class="text-success mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            API 요청 성공
                        </div>
                        <div class="alert alert-info">
                            <strong>주문번호 연동:</strong> ${result.ordNo}<br>
                            <small>이 주문번호가 오른쪽 결제 폼에서도 사용됩니다.</small><br>
                            <strong>💾 localStorage 저장 완료:</strong> payment_prepare.html에서 사용 가능
                        </div>
                        <pre class="text-light">${JSON.stringify(result, null, 2)}</pre>
                    `;
                    
                    // 직접 결제 버튼 활성화
                    document.getElementById('directPaymentBtn').disabled = false;
                } else {
                    responseDiv.innerHTML = `
                        <div class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            API 요청 실패
                        </div>
                        <pre class="text-light">${JSON.stringify(result, null, 2)}</pre>
                    `;
                }

            } catch (error) {
                console.error('API 오류:', error);
                responseDiv.innerHTML = `
                    <div class="text-danger mb-3">
                        <i class="fas fa-times-circle me-2"></i>
                        오류 발생
                    </div>
                    <pre class="text-light">${error.message}</pre>
                `;
            } finally {
                // 로딩 종료
                apiBtn.disabled = false;
                loading.style.display = 'none';
            }
        }

        // 결제창 오픈 함수
        function openPaymentWindow(data) {
            const form = document.getElementById('fintreeForm');
            const pd = data.paymentData;

            // PG SDK 스크립트 동적 로드
            const script = document.createElement('script');
            script.src = data.fintreeJsUrl;
            script.onload = () => {
                try {
                    // 폼 액션 설정
                    form.action = pd.returnUrl;

                    // 결제 필드 세팅
                    form.payMethod.value = 'card';
                    form.mid.value = pd.mid;
                    form.trxCd.value = pd.trxCd;
                    form.goodsNm.value = pd.goodsNm;
                    form.ordNo.value = pd.ordNo;
                    form.goodsAmt.value = pd.goodsAmt;
                    form.ordNm.value = pd.ordNm;
                    form.ordTel.value = pd.ordTel;
                    form.ordEmail.value = pd.ordEmail;
                    form.returnUrl.value = pd.returnUrl;
                    form.notiUrl.value = pd.notiUrl;
                    form.userIp.value = pd.userIp;
                    form.mbsUsrId.value = pd.mbsUsrId;
                    form.mbsReserved.value = pd.mbsReserved;
                    form.charSet.value = pd.charSet;
                    form.ediDate.value = pd.ediDate;
                    form.encData.value = pd.encData;

                    // SendPay 호출
                    SendPay(form);
                } catch (err) {
                    console.error('결제창 오픈 오류:', err);
                    alert('결제창을 여는 중 오류가 발생했습니다: ' + err.message);
                }
            };
            script.onerror = () => {
                alert('PG SDK 로드에 실패했습니다. 네트워크를 확인해주세요.');
            };
            document.head.appendChild(script);
        }

        // Fintree 콜백 함수들
        function pay_result_submit() {
            console.log('pay_result_submit 호출됨 - 결제 완료');
            payResultSubmit();
        }

        function pay_result_close() {
            console.log('결제창 종료 - 사용자 취소');
            alert('결제를 취소하였습니다.');
        }
    </script>
</body>
</html>