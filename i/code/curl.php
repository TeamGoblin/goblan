<?php
/* cURL call */
function CurlMePost($url,$headers,$post){ 
	// $post is a URL encoded string of variable-value pairs separated by &
	$curl = curl_init();
	curl_setopt ($curl, CURLOPT_URL, $url);
	curl_setopt ($curl, CURLOPT_POST, 1);
	curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($curl, CURLOPT_POSTFIELDS, $post); 
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 3); // 3 seconds to connect
	curl_setopt ($curl, CURLOPT_TIMEOUT, 10); // 10 seconds to complete
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}