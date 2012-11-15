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
$a = $OAUTH->request('http://api.doyoubuzz.com/user', array('format' => 'json'), $_SESSION['access_token'], $_SESSION['token_access_secret']);	
$response = json_decode($a, true);

echo '<h2>Yeah '.$response['user']['firstname'].', here are your user informations !</h2>';
echo '<pre style="color:#333;font-size:10px;padding:10px;border:1px solid #999;background:#eee">';	 		
print_r($response);
echo '</pre>';

?>



</html>
</body>