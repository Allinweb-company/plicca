<?php
session_start();
require_once 'config.php';

// 테스트 모드 설정
$testMode = true;
$testOrderNo = '752654189185'; // 실제 테스트에 사용된 주문번호

// 결제 데이터 세션에 저장 (실제 결제 시뮬레이션)
$_SESSION['payment_data'] = [
    'ordNo' => $testOrderNo,
    'goodsNm' => '올인웹',
    'goodsAmt' => '132',
    'ordNm' => '김성곤',
    'ordTel' => '01022691000',
    'ordEmail' => 'customer@temp.com'
];

?>
<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 흐름 테스트</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-card {
            background: linear-gradient(135deg, #1a1d23 0%, #2d3748 100%);
            border: 1px solid #4a5568;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 30px;
            margin-bottom: 20px;
        }
        .test-btn {
            background: linear-gradient(45deg, #38a169, #2f855a);
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: 10px;
        }
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(56, 161, 105, 0.4);
        }
        .log-box {
            background: #1a1d23;
            border: 1px solid #4a5568;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .log-entry {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background: #2d3748;
        }
        .log-success { border-left: 4px solid #38a169; }
        .log-info { border-left: 4px solid #3182ce; }
        .log-warning { border-left: 4px solid #ecc94b; }
        .log-error { border-left: 4px solid #e53e3e; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="test-card">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-vial me-2"></i>
                        결제 흐름 완전 테스트
                    </h2>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>테스트 시나리오</h5>
                        <ol class="mb-0">
                            <li>결제 데이터 생성 (주문번호: <?php echo $testOrderNo; ?>)</li>
                            <li>결제창 시뮬레이션 (TID 자동 생성)</li>
                            <li>결제 승인 처리 (테스트 승인)</li>
                            <li>NOTI 백엔드 알림 전송</li>
                            <li>결제 완료 화면 표시</li>
                        </ol>
                    </div>

                    <div class="text-center my-4">
                        <button onclick="startTest()" class="btn test-btn btn-lg">
                            <i class="fas fa-play me-2"></i>
                            전체 결제 흐름 테스트 시작
                        </button>
                        
                        <button onclick="testNotiOnly()" class="btn test-btn btn-lg btn-warning">
                            <i class="fas fa-bell me-2"></i>
                            NOTI 알림만 테스트
                        </button>
                        
                        <button onclick="clearLogs()" class="btn test-btn btn-lg btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            로그 초기화
                        </button>
                    </div>

                    <div class="log-box" id="logBox">
                        <div class="log-entry log-info">
                            <i class="fas fa-clock me-2"></i>
                            테스트 준비 완료...
                        </div>
                    </div>
                </div>

                <!-- 결과 표시 영역 -->
                <div id="resultArea" style="display: none;">
                    <div class="test-card">
                        <h3 class="text-success mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            테스트 결과
                        </h3>
                        <div id="resultContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addLog(message, type = 'info') {
            const logBox = document.getElementById('logBox');
            const timestamp = new Date().toLocaleTimeString('ko-KR');
            const icons = {
                'success': 'fa-check-circle',
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle',
                'error': 'fa-times-circle'
            };
            
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry log-${type}`;
            logEntry.innerHTML = `
                <i class="fas ${icons[type]} me-2"></i>
                [${timestamp}] ${message}
            `;
            
            logBox.appendChild(logEntry);
            logBox.scrollTop = logBox.scrollHeight;
        }

        function clearLogs() {
            document.getElementById('logBox').innerHTML = '';
            addLog('로그가 초기화되었습니다.', 'info');
        }

        async function startTest() {
            addLog('결제 흐름 테스트를 시작합니다...', 'info');
            
            // 1단계: 결제 데이터 준비
            addLog('1단계: 결제 데이터 준비 중...', 'info');
            
            const paymentData = {
                ordNo: '<?php echo $testOrderNo; ?>',
                goodsNm: '올인웹',
                goodsAmt: '132',
                ordNm: '김성곤',
                ordTel: '01022691000',
                ordEmail: 'customer@temp.com',
                mid: 'mimich067m'
            };
            
            addLog('결제 데이터 준비 완료: ' + JSON.stringify(paymentData), 'success');
            
            // 2단계: 결제 시뮬레이션 실행
            addLog('2단계: 결제 시뮬레이션 실행 중...', 'info');
            
            // 시뮬레이션 URL로 이동
            const simulationUrl = `/payment_result.php?simulation=true&testOrdNo=${paymentData.ordNo}&goodsAmt=${paymentData.goodsAmt}&goodsNm=${encodeURIComponent(paymentData.goodsNm)}&ordNm=${encodeURIComponent(paymentData.ordNm)}&mid=${paymentData.mid}`;
            
            addLog('시뮬레이션 URL 생성: ' + simulationUrl, 'success');
            addLog('3초 후 결제 결과 페이지로 이동합니다...', 'warning');
            
            setTimeout(() => {
                window.location.href = simulationUrl;
            }, 3000);
        }

        async function testNotiOnly() {
            addLog('NOTI 백엔드 알림 테스트를 시작합니다...', 'info');
            
            const notiData = {
                resultCd: '3001',
                resultMsg: '테스트 결제 승인 성공',
                tid: 'mimich067m0101' + Date.now(),
                ordNo: '<?php echo $testOrderNo; ?>',
                amt: '132',
                appNo: 'TEST' + Date.now().toString().slice(-6),
                appDt: new Date().toISOString().slice(0, 10).replace(/-/g, ''),
                appTm: new Date().toTimeString().slice(0, 8).replace(/:/g, ''),
                payMethod: 'CARD'
            };
            
            addLog('NOTI 데이터 생성: ' + JSON.stringify(notiData), 'success');
            
            try {
                const response = await fetch('/payment_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(notiData)
                });
                
                const result = await response.text();
                
                if (result === 'OK') {
                    addLog('NOTI 알림 전송 성공! 응답: ' + result, 'success');
                } else {
                    addLog('NOTI 알림 응답: ' + result, 'warning');
                }
                
                // 로그 파일 확인
                addLog('payment_errors.log 파일을 확인하여 NOTI 처리 결과를 확인하세요.', 'info');
                
            } catch (error) {
                addLog('NOTI 알림 전송 실패: ' + error.message, 'error');
            }
        }

        // 페이지 로드 시 실행
        document.addEventListener('DOMContentLoaded', function() {
            addLog('테스트 페이지가 로드되었습니다.', 'success');
            addLog('결제 흐름 테스트를 시작하려면 버튼을 클릭하세요.', 'info');
        });
    </script>
</body>
</html>