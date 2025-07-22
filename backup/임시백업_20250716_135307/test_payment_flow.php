<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>수동 결제 테스트</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <script src="https://api.fintree.kr/js/v1/pgAsistant.js"></script>
    <style>
        body { background: #1a1d23; color: #fff; }
        .form-control { background: #2d3748; border: 1px solid #4a5568; color: #fff; }
        .form-control:focus { background: #2d3748; border-color: #38a169; color: #fff; box-shadow: 0 0 0 0.2rem rgba(56, 161, 105, 0.25); }
        .btn-payment { background: linear-gradient(45deg, #38a169, #2f855a); border: none; }
        .btn-payment:hover { background: linear-gradient(45deg, #2f855a, #276749); }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">수동 결제 테스트</h2>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card bg-dark border-secondary">
                    <div class="card-body">
                        <h5 class="card-title">결제 정보 입력</h5>
                        
                        <form id="paymentForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">주문번호</label>
                                    <input type="text" class="form-control" id="ordNo" value="1234">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">상품명</label>
                                    <input type="text" class="form-control" id="goodsNm" value="올인웹">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">결제금액 (원)</label>
                                    <input type="number" class="form-control" id="goodsAmt" value="132">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">구매자명</label>
                                    <input type="text" class="form-control" id="ordNm" value="김성곤">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">휴대폰번호</label>
                                    <input type="text" class="form-control" id="ordTel" value="01022691000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">이메일</label>
                                    <input type="email" class="form-control" id="ordEmail" value="customer_1234@temp.com">
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="button" class="btn btn-payment btn-lg" onclick="openPayment()">
                                    결제창 열기
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        <div id="status" class="alert alert-info">
                            결제 정보를 입력하고 "결제창 열기" 버튼을 클릭하세요.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPayment() {
            // 폼에서 입력된 값들 가져오기
            const ordNo = document.getElementById('ordNo').value;
            const goodsNm = document.getElementById('goodsNm').value;
            const goodsAmt = parseInt(document.getElementById('goodsAmt').value);
            const ordNm = document.getElementById('ordNm').value;
            const ordTel = document.getElementById('ordTel').value;
            const ordEmail = document.getElementById('ordEmail').value;
            
            // 현재 시간으로 ediDate 생성
            const now = new Date();
            const ediDate = now.getFullYear() + 
                           String(now.getMonth() + 1).padStart(2, '0') + 
                           String(now.getDate()).padStart(2, '0') + 
                           String(now.getHours()).padStart(2, '0') + 
                           String(now.getMinutes()).padStart(2, '0') + 
                           String(now.getSeconds()).padStart(2, '0');
            
            // 결제 데이터 구성
            const paymentData = {
                mid: "mimich067m",
                trxCd: "0",
                goodsNm: goodsNm,
                ordNo: ordNo,
                goodsAmt: goodsAmt,
                ordNm: ordNm,
                ordTel: ordTel,
                ordEmail: ordEmail,
                returnUrl: "https://simple-payment-portal-gonskykim.replit.app/payment_result.php",
                notiUrl: "https://simple-payment-portal-gonskykim.replit.app/payment_notification.php",
                userIp: "127.0.0.1",
                mbsUsrId: "manual_user_" + ediDate.substr(-6),
                mbsReserved: "ManualTest",
                charSet: "UTF-8",
                ediDate: ediDate
            };
            
            document.getElementById('status').innerHTML = '<div class="text-warning">결제창을 여는 중...</div>';
            document.getElementById('status').className = 'alert alert-warning';
            
            // Fintree 결제창 오픈
            pgAsistant.requestPay(paymentData, function(result) {
                document.getElementById('status').innerHTML = '<div class="text-success">결제 응답:</div><pre>' + JSON.stringify(result, null, 2) + '</pre>';
                document.getElementById('status').className = 'alert alert-success';
                console.log('결제 결과:', result);
            });
        }
    </script>
</body>
</html>