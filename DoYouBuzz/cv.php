<!doctype html>

<meta charset="UTF-8">
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">

<body>

<?php
session_start();

require 'conf.php';
require 'oauth.php';

$OAUTH = new DoYouBuzz\Oauth($site_url);
$OAUTH->set_site("http://www.doyoubuzz.com/fr/", $key, $secret);

if(isset($_GET['id'])) {
	$a = $OAUTH->request('http://api.doyoubuzz.com/cv/'.$_GET['id'], array('format' => 'json'), $_SESSION['access_token'], $_SESSION['token_access_secret']);	
	$response = json_decode($a, true);


	echo '<h2>Yeah, here is your resume "'.$response['resume']['title'].'!"</h2>';
	echo '<pre style="color:#333;font-size:10px;padding:10px;border:1px solid #999;background:#eee">';	 		
	print_r($response);
	echo '</pre>';

}
else {
	echo '<p>Sorry we need an id</p>';
}


?>

</html>
</body>