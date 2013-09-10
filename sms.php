<?php
header('Content-Type: text/html; charset=utf-8');

if($_GET['key'] != '{Your secret key goes here}')
	{
	die();
	}
	
if(substr(trim(strtolower($_POST['message'])),0,6) == 'login:')
	{
	$delar = preg_split('/,/', substr($_POST['message'],6));
	$utdata = new stdClass();
	$utdata->username = $delar[0];
	$utdata->password = simple_encrypt($delar[1],$_POST['from']);
	file_put_contents('accounts/'.md5($_POST['from']).".json", json_encode($utdata)); 
	}

if(is_file('accounts/'.md5($_POST['from']).'.json'))
	{
	$indata = json_decode(file_get_contents('accounts/'.md5($_POST['from']).".json"));
	$alldata = json_decode(file_get_contents('http://127.0.0.1/mysl/api.php?username='.$indata->username.'&password='.simple_decrypt($indata->password,$_POST['from'])));
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


function simple_encrypt($text,$key)
    {
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

function simple_decrypt($text,$key)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }
?>
