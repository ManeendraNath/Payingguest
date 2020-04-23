<?php
//	get_plain_ws_data
function get_plain_ws_data($url_details,$scheme_code,$statecode,$requestdate,$txt){
	
	$responseData = '';
	$errormsg = '';
	// get parameters details
	$parameters_details = get_parameters_details($url_details->webservice_id);
	
	for($i=0;$i<count($parameters_details);$i++)
	{
		$parameter[$i] = array($parameters_details[$i]->webservice_parameter_index,$parameters_details[$i]->webservice_parameter_key,$parameters_details[$i]->webservice_parameter_value);
	}
	
	if ($url_details->webservice_type_id == 2){ //soap
	// soap service
		$recquest_obj = $url_details->request_object_name;
		$response_obj = $url_details->response_object_name;
		$any_obj = $url_details->any_object_name;
	
		try {
			if($url_details->use_soap_option == 'yes'){
				$options = array(
					'soap_version'=>SOAP_1_1,
					'exceptions'=>true,
					'trace'=>1,
					'cache_wsdl'=>WSDL_CACHE_NONE
				);
				$client = new SoapClient($url_details->webservice_url,$options);
			} else {
				$client = new SoapClient($url_details->webservice_url);
			}
			
			$arr="";
			if ($url_details->use_key_name == 'n') {
				foreach($parameter AS $key=>$value)
				{
					if($value[0]==1)	$arr[]=$scheme_code;
					if($value[0]==2)	$arr[]=$statecode;
					if($value[0]==3)	$arr[]=$requestdate;
					if($value[0]==4)	$arr[]=$value[2];
					if($value[0]==5)	$arr[]=$value[2];
				}
				if (isset($arr[3])) {
					$sopResponse = $client->$recquest_obj($arr[0],$arr[1],$arr[2],$arr[3]);
				} else if (isset($arr[4])) {
					$sopResponse = $client->$recquest_obj($arr[0],$arr[1],$arr[2],$arr[3],$arr[4]);
				} else {
					$sopResponse = $client->$recquest_obj($arr[0],$arr[1],$arr[2]);
				}
				
			} else {
				foreach($parameter AS $key=>$value)
				{						
					if($value[0]==1)	$arr[$value[1]]=$scheme_code;
					if($value[0]==2)	$arr[$value[1]]=$statecode;
					if($value[0]==3)	$arr[$value[1]]=$requestdate;
					if($value[0]==4)	$arr[$value[1]]=$value[2];
					if($value[0]==5)	$arr[$value[1]]=$value[2];
				}
				$sopResponse = $client->$recquest_obj($arr);
			}
			
			$xmlflag = 1;
			try{
				if ($response_obj && $any_obj) {
					$xml = new SimpleXMLElement($sopResponse->$response_obj->$any_obj);
					
				} else if ($response_obj) {
					$xml = new SimpleXMLElement($sopResponse->$response_obj);
				} else {
					$xml = new SimpleXMLElement($sopResponse);
				}
				
			} catch(Exception $e){
				$txt .= $e->getMessage();
				$errormsg = addslashes($e->getMessage());
				$xmlflag = 0;
				$error_code = 4;
			}
			if($xmlflag == 1 && $xml){
				$body = $xml->xpath('//scheme-details');
				
				$getValue = json_decode(json_encode((array)$body), TRUE);
				if(count($getValue) > 0){
					if(isset($getValue[0]['error'])) { //common error message in webservice 
						if($getValue[0]['error']=="")	$txt .= $errormsg = "Error tag has no message.";
						else							$txt .= $errormsg = $getValue[0]['error'];
						$error_code = 2;
					}elseif(!array_key_exists("location",$getValue[0])){
						$txt .= $errormsg = 'Response XML format is not as required';
						$error_code = 2;
					}
					else 
					{
						$responseData = $getValue;
					}							
				} else{
					$txt .= $errormsg = "Something Went Wrong.";
					$error_code = 2;
				}
			} else {
				$txt .= $errormsg = "Did not get any response from server!";
				$error_code = 2;
			}
			
		} catch(SoapFault $s){
			$txt .= $errormsg = $s->getMessage();
			$error_code = 1;
		}
		
		$errormsg=isset($errormsg) ? $errormsg : "";
		$error_code=isset($error_code) ? $error_code : 0;
		return $return_data = array($responseData,$txt,$errormsg, $error_code);
	} 
	else if ($url_details->webservice_type_id == 1){
		// rest service
	
			$url = $url_details->webservice_url;
			if(strpos($url, 'dbtdos.isro.gov.in') !== false)	$useragent="Mozillas/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36";
			else	$useragent="";
			$request = '';
			$header = '';
			$tokenurl = isset($url_details->token_url) ? $url_details->token_url : "";
			$tokenplace = isset($url_details->token_place) ? $url_details->token_place : "";
			$appkey = isset($url_details->token_key) ? $url_details->token_key : "";
			
			if(!empty($tokenurl))
			{
				foreach($parameter AS $key=>$value)
				{
					if($value[0]==4)	$username=$value[2];
					if($value[0]==5)	$password=$value[2];
				}
				$tokenkey=dynamic_token($tokenurl, $username, $password);
			}
			$request_type=$url_details->rest_request_type;
			
		if ($url_details->rest_service_request_format == "inurl")
		{
			if ($url_details->delimiter_value=="1")
			{
				$delimiter=$urlappend="/";
			}
			else
			{
				$delimiter="&";
				$urlappend="?";
			}
			$url.=$urlappend;
			if ($url_details->use_key_name=="y")
			{
				foreach($parameter AS $key=>$value)
				{						
					if($value[0]==1)	$url.=$value[1].'='.$scheme_code.$delimiter;
					if($value[0]==2)	$url.=$value[1].'='.$statecode.$delimiter;
					if($value[0]==3)	$url.=$value[1].'='.$requestdate.$delimiter;
					if($value[0]>3)
					{
						if($tokenplace==1 && $url_details->parameter_base_type=="token" && $value[0]==4 && !empty($value[1]) && !empty($value[2]))
						{
							$url.=$value[1].'='.$value[2].$delimiter;
						}
						elseif($url_details->parameter_base_type<>"token" && $value[0]==4 && !empty($value[1]) && !empty($value[2]))
						{
							$url.=$value[1].'='.$value[2].$delimiter;
						}
						
						if($value[0]==5 && !empty($value[1]) && !empty($value[2]) && $tokenplace != 2)
						{
							$url.=$value[1].'='.$value[2].$delimiter;
						}
					}
				}
			}
			else
			{
				foreach($parameter AS $key=>$value)
				{
					if($value[0]==1)	$url.=$scheme_code.$delimiter;
					if($value[0]==2)	$url.=$statecode.$delimiter;
					if($value[0]==3)	$url.=$requestdate.$delimiter;
					if($value[0]==4)	$url.=$value[2].$delimiter;
					if($value[0]==5)	$url.=$value[2].$delimiter;
				}
			}
			if(substr($url,-1) == $delimiter) $url = substr_replace($url ,"", -1);
			if(!empty($tokenurl) && !empty($tokenkey) && $tokenplace == 2) {
				
				if(!empty($appkey)){
					$header = array("AppKey: $appkey","Authorization: Bearer " . $tokenkey);
				} else {
					$header = array("Authorization: Bearer " . $tokenkey);
				}
			}
			
			
			if(strpos($url, 'onlinesales.licindia.in') !== false || strpos($url, '115.111.143.40') !== false || strpos($url, '10.248.107.200') !== false) {
				foreach($parameter AS $key=>$value)
				{
					if($value[0]==4)	$user=$value[2];
					if($value[0]==5)	$pass=$value[2];
				}
				$curlopt_userpwd = $user.':'.$pass;
			}
			
		}
		else if ($url_details->rest_service_request_format == "notinurl")
		{
			$delimiter="&";				
			foreach($parameter AS $key=>$value)
			{						
				if($value[0]==1)	$schcode=$value[1].'='.$scheme_code;
				if($value[0]==2)	$stcode=$value[1].'='.$statecode;
				if($value[0]==3)	$dat=$value[1].'='.$requestdate;
				if($value[0]==4)	$uname=$value[1].'='.$value[2];
				if($value[0]==5)	$psw=$value[1].'='.$value[2];
			}
			$request = $uname.$delimiter.$psw.$delimiter.$schcode.$delimiter.$dat.$delimiter.$stcode;
			$header = array(
			  // "Content-type: application/xml",
			  "Content-type: application/x-www-form-urlencoded",
			  "Accept: application/xml",
			  "Cache-Control: no-cache",
			  "Content-length: ".strlen($request),
			);
		}
		else if ($url_details->rest_service_request_format == "xml")
		{
			$xml_root_tag = isset($url_details->xml_root_tag) ? $url_details->xml_root_tag : "";
			$request  = xml_request($xml_root_tag, $parameter, $scheme_code, $statecode, $requestdate);
			$header = array(
			  "Content-type: application/xml",
			  "Accept: application/xml",
			  "Cache-Control: no-cache",
			  "Content-length: ".strlen($request),
			);
		}
		else if ($url_details->rest_service_request_format == "json")
		{
			$request  = json_request($parameter, $scheme_code, $statecode, $requestdate, $tokenplace);
			if ($tokenplace == 2 && strpos($url, '164.100.229.61') !== false) {
				$header = array(
					"authorization: $appkey",
					"cache-control: no-cache",
					"content-type: application/json"
				);

			} else {
				$header = array(
					"cache-control: no-cache",
					"content-type: application/json"
				);
			}
			
		}
		
		$curlopt_userpwd=isset($curlopt_userpwd) ? $curlopt_userpwd : "";
		
		$result = curl_request($url, $request_type, $request, $header, $useragent, $curlopt_userpwd);
			if($result[0] == 1 || $result[0] == 2)
			{
				$txt .= "Scheme Code : ".$scheme_code."\n";
				$txt .= $errormsg = 'error:' .$result[1];
				$responseData = $result[1];
				$error_code = $result[2];
			}
			else {
				  
				$xmlflag = 1;
				try{
					$xml = simplexml_load_string($result[1]);
				} catch(Exception $e){
					$txt .= $e->getMessage();
					$errormsg = addslashes($e->getMessage());
					$xmlflag = 0;
					$error_code = 4;
				}
				if($xmlflag == 1 && $xml){
					$body = $xml->xpath('//scheme-details');
					if($body) {
						$getValue = json_decode(json_encode((array)$body), TRUE);
						if(count($getValue) > 0){
							if(isset($getValue[0]['error'])) {
								if($getValue[0]['error']=="")	$txt .= $errormsg = "Error tag has no message.";
								else		$txt .= $errormsg = "Error Message : ".$getValue[0]['error']."\n";
								$error_code = 2;
							}
							elseif(!array_key_exists("location",$getValue[0])){
								$txt .= $errormsg = 'Response XML format is not as required';
								$error_code = 2;
							}
							else 
							{
								$responseData = $getValue;
							}
						} else {
							$txt .= $errormsg = "Data Not Found";
							$error_code = 2;
						}
					} else {
						$txt .= $errormsg = "XML response tags are not matched.";
						$error_code = 4;
					}
				} else {
					$txt .= $errormsg = "Did not get any response from server!";
					$error_code = 2;
				}
			}
		$error_code=isset($error_code) ? $error_code : 0;
		return $return_data = array($responseData,$txt,$errormsg,$error_code);
	}
} // get_plain_ws_data end
	
// curl_request
function curl_request ($url, $request_type, $data = null, $header = null, $useragent = null, $curlopt_userpwd = null) {
	$error=1;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);	                                                                 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_TIMEOUT,        0);
	curl_setopt($ch, CURLOPT_ENCODING,	   "");
	curl_setopt($ch, CURLOPT_MAXREDIRS,	   10);
	if ($data <> null) {
		curl_setopt($ch, CURLOPT_HTTP_VERSION,   CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
	}
	if($request_type==1)	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'POST');
	if ($header <> null) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	if ($useragent <> "") {
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	}
	if ($curlopt_userpwd <> "") {
		curl_setopt($ch, CURLOPT_USERPWD, $curlopt_userpwd);
	}
	$response = curl_exec($ch);
		
	if(curl_error($ch))
	{
		$response = curl_error($ch);
		echo $error_code = 1;
	}
	else
	{
		$response = curl_exec($ch);
		if(strpos($response, "error") !== FALSE || strpos($response, "Error") !== FALSE || strpos($response, "Message") !== FALSE)
		{
			$error=2;
			$error_code = 2;
		}
		else if((strpos($response, "scheme-master") !== FALSE) || (strpos($response, "scheme_master") !== FALSE))
		{
			$error=0;
		}
		else	
		{
			$response="Did not get any response from server or XML format is not as required!";
			$error_code = 2;
		}
	}
	
	curl_close($ch);
	$error_code=isset($error_code) ? $error_code : 0;
	$result_arr[0] = $error;
	$result_arr[1] = $response;
	$result_arr[2] = $error_code;
	return $result_arr;
	
} // curl_request end

