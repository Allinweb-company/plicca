<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fintree 결제 테스트</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-container {
            background: linear-gradient(135deg, #1e293b 0%, #2d3748 100%);
            border: 1px solid #475569;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            padding: 4rem;
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .title {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #38bdf8, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .subtitle {
            color: #94a3b8;
            font-size: 1.2rem;
            margin-bottom: 3rem;
        }
        
        .start-button {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            border: none;
            font-size: 1.5rem;
            font-weight: bold;
            padding: 20px 50px;
            border-radius: 15px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            cursor: pointer;
        }
        
        .start-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
            background: linear-gradient(45deg, #2563eb, #1e40af);
        }
        
        .start-button:active {
            transform: translateY(-2px);
        }
        
        .icon {
            font-size: 4rem;
            color: #38bdf8;
            margin-bottom: 2rem;
        }
        
        .features {
            margin-top: 3rem;
            text-align: left;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #94a3b8;
        }
        
        .feature-item i {
            color: #10b981;
            margin-right: 1rem;
            width: 20px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="icon">
            <i class="fas fa-credit-card"></i>
        </div>
        
        <h1 class="title">
            Fintree 결제 테스트 시스템
        </h1>
        
        <p class="subtitle">
            모바일 최적화된 결제 테스트를 시작하세요
        </p>
        
        <button id="startBtn" class="start-button">
            <i class="fas fa-play me-3"></i>
            시작하기
        </button>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>API 결제준비 테스트</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>실시간 결제 시뮬레이션</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>모바일 최적화 UI</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Fintree API 연동</span>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('startBtn').addEventListener('click', function() {
            // 팝업창 옵션
            const popupFeatures = [
                'width=500',
                'height=600',
                'left=' + (screen.width / 2 - 250), // 화면 중앙에 위치
                'top=' + (screen.height / 2 - 300),
                'resizable=yes',
                'scrollbars=yes',
                'toolbar=no',
                'menubar=no',
                'location=no',
                'status=no'
            ].join(',');
            
            // 팝업창 열기
            const popup = window.open('mobile_payment.php', 'fintreePayment', popupFeatures);
            
            // 팝업창이 차단되었는지 확인
            if (!popup) {
                alert('팝업창이 차단되었습니다. 브라우저에서 팝업 허용 설정을 확인해주세요.');
                return;
            }
            
            // 팝업창 포커스
            popup.focus();
        });
    </script>
</body>
</html>