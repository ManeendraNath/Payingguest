<?php
error_log(E_ALL);
//	get_plain_ws_data
function get_plain_ws_data($url_details, $scheme_code, $statecode, $requestdate, $requestToken){
	$txt='';
	$responseData = '';
	$errormsg = '';
	// get parameters details
	$parameters_details = get_parameters_details($url_details->webservice_id);

	for($i=0;$i<count($parameters_details);$i++)
	{
		$parameter[$i] = array($parameters_details[$i]->webservice_parameter_index,$parameters_details[$i]->webservice_parameter_key,$parameters_details[$i]->webservice_parameter_value);
	}
	if($url_details->webservice_type_id == 1)	//	rest service
	{
		$url = $url_details->webservice_url;
		$tokenurl = isset($url_details->token_url) ? $url_details->token_url : "";		
		foreach($parameter AS $key=>$value)
		{
			if($value[0]==4)	$username=$value[2];
			if($value[0]==5)	$password=$value[2];
		}
		$curlopt_userpwd = $username.':'.$password;
		if(!empty($tokenurl))	//	token is creating in run time (dynamic).
		{
			$tokenkey=dynamic_token($tokenurl, $curlopt_userpwd, $requestToken);	//	dynamic token creation
			$curlopt_userpwd = "";
		}
		else	$tokenkey="";
			
		$request_type=$url_details->rest_request_type;
		
	//	$request = json_request($parameter, $scheme_code, $statecode, $requestdate);
		$request = json_request($parameter, $scheme_code, $requestdate);
		if(!empty($tokenurl) && !empty($tokenkey) && $tokenplace == 2) {
			$header = array(
				"Authorization: Bearer " . $tokenkey,	//	This is dynamic token
				"cache-control: no-cache",
				"content-type: application/json"
			);
		}
		else
		{
			$header = array(
				"Authorization: Bearer " . $requestToken,	//	This is request token, if using only username and password
				"cache-control: no-cache",
				"content-type: application/json"
			);
		}
		$result = curl_request($url, $request, $header, $curlopt_userpwd);
		if($result[0] == 1 || $result[0] == 2)
		{
			$txt .= "Scheme Code : ".$scheme_code."\n";
			$txt .= $errormsg = 'error:' .$result[1];
			$data = $result[1];
			$error_code = $result[2];
		}
		else {
			if($data<>"") {
				$getValue = json_decode($data);
				if(count($getValue) > 0){
					if(isset($getValue[0]['error'])) {
						if($getValue[0]['error']=="")	$txt .= $errormsg = "Error tag has no message.";
						else				$txt .= $errormsg = "Error Message : ".$getValue[0]['error']."\n";
						$error_code = 2;
					}
					elseif(!array_key_exists("location", $getValue[0])){
						$txt .= $errormsg = 'Response JSON format is not as required';
						$error_code = 2;
					}
					else	$responseData = $getValue;
				} else {
					$txt .= $errormsg = "Data Not Found";
					$error_code = 2;
				}
			} else {
				$txt .= $errormsg = "Did not get any response from server!";
				$error_code = 2;
			}
		}
		$error_code=isset($error_code) ? $error_code : 0;
		return $return_data = array($responseData,$txt,$errormsg,$error_code);
	}
	else
	{
		$errormsg="Webservice is not in REST";
		$error_code=100;
		return $return_data = array($responseData,$txt,$errormsg,$error_code);
	}
} // get_plain_ws_data end
	
// curl_request
function curl_request($url, $data, $header, $curlopt_userpwd = null) 
{
	$error=1;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);	                                                                 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 0);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);		
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	if ($curlopt_userpwd <> "") {
		curl_setopt($ch, CURLOPT_USERPWD, $curlopt_userpwd);
	}
	if(curl_error($ch))
	{
		$response = curl_error($ch);
		echo $error_code = 1;
	}
	else
	{
		$response = curl_exec($ch);
	//	echo "<pre>";print_r($response);die;
		if(strpos($response, "error") !== FALSE || strpos($response, "Error") !== FALSE || strpos($response, "Message") !== FALSE)
		{
			$error=2;
			$error_code = 2;
		}
		else	$error=0;
	}
	curl_close($ch);
	$error_code=isset($error_code) ? $error_code : 0;
	$result_arr[0] = $error;
	$result_arr[1] = $response;
	$result_arr[2] = $error_code;
	return $result_arr;
	
} // curl_request end

function dynamic_token($url, $curlopt_userpwd, $requestToken) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "requestToken=$requestToken");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $curlopt_userpwd);
	curl_setopt($ch, CURLOPT_HTTPHEADER, "Content-Type: application/x-www-form-urlencoded");
	$result = curl_exec($ch);
	
	if(curl_errno($ch)) {
	//	echo 'Error:' . curl_error($ch);
		return 0;
	}
	else
	{
		curl_close ($ch);
		$json   = json_decode($result);
		$atoken = $json->access_token; 
		return $atoken;
	}
} // dynamic_token end

//	function json_request($parameter, $scheme_code, $statecode, $requestdate)
function json_request($parameter, $scheme_code, $requestdate)
{
	$request  = array();
	foreach($parameter AS $key=>$value)
	{
		if($value[0] == 1) $request[$value[1]] = $scheme_code;
	//	if($value[0] == 2) $request[$value[1]] = $statecode;
		if($value[0] == 3) $request[$value[1]] = $requestdate;
	//	if($value[0] == 4) $request[$value[1]] = $value[2];		//	username
	//	if($value[0] == 5) $request[$value[1]] = $value[2];		//	password
	}
	$request = json_encode($request);
	return $request;
} // json_request end