<?php
/**
 * oAuth Consumer Library
 *
 * Requires the PHP cURL extension and the MY_Input hack (for CI apps),
 * which is included in Flame
 *
 * @package Flame
 * @subpackage oAuth
 * @copyright 2009, Jamie Rumbelow
 * @author Jamie Rumbelow <http://www.jamierumbelow.net>
 * @license GPLv3
 * @version 1.1.0
 */

namespace DoYouBuzz;

class Oauth {
	public $site = "";
	public $request_token_path = "oauth/requestToken";
	public $access_token_path = "oauth/accessToken";
	public $authorize_path = "oauth/authorize";

	public $shared_key = '';
	public $shared_secret = '';
	public $signature_method = "HMAC-SHA1";

	public $token_secret = '';

	private $request_token = '';
	private $access_token = '';

	public $base_url = "";
	public $callback = "";

	private $curl;
	private $ci;

	public function __construct($base_url = FALSE) {
		$this->curl = curl_init();

		if ($base_url !== FALSE) {
			$this->base_url = $base_url;
		}

		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
	}

	public function set_site($site, $key, $secret) {
		$this->site = $site;
		$this->shared_key = $key;
		$this->shared_secret = $secret;
	}
	
	public function set_callback($callback) {
		$this->callback = $callback;
	}

	public function get_request_token() {
		$prot = array(
			"oauth_consumer_key"       => $this->shared_key,
			"oauth_signature_method"   => $this->signature_method,
			"oauth_timestamp"          => time(),
			"oauth_nonce"              => sha1(time().rand(0,1000000)),
			"oauth_callback"           => urlencode($this->base_url.$this->callback)
		);	

		$url = $this->site . $this->request_token_path;
				
		$prot['oauth_signature'] = $this->sign_request("GET", $url, $prot, '');

		$request = $this->http_get($url, $prot);

		$request = explode('&', $request);
		$final = array();
		
		foreach ($request as $item) {
			$a = explode('=', $item);
			$final[$a[0]] = $a[1];
		}
		
		$this->request_token = $final['oauth_token'];
		$_SESSION['token'] = $this->request_token;
		 
		$this->token_secret = $final['oauth_token_secret'];
		$_SESSION['token_secret'] = $this->token_secret;
				
		return $this;
	}

	public function get_user_authorization() {
		$url = $this->site . $this->authorize_path;
		$token = $this->request_token;

		$url .= "?oauth_token=".$token."&oauth_callback=".urlencode($this->base_url.$this->callback);

		header("Location: ".$url);
	}

	public function get_access_token($request_token, $oauth_verifier, $token_secret) {
				
		$prot = array(
			"oauth_verifier"           => $oauth_verifier,
			"oauth_consumer_key"       => $this->shared_key,
			"oauth_signature_method"   => $this->signature_method,
			"oauth_nonce"              => sha1(time().rand(0,1000000)),
			"oauth_timestamp"          => time(),
			"oauth_version"            => "1.0",
			"oauth_token"              => $request_token,
		);

		$url = $this->site . $this->access_token_path;
			
		$prot['oauth_signature'] = $this->sign_request("GET", $url, $prot, $token_secret);
		
		$request = $this->http_get($url, $prot, $prot);
		
		$request = explode('&', $request);
		$final = array();
		
		foreach ($request as $item) {
			$a = explode('=', $item);
			$final[$a[0]] = $a[1];
		}

		$this->access_token = $final['oauth_token'];
		$this->token_secret = $final['oauth_token_secret'];
		
		$result = array(
			'access_token' => $this->access_token,
			'token_secret' => $this->token_secret
		);
		return $result;
	}

	public function request($url, $params, $oauth_token, $token_secret, $method = "GET") {
		$prot = array(
			"oauth_consumer_key"       => $this->shared_key,
			"oauth_token"              => $oauth_token,//$this->access_token,
			"oauth_signature_method"   => $this->signature_method,
			"oauth_timestamp"          => time(),
			"oauth_nonce"              => sha1(time().rand(0,1000000)),
		);

		$params = array_merge($params, $prot);
		$params['oauth_signature'] = $this->sign_request($method, $url, $params, $token_secret);

		$method = "http_".$method;

		$request = $this->$method($url, $params);
		return $request;
	}

	private function sign_request($method, $url, $params, $token_secret) {
		//Method is fine, so straight onto URL
		$url = rawurlencode($url);

		//Handle the request parameters
		uksort($params, 'strcmp');

		// Generate key=value pairs
		$pairs = array();
		
		foreach ($params as $key=>$value ) {
			if (is_array($value)) {
				// If the value is an array, it's because there are multiple 
				// with the same key, sort them, then add all the pairs
				natsort($value);
				foreach ($value as $v2) {
					$pairs[] = $key . '=' . $v2;
				}
			} else {
				$pairs[] = $key . '=' . $value;
			}
		}

		$params = implode("&", $pairs);
		$params = rawurlencode($params);
		
		//Concat them all
		$base_string = $method . "&" . $url . "&" . $params;
		
		// Make the key
		$key = $this->shared_secret . "&" . $token_secret;//$this->token_secret;
		

		switch ($this->signature_method) {
			case 'HMAC-SHA1':
				$str = hash_hmac('sha1', $base_string, $key, TRUE);
				$str = base64_encode($str);
				return rawurlencode($str);
				break;
			case 'PLAINTEXT':
				return rawurlencode($key);
				break;
		}
	}

	private function return_params($prot)
	{				
		// Generate key=value pairs
		$pairs = array();		
		
		uksort($prot, 'strcmp');
		
		foreach ($prot as $key=>$value ) {
			if (is_array($value)) {
				// If the value is an array, it's because there are multiple
				// with the same key, sort them, then add all the pairs
				natsort($value);
				foreach ($value as $v2) {
					$pairs[] = $key . '=' . $v2;
				}
			} else {
				$pairs[] = $key . '=' . $value;
			}
		}
		$params = implode("&", $pairs);
		
		return $params;
	}

	
	private function http_get($url, $prot) { 
		curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
		
		// Set the parameters in the url
		$url = $url.'?'.$this->return_params($prot);
				
		// Send the request			
		$request = $this->_request($url, $prot);
		return $request;
	}

	private function http_post($url, $prot, $params = array()) {
		curl_setopt($this->curl, CURLOPT_POST, TRUE);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);

		$request = $this->_request($url, $prot);
		return $request;
	}

	private function _request($url, $prot) {
		$headers[] = "Expect:";
		$headers[] = "Authorization: OAuth realm=\"\", ".$this->_build_protocol_string($prot);

		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);

		return curl_exec($this->curl);
	}

	private function _build_protocol_string($prot) {
		$array = array();

		foreach ($prot as $key => $value) {
			$array[] = "$key=$value";
		}

		return implode(", ", $array);
	}

}