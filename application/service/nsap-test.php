<?php
//echo "fdfdfd"; die;
ini_set("error_reporting","E_ALL");
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

     $url='http://10.248.107.200:8080/nsapservices/dbt/stateCode/00/day-month-year/14-11-2019/dbtSchemeCode/B10AN';
	// echo $url; die;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 450);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 450);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "dbt:dbt@!321");
	$result = curl_exec($ch);
	// echo "<pre>"; print_r($result); die;
	if(curl_error($ch))
	{
		// echo 'error:' . curl_error($ch);
		$txt .= curl_error($ch);
		$errormsg = curl_error($ch);
		curl_close($ch);
	} else {
		curl_close($ch);
		print '<pre>';
		print_r($result);
		die;
		
	}
?>