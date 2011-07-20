<?php
require_once('/home/white-cube/www/mixiapp/cake/app/webroot/lib/OAuth.php');


class RestfulapiComponent extends Object{
var $uses = array('Products','Masters','Dealers');
	public function get($VIEWER_ID,$PATTERN_CODE,$CONSUMER_KEY,$CONSUMER_SECRET)
	{
		$consumer = new OAuthConsumer($CONSUMER_KEY, $CONSUMER_SECRET, NULL);
		$user= $VIEWER_ID;

		/*
		 下記のような記述で情報を取得できる
		 /people/{guid}/@all
		 /people/{guid}/@friends
		 /people/{guid}/@self
		 /people/@me/@self
		 */
		if($PATTERN_CODE == '1'){
			$PATTERN_NAME = '@all';
		}else if ($PATTERN_CODE == '2'){
			$PATTERN_NAME = '@friends';
		}else if($PATTERN_CODE == '3'){
			$PATTERN_NAME = '@self';
		}


		$base_feed = 'http://api.mixi-platform.com/os/0.8/people/'.$VIEWER_ID.'/'.$PATTERN_NAME;

		$params = array(
			'xoauth_requestor_id' => $user,
			'filterBy'            => 'hasApp',
			'count'               => 100
		);

		$request = OAuthRequest::from_consumer_and_token($consumer, NULL, 'GET', $base_feed, $params);

		// Sign the constructed OAuth request using HMAC-SHA1
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

		// Make signed OAuth request to the Contacts API server
		$url = $base_feed . '?' . $this->implode_assoc('=', '&', $params);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

		//$auth_header = $request->to_header();
		$auth_header = $request->to_header('api.mixi-platform.com');
		if ($auth_header) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));
		}

		$response = curl_exec($curl);
		if (!$response) {
			$response = curl_error($curl);
		}
		curl_close($curl);

		return (array)json_decode($response);;
	}

	function implode_assoc($inner_glue, $outer_glue, $array, $skip_empty=false)
	{
		$output=array();
		foreach ($array as $key => $item) {
			if (!$skip_empty || $item) {
				$output[] = $key. $inner_glue. urlencode($item);
			}
		}
		return implode($outer_glue, $output);
	}
}


