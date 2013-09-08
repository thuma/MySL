<?php
header('Content-Type: text/html; charset=utf-8');

if($_GET['key'] != '{your secret key}')
	{
	die();
	}
	
if(substr(trim(strtolower($_POST['message'])),0,6) == 'login:')
	{
	$delar = preg_split('/,/', substr($_POST['message'],6));
	$utdata = new stdClass();
	$utdata->username = $delar[0];
	$utdata->password = $delar[1];
	file_put_contents('accounts/'.md5($_POST['from']).".json", json_encode($utdata)); 
	}

if(is_file('accounts/'.md5($_POST['from']).'.json'))
	{
	$indata = json_decode(file_get_contents('accounts/'.md5($_POST['from']).".json"));
	$alldata = json_decode(file_get_contents('http://127.0.0.1/mysl/api.php?username='.$indata->username.'&password='.$indata->password));
	if(isset($alldata->error))
		{
		print $alldata->error;
		}
	else
		{
		print "Dina kort:";
		foreach($alldata->cards as $card)
			{
			print "\n".$card->travel_card->name." ";
			print $card->travel_card->detail->purse_value."Kr";
			if(isset($card->travel_card->detail->products))
				{
				foreach($card->travel_card->detail->products as $product)
					{
					if($product->active)
						{
						print " Period:".$product->end_date;
						}
					}
				}
			}
		}
	}
else
	{	
	print "Du har inget konto registrerat. Registrera kontot genom att smsa: login:{Användarnamn},{lösenord}\nex: login:anders,abc123";
	}
?>