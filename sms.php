<?php
header('Content-Type: text/html; charset=utf-8');

// Check that the source of the request knows the secret:
if($_GET['key'] != '{Your secret key goes here}')
	{
	die();
	}
	
// Check if user is setting the user and password for that number:
if(substr(trim(strtolower($_POST['message'])),0,6) == 'login:')
	{
	$delar = preg_split('/,/', substr($_POST['message'],6));
	$utdata = new stdClass();
	$utdata->username = $delar[0];
	$utdata->password = simple_encrypt($delar[1],$_POST['from']);						// Encrupt the password with the phone number not safe but the password is at least not in clear text.
	file_put_contents('accounts/'.md5($_POST['from']).".json", json_encode($utdata));	// Store data to json file with the md5sum of the phone number to make the service more private.
	}

// Check if the phonumber has a stored file id so request the card info:
if(is_file('accounts/'.md5($_POST['from']).'.json'))
	{
	// Load the data
	$indata = json_decode(file_get_contents('accounts/'.md5($_POST['from']).".json"));
	// Get the data
	$alldata = json_decode(file_get_contents('http://127.0.0.1/mysl/api.php?username='.$indata->username.'&password='.simple_decrypt($indata->password,$_POST['from'])));
	//If there is an error print error message from API.
	if(isset($alldata->error))
		{
		print $alldata->error;
		}
	else //If all ok print card info;
		{
		print "Dina kort:";
		foreach($alldata->data->UserTravelCards as $card)
			{
			print "\n".$card->Name." ".$card->PurseValue."Kr";
			}
		}
	}
else // If not logindetails are stored return help information;
	{	
	print "Du har inget konto registrerat. Registrera kontot genom att smsa: login:{Användarnamn},{lösenord}\nex: login:anders,abc123";
	}

// Wrappers for the mcrypt:
function simple_encrypt($text,$key)
    {
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

function simple_decrypt($text,$key)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }
?>