function dynamic_token($url, $username, $password) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=password&username=$username&password=$password");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);

	$headers = array();
	$headers[] = "Content-Type: application/x-www-form-urlencoded";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	$json   = json_decode($result);
	$atoken = $json->access_token; 
	return $atoken;
} // dynamic_token end

function xml_request($xml_root_tag = 'xml', $parameter, $scheme_code, $statecode, $requestdate)
{
	$request  = '<'.$xml_root_tag.'>';
	foreach($parameter AS $key=>$value)
	{
		if($value[0] == 1) $request.='<'.$value[1].'>'.$scheme_code.'</'.$value[1].'>';
		if($value[0] == 2) $request.='<'.$value[1].'>'.$statecode.'</'.$value[1].'>';
		if($value[0] == 3) $request.='<'.$value[1].'>'.$requestdate.'</'.$value[1].'>';
		if($value[0] == 4) $request.='<'.$value[1].'>'.$value[2].'</'.$value[1].'>';
		if($value[0] == 5) $request.='<'.$value[1].'>'.$value[2].'</'.$value[1].'>';		
	}
	$request  .= '</'.$xml_root_tag.'>';
	return $request;
} // xml_request end

function json_request($parameter, $scheme_code, $statecode, $requestdate, $tokenplace = null)
{
	$request  = "{";
	foreach($parameter AS $key=>$value)
	{
		if($value[0] == 1) $request.="\r\n\"$value[1]\":\"$scheme_code\",";
		if($value[0] == 2) $request.="\r\n\"$value[1]\":\"$statecode\",";
		if($value[0] == 3) $request.="\r\n\"$value[1]\":\"$requestdate\",";
		if($value[0] == 4) $request.="\r\n\"$value[1]\":\"$value[2]\",";
		if($value[0] == 5) $request.="\r\n\"$value[1]\":\"$value[2]\",";
	}
	if(substr($request,-1) == ',') $request = substr_replace($request ,"\r\n}", -1);
	return $request;
} // json_request end