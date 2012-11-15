# DoYouBuzz API connection examples

Here are two PHP examples to get informations from DoYouBuzz using either an oAuth connection or a Partner connection (for more information see the [Documentation](http://doc.doyoubuzz.com)).

## Requirements

You must :

* have a DoYouBuzz API Key and API Secret 
* have a local webserver with cURL installed
* know if you have a Partner or Application access
* know the link to the official [DoYouBuzz API Documentation](http://doc.doyoubuzz.com)


## Application

* Set your ApiKey, ApiSecret and site url in the config.php file
* Launch app.php in your webserver (cURL extension must be installed). 
* You will then be redirected on DoYouBuzz.com on the authorization screen. 
* If you authorize the application to access your information, you will be redirected on your website with an extract of your DoYouBuzz datas

## Partner (deprecated)

Please note this API is deprecated and will be removed soon. If you rely on this API please contact us.

* Set your ApiKey and ApiSecret in the partner.php file
* Change the userId parameter in the $url string (this user must has joined you private CV database so you can get his informations)
* Launch partner.php in your webserver (cURL extension must be installed)