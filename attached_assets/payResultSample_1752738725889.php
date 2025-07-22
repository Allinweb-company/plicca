<?php
header("Content-Type:text/html; charset=utf-8;"); 
/*
****************************************************************************************
* <인증 결과 파라미터>
****************************************************************************************
*/
$resultCode			=	$_POST['resultCode'];		// 인증결과 : 0000(성공)
$resultMsg			=	$_POST['resultMsg'];		// 인증겨로가 메시지
$tid				=	$_POST['tid'];				// 거래ID
$payMethod			=	$_POST['payMethod'];		// 결제수단
$ediDate			=	$_POST['ediDate'];			// 결제 일시
$mid				=	$_POST['mid'];				// 상점 아이디
$ordNo				=	$_POST['ordNo'];			// 싱잠 주문번호
$goodsAmt			=	$_POST['goodsAmt'];			// 결제 금액
$reqReserved		=	$_POST['mbsReserved'];		// 상점 예약필드
$signData			=	$_POST['signData'];			// 

/*
*******************************************************
* <해쉬암호화> (수정하지 마세요)
* SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
*******************************************************
*/ 
$merchantKey	= "발급받은 상점키"; 						// 상점키
$encData = bin2hex(hash('sha256', $mid.$ediDate.$goodsAmt.$merchantKey, true));

/*
****************************************************************************************
* <승인 결과 파라미터 정의>
* 샘플페이지에서는 승인 결과 파라미터 중 일부만 예시되어 있으며, 
* 추가적으로 사용하실 파라미터는 연동메뉴얼을 참고하세요.
****************************************************************************************
*/

$response = "";


/*
****************************************************************************************
* <인증 결과 성공시 승인 진행>
****************************************************************************************
*/
if($_POST['resultCode'] === "0000") {
	
	/*
	****************************************************************************************
	* <승인 요청 >
	****************************************************************************************
	*/
	try{
		$data = Array(
			'tid' => $tid,
			'mid' => $mid,
			'goodsAmt' => $goodsAmt,
			'ediDate' => $ediDate,
			'charSet' => 'utf-8',
			'encData' => $encData,
			'signData' => $signData
		);
		$response = reqPost($data, "https://api.fintree.kr/pay.do");
		jsonRespDump($response);
		
	} catch(Exception $e){
		// 실패처리
	}
		
} else {
	// 인증 실패처리
}

// API CALL foreach 예시
function jsonRespDump($resp){
	$respArr = json_decode($resp);
	foreach ( $respArr as $key => $value ){
		echo "$key=". $value."<br />";
	}
}

//Post api call
function reqPost(Array $data, $url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);					//connection timeout 15 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));	//POST data
	curl_setopt($ch, CURLOPT_POST, true);
	$response = curl_exec($ch);
	curl_close($ch);	 
	return $response;
}

?>
