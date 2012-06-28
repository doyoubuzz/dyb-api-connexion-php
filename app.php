<?php

/*
 * TEST FILE FOR DOYOUBOUZZ API CONNECTION using an Application connection (oAuth)
 * Just configure the informations below :)
 */

$key = '';
$secret = '';
$format = 'xml';
$site_url = ''; // Your site url (example : http://sandbox.local/dyb/)
$callback_url = 'test.php'; // Your relative callback URL


session_start();

require 'oauth.php';
$OAUTH = new Oauth($site_url);


if (isset($_GET['oauth_token'])) {	
	
	$OAUTH->set_site("http://www.doyoubuzz.com/fr/", $key, $secret);
	$OAUTH->set_callback($callback_url);
	
	if(!isset($_SESSION['access_token'])) {		
		$token = $OAUTH->get_access_token($_GET['oauth_token'], $_GET['oauth_verifier'], $_SESSION['token_secret']);
		$_SESSION['access_token'] = $token['access_token'];
		$_SESSION['token_access_secret'] = $token['token_secret'];
	}

	$a = $OAUTH->request('http://api.doyoubuzz.com/user', array(), $_SESSION['access_token'], $_SESSION['token_access_secret']);
	
	echo '
	<h1>Congrats!</h1>
	<h2>You just retrieved your own DoYouBuzz informations :</h2>';
	echo '<pre>';	 		
	print_r($a);
	echo '</pre>';
} 


else {	
	session_unset();
	$OAUTH->set_site("http://www.doyoubuzz.com/fr/", $key, $secret);
	$OAUTH->set_callback($callback_url);
	
	$OAUTH->get_request_token()
		  ->get_user_authorization();

}


