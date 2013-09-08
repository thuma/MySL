<?php
header('Content-type: application/json; charset=UTF-8');

// Generate temp coockie:
$tmpfname = tempnam("/tmp", "coockie");

// Check input:
if(isset($_GET['username']) == FALSE OR isset($_GET['password']) == FALSE){
	die('{"error":"no username / password supplied"}');
}

// Handle username and password:
$_GET['username'] = preg_replace('/"/', '', $_GET['username']);
$_GET['password'] = preg_replace('/"/', '', $_GET['password']);

// Auth user:
$curl = curl_init("https://sl.se/ext/mittsl/api/authenticate.json");
curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
	'Accept:*/*',
	'Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3',
	'Accept-Encoding:gzip,deflate,sdch',
	'Accept-Language:sv-SE,sv;q=0.8,en-US;q=0.6,en;q=0.4',
	'Connection:keep-alive',
	'Content-Type:application/json; charset=UTF-8',
	'Host:sl.se',
	'X-Requested-With:XMLHttpRequest'));
curl_setopt( $curl,CURLOPT_ENCODING , "gzip");
$request = json_decode('{"redirect":{"200":"/sv/Resenar/Mitt-SL/MittSL-Oversikt/"},"post_data":{"username":"'.$_GET['username'].'","password":"'.$_GET['password'].'"},"form_name":"Authenticate"}');
curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31");
curl_setopt( $curl, CURLOPT_POST, true); 
curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($request));
curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt( $curl, CURLOPT_COOKIEJAR, $tmpfname);
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
$login = json_decode(curl_exec($curl));
if_debug('Login',$login);

// Check input:
if(isset($login->result_data->authentication_session->party_ref->ref) == FALSE){
	die('{"error":"login failed, username or password incorrect?"}');
}

// Get user id:
$userid = $login->result_data->authentication_session->party_ref->ref;
if_debug('User data:', $userid);

// User cookiefile instead:
curl_setopt( $curl, CURLOPT_COOKIEFILE, $tmpfname);

// Get user cards:
curl_setopt( $curl, CURLOPT_POSTFIELDS, ""); 
curl_setopt( $curl, CURLOPT_POST, false); 
curl_setopt( $curl, CURLOPT_URL, "https://sl.se/ext/mittsl/api/travel_card.json?queryproperty=owner.ref&value=".$userid);
$cards = json_decode(curl_exec($curl))->result_data->travel_card_list;
if_debug('Cards:', $cards);

// Get value of each card:
foreach($cards as $key => $card){
	
	// Get all products for card:
	curl_setopt( $curl, CURLOPT_URL, "https://sl.se/ext/mittsl/api/".$card->travel_card->href.".json");
	$cards[$key]->travel_card = json_decode(curl_exec($curl))->result_data->travel_card;
	if_debug('Card:'.$key, $cards[$key]);
	
	// Get transaktions of card:
	curl_setopt( $curl, CURLOPT_URL, "https://sl.se/ext/mittsl/api/travel_card_transaction?serial_number=".$card->travel_card->serial_number."&start=".date('Y-m-d',(time()-1296000))."&end=".date('Y-m-d')."&limit=10&skip=0");
	$cards[$key]->travel_card->travel_card_transaction_list = json_decode(curl_exec($curl))->result_data->travel_card_transaction_list;
	if_debug('Trips:'.$key, $cards[$key]);

}

// Loggout again:
curl_setopt( $curl, CURLOPT_URL, "https://sl.se/sv/Resenar/Mitt-SL/Logga-ut/");
if_debug('Logout:'.$key, curl_exec($curl));

// Close curl and kill remove cookie file:
curl_close($curl);
unlink($tmpfname);

// combine user data and cards:
$all->user = $login->result_data->authentication_session->party_ref;
$all->cards = $cards;

// Output the data to the user:
print json_encode($all);

// Function to print debugg data:
function if_debug($text,$data){
	if(isset($_GET['debug']))
		{	
		print $text."\n";
		print_r($data);
		print "\n";
		}
}

?>

