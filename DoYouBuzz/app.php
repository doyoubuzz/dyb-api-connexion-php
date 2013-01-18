<!doctype html>

<meta charset="UTF-8">
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">

<body>


<?php

/*
 * TEST FILE FOR DOYOUBOUZZ API CONNECTION using an Application connection (oAuth)
 * Just configure the informations in the config.php file
 */

session_start();

require 'conf.php';
require 'oauth.php';

$OAUTH = new DoYouBuzz\Oauth($site_url);

// If this is a callback
if (isset($_GET['oauth_token'])) {	
	
	$OAUTH->set_site("http://www.doyoubuzz.com/fr/", $key, $secret);
	$OAUTH->set_callback($callback_url);
	
	// Request the access token ...
	if(!isset($_SESSION['access_token'])) {		
		$token = $OAUTH->get_access_token($_GET['oauth_token'], $_GET['oauth_verifier'], $_SESSION['token_secret']);
		// ... and store the access token and token secret in session (or in your database) to access the user's datas
		$_SESSION['access_token'] = $token['access_token'];
		$_SESSION['token_access_secret'] = $token['token_secret'];
	}

	// Now that we have the access token, we can request datas

	$a = $OAUTH->request('http://api.doyoubuzz.com/user', array('format' => 'json'), $_SESSION['access_token'], $_SESSION['token_access_secret']);
	
	$response = json_decode($a, true);

	echo '
	<h1>Congrats '.$response['user']['firstname'].'!</h1>
	<h2>You just retrieved your own DoYouBuzz informations</h2>';

	echo '<a href="user.php">Go to your user page</a>';
	
	echo '<h3>Your resume(s)</h3>';
	echo '<ul>';
	foreach($response['user']['resumes']['resume'] as $resume) {
		echo '<li><a href="cv.php?id='.$resume["id"].'">'.$resume['title'].'</a></li>';
	}
	echo '</ul>';


	echo '<h2>Your own user information</h2>';
	echo '<pre style="color:#333;font-size:10px;padding:10px;border:1px solid #999;background:#eee">';	 		
	print_r($response);
	echo '</pre>';
} 


else {	
	session_unset();
	$OAUTH->set_site("http://www.doyoubuzz.com/fr/", $key, $secret);
	$OAUTH->set_callback($callback_url);
	
	$OAUTH->get_request_token()
		  ->get_user_authorization();

}

?>


</body>
</html>