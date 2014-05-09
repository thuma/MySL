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
$curl = curl_init("https://sl.se/api/MySL/Authenticate");
curl_setopt( $curl, CURLOPT_HTTPHEADER, 'Content-Type: application/json;charset=UTF-8');
$request = json_decode('{"username":'.$_GET['username'].',"password":'.$_GET['password'].'}');
curl_setopt( $curl, CURLOPT_POST, true); 
curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($request));
curl_setopt( $curl, CURLOPT_COOKIEJAR, $tmpfname);
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
$login = json_decode(curl_exec($curl));
if_debug('Login',$login);

// User cookiefile instead:
curl_setopt( $curl, CURLOPT_COOKIEFILE, $tmpfname);

// Get user cards:
curl_setopt( $curl, CURLOPT_POSTFIELDS, ""); 
curl_setopt( $curl, CURLOPT_POST, false); 
curl_setopt( $curl, CURLOPT_URL, "https://sl.se/api/ECommerse/GetShoppingCart");
$cards = json_decode(curl_exec($curl))->result_data->travel_card_list;
if_debug('Cards:', $cards);

// Loggout again:
curl_setopt( $curl, CURLOPT_URL, "https://sl.se/sv/Resenar/Mitt-SL/Logga-ut/");
if_debug('Logout:'.$key, curl_exec($curl));

// Close curl and kill remove cookie file:
curl_close($curl);
unlink($tmpfname);

// Output the data to the user:
print json_encode($all);

?>

