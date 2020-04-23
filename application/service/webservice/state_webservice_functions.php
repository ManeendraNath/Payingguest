<?php

	// get_plain_ws_data
	function get_plain_ws_data($url_details,$requestdate,$txt){
		$responseData = '';
		$errormsg = '';
		if ($url_details->api_owner == 10){
			$scheme_code="0";
		} else {
			$scheme_code="00";
		}
		// get parameters details
		$parameters_details = get_parameters_details($url_details->webservice_id);
		for($i=0;$i<count($parameters_details);$i++)
		{
			$parameter[$i] = array($parameters_details[$i]->webservice_parameter_index,$parameters_details[$i]->webservice_parameter_key,$parameters_details[$i]->webservice_parameter_value);
		}
		$url = $url_details->webservice_url;
		$request_type=$url_details->rest_request_type;
		
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
					$client = new SoapClient($url,$options);
				} else {
					$client = new SoapClient($url);
				}
				
				if ($url_details->use_key_name == 'n') {
					$sopResponse = $client->$recquest_obj($scheme_code, $requestdate);
				} else {
					$arr = "";
					
					foreach($parameter AS $key=>$value)
					{						
						if($value[0]==1)	$arr[$value[1]]=$scheme_code;
						if($value[0]==3)	$arr[$value[1]]=$requestdate;
					}
					$sopResponse = $client->$recquest_obj($arr);
				}
				$xmlflag = 1;
				try{
					// echo "<pre>";print_r($sopResponse);die;
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
						if(isset($getValue[0]['error'])){
							$txt .= $errormsg = $getValue[0]['error'];
							$error_code = 4;
						}else{
							$responseData = $getValue;
						}
					} else{
						$txt .= $errormsg = "Something Went Wrong.";
						$error_code = 2;
					}
				} else {
					$txt .= $errormsg = "Did not get any response from server!";
					$error_code = 4;
				}
			} catch(SoapFault $s){
				$txt .= $errormsg = $s->getMessage();
				$error_code = 1;
			}
			$errormsg=isset($errormsg) ? $errormsg : "";
			
		}
		else if ($url_details->webservice_type_id == 1){ //rest
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
						if($value[0]==3)	$url.=$value[1].'='.$requestdate.$delimiter;
					}
				}
				else
				{
					foreach($parameter AS $key=>$value)
					{
						if($value[0]==1)	$url.=$scheme_code.$delimiter;
						if($value[0]==3)	$url.=$requestdate.$delimiter;
					}
				}
				if(substr($url,-1) == $delimiter) $url = substr_replace($url ,"", -1);
				$data="";			
			}
			else {
				foreach($parameter AS $key=>$value)
				{						
					if($value[0]==1)	$data[$value[1]]=$scheme_code;
					if($value[0]==3)	$data[$value[1]]=$requestdate;
				}
			}
			$rec=curl_request ($url, $request_type, $data);	
			$responseData=$rec[0];
			$errormsg=$rec[1];
			$error_code = $rec[2];
		}
		$error_code=isset($error_code) ? $error_code : 0;
		return $return_data = array($responseData,$txt,$errormsg,$error_code);
	} // get_plain_ws_data end

	// curl_request
	function curl_request ($url, $request_type, $data = null) {
		$error=1;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		if($request_type==1)
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'POST');
			if ($data <> null) {
				curl_setopt($ch, CURLOPT_HTTP_VERSION,   CURL_HTTP_VERSION_1_1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
			}
		}		
		if(curl_error($ch))
		{
			$response = curl_error($ch);
		}
		else
		{
			$response = curl_exec($ch);
			if(strpos($response, "error") !== FALSE || strpos($response, "Error") !== FALSE || strpos($response, "Message") !== FALSE)
			{
				$error=2;
				$error_code = 2;
			}
			elseif(strpos($response, "scheme-master") !== FALSE)
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
		if($error == 0 || $error ==2){
			$xml = new SimpleXMLElement($response);
			$body = $xml->xpath('//scheme-details');
			$response = json_decode(json_encode($body), TRUE);
		}
		
		$result_arr[0] = $response;
		$result_arr[1] = $error;
		$result_arr[2] = $error_code;
		return $result_arr;
		
	} // curl_request end