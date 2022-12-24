<?php

/*
   _____ __    _                          __  
  / ___// /_  (_)___  __  __  ____  ___  / /_ 
  \__ \/ __ \/ / __ \/ / / / / __ \/ _ \/ __/ 
 ___/ / / / / / /_/ / /_/ / / / / /  __/ /_  
/____/_/ /_/_/ .___/\__, (_)_/ /_/\___/\__/ 
            /_/    /____/                                         

Modül Versiyon: 1
Son Güncelleme Tarihi: 8.02.2020

efekanrasit version
Son Güncelleme Tarihi: 24.12.2022
*/

function shipy_nolocalcc() {}

function shipy_config() {
	$configarray = array(
		"FriendlyName" => array(
			"Type" => "System",
			"Value" => "Shipy-KK"
		),
		"apiKey" => array(
			"FriendlyName" => "API Anahtarı", 
			"Type" => "text", 
			"Size" => "80",
		),
		"currency" => array(
			"FriendlyName" => "Para Birimi", 
			"Type" => "text", 
			"Description" => "Ödeme sırasında kullanılacak para birimi.", 
			"Default" => "TRY", 
		),
		"payPageLang" => array(
			"FriendlyName" => "Sayfa Dili", 
			"Type" => "text", 
			"Description" => "Ödeme sayfasının dili.", 
			"Default" => "TR", 
		),
		"mailPageLang" => array(
			"FriendlyName" => "Para Birimi", 
			"Type" => "text", 
			"Description" => "Ödeme sonrası gönderilecek mailin dili.", 
			"Default" => "TR", 
		)
	);
	return $configarray;
}
function shipy_activate() {
}
function shipy_link($params)
{
	ini_set('display_errors', 0); error_reporting(0);
	if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	} elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		$ip = $_SERVER["REMOTE_ADDR"];
	}

	$amount = $params['amount'];
	$return_id = uniqid().'SHPWHMCS'.$params['invoiceid'];

	$fields = array(
		"usrIp" => $ip,
		"usrName" => $params['clientdetails']['fullname'],
		"usrAddress" => $params['clientdetails']['address1'],
		"usrPhone" => $params['clientdetails']['phonenumber'],
		"usrEmail" => $params['clientdetails']['email'],
		"amount" => $amount,
		"returnID" => $return_id,
		"apiKey" => $params['apiKey'],

		"currency" => $params['currency'],
	    "pageLang" => $params['payPageLang'],
	    "mailLang" => $params['mailPageLang'],
	    "installment" => 0
	);

	$postvars = http_build_query($fields);
	$ch = curl_init();

	curl_setopt_array($ch, array(
		CURLOPT_URL => "https://api.shipy.dev/pay/credit_card",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => http_build_query($fields),
	));

	$result = curl_exec($ch);

	$result = json_decode($result, true);

	if ($result['status'] == "success") {
		$link = $result['link'];
	} else print("Ödeme işlemi sırasında bir hata oluştu: " . $result["message"]);

	curl_close($ch);

	return '
	<link rel="stylesheet" href="https://api.shipy.net/css/iziModal.css">
	<script src="https://api.shipy.net/js/jquery.js" type="text/javascript"></script>
	<script src="https://api.shipy.net/js/iziModal.js" type="text/javascript"></script>

	<button id="pay" class="btn btn-success" onclick="redirectToLink()" type="button">'.$params['langpaynow'].'</button>

	<script>
		function redirectToLink() {
			window.location = "'.$link.'";
		  }		  
	</script>';
}

if (!defined( 'WHMCS' )) {
	exit( 'This file cannot be accessed directly' );
}

?>
