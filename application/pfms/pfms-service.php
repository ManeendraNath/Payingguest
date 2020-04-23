<?php

ini_set("error_reporting","E_ALL");
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once(realpath(__DIR__.'/../configs/connection_mysqli_new.php'));

//Get Cash/Cash-In Kind Scheme Codes
$scheme_code_array = $get_schemes_list = '';
$get_schemes_list = mysqli_query($con,"SELECT `sch`.`scheme_code`, GROUP_CONCAT(distinct(schbt.scheme_benefit_type_id) ORDER BY schbt.scheme_benefit_type_id ASC SEPARATOR ',') AS `scheme_benefit_type_id` FROM `dbt_scheme_master` AS `sch` LEFT JOIN `dbt_scheme_benefit_type_relation` AS `schbr` ON sch.scheme_id = schbr.scheme_id LEFT JOIN `dbt_scheme_benefit_type` AS `schbt` ON schbr.scheme_benefit_type_id = schbt.scheme_benefit_type_id WHERE (schbr.benefit_type_relation_status = 1) AND (sch.state_code is NULL) AND (sch.scheme_onboarding_status = 'Y') AND sch.scheme_status = 1 GROUP BY `sch`.`scheme_id`" );
while ($row = mysqli_fetch_assoc($get_schemes_list)){
	//Consider Cash/Cash-In Kind Schemes only
	if($row['scheme_benefit_type_id'] === '1,2' || $row['scheme_benefit_type_id'] === '1'){
		$scheme_code_array[] = $row['scheme_code'];
	}
 }
$schemecode = $scheme_code_array;
$dt = DateTime::createFromFormat('!d/m/Y', date('d/m/Y'));
$requst_month = $dt->format('M-Y');


$i=0;
while($i < count($schemecode)) {

  $soap_request  = "<?xml version=\"1.0\"?>\n";
  $soap_request .= '<x:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:x="http://www.w3.org/2003/05/soap-envelope" xmlns:tem="http://tempuri.org/">
<x:Header>
	<a:Action x:mustUnderstand="1">http://tempuri.org/IDBTBharatPortalContract/GetTransactionSummary</a:Action>
	<a:MessageID>urn:uuid:2fae571f-7618-492e-93c7-752a14c20f20</a:MessageID>
	<a:ReplyTo>
		<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
	</a:ReplyTo>
	<a:To>https://pfms.nic.in/PFMSWebService/Service/DBTBharatPortalService.svc</a:To>
</x:Header>
<x:Body>
         <tem:GetTransactionSummary>
            <tem:oSoapEnvelop>
                <tem:Username>extsyssvc</tem:Username>
                <tem:Password>cpsms@321!</tem:Password>
                <tem:SchemeCode>'.$schemecode[$i].'</tem:SchemeCode>
                <tem:StateCode>00</tem:StateCode>
                <tem:TransactionDate>'.$requst_month.'</tem:TransactionDate>
            </tem:oSoapEnvelop>
        </tem:GetTransactionSummary>
</x:Body>
</x:Envelope>';
 
  $header = array(
    "Content-type: application/soap+xml;charset=\"utf-8\"",
    "Accept: application/soap+xml",
    "Cache-Control: no-cache",
    "Pragma: no-cache",
    "SOAPAction: \"run\"",
    "Content-length: ".strlen($soap_request),
  );
 
  $soap_do = curl_init();
  //curl_setopt($soap_do, CURLOPT_URL, "https://pfms.nic.in/PFMSWebService/Service/DBTBharatPortalService.svc" );
  curl_setopt($soap_do, CURLOPT_URL, "https://164.100.128.140:443/PFMSWebService/Service/DBTBharatPortalService.svc" );
  curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
  curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
  curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($soap_do, CURLOPT_POST,           true );
  curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
  curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
 
  if(curl_exec($soap_do) === false) {
    $err = 'Curl error: ' . curl_error($soap_do);
    curl_close($soap_do);
    print 'Error : '.$err;
  } else {
	  $aa = curl_error($soap_do);
	//  print_r($aa); die;
	  $response=curl_exec($soap_do);
	  // print '<pre>';
	  // print_r($response); die;
	  
	  
	  $clean_xml = str_ireplace(['s:','a:'],'',$response);
	  $xml =new SimpleXMLElement($clean_xml);
	  $body = $xml->xpath('//PFMSResponse');
	  $getvalue = json_decode(json_encode((array)$body), TRUE);
	  
	  if($getvalue[0]['scheme-master']['scheme-details']['location']) {
		  $pfmsdata = array();
		  $pfmsdata[0] = $getvalue[0]['scheme-master']['scheme-details'];
	  } else {
		  $pfmsdata = $getvalue[0]['scheme-master']['scheme-details'];
	  }

	$createddate = date('Y-m-d H:i:s');
	foreach($pfmsdata as $value) {
		// print '<pre>'; print_r($value); die;
		if($value['scheme-progress']['month'] == 'Apr' || $value['scheme-progress']['month'] == 'apr') {
			$monthval = '04';
		} else if($value['scheme-progress']['month'] == 'May' || $value['scheme-progress']['month'] == 'may'){
			$monthval = '05';
		} else if($value['scheme-progress']['month'] == 'Jun' || $value['scheme-progress']['month'] == 'jun'){
			$monthval = '06';
		} else if($value['scheme-progress']['month'] == 'Jul' || $value['scheme-progress']['month'] == 'jul'){
			$monthval = '07';
		} else if($value['scheme-progress']['month'] == 'Aug' || $value['scheme-progress']['month'] == 'aug'){
			$monthval = '08';
		} else if($value['scheme-progress']['month'] == 'Sept' || $value['scheme-progress']['month'] == 'sept' || $value['scheme-progress']['month'] == 'Sep' || $value['scheme-progress']['month'] == 'sep'){
			$monthval = '09';
		} else if($value['scheme-progress']['month'] == 'Oct' || $value['scheme-progress']['month'] == 'oct'){
			$monthval = '10';
		} else if($value['scheme-progress']['month'] == 'Nov' || $value['scheme-progress']['month'] == 'nov'){
			$monthval = '11';
		} else if($value['scheme-progress']['month'] == 'Dec' || $value['scheme-progress']['month'] == 'dec'){
			$monthval = '12';
		} else if($value['scheme-progress']['month'] == 'Jan' || $value['scheme-progress']['month'] == 'jan'){
			$monthval = '01';
		} else if($value['scheme-progress']['month'] == 'Feb' || $value['scheme-progress']['month'] == 'feb'){
			$monthval = '02';
		} else if($value['scheme-progress']['month'] == 'Mar' || $value['scheme-progress']['month'] == 'mar'){
			$monthval = '03';
		}
		$state_code = $value['location']['State_Code'];
		$state_name = $value['location']['State_Name'];
		
		$scheme_code = $value['general-information']['schemecode'];
		
		$total_no_of_beneficiaries = $value['beneficiary-details']['total_no_of_beneficiaries'];
		$no_of_aadhaarseeded_beneficiries = $value['beneficiary-details']['no_of_aadhaarseeded_beneficiries_transactions'];
		$no_of_beneficiaries_whom_mobile_no_captured = $value['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'];
		
		$total_no_transactions_electronic_modes = $value['transaction-details']['total_no_transactions_electronic_modes'];
		$payment_electronic_modes = $value['transaction-details']['payment_electronic_modes'];
		$total_no_transactions_other_modes = $value['transaction-details']['total_no_transactions_other_modes'];
		$payment_other_modes = $value['transaction-details']['payment_other_modes'];
		
		$remarks = $value['general-information']['remarks'];
		$month = $monthval;
		$year = $value['scheme-progress']['year'];
		$requestid = $value['scheme-progress']['requestid'];
		
		//$datainfo = mysqli_query($con,"SELECT count(*) as total_count FROM dbt_pfms_data where state_code = '".$state_code."' and scheme_code = '".$scheme_code."' and month = '".$month."' and year = '".$year."'" );
		//$datainfocount = mysqli_fetch_object($datainfo);
		
		if($datainfocount->total_count == 0){
		//	mysqli_query($con,"INSERT INTO dbt_pfms_data (state_code, state_name, scheme_code, total_no_of_beneficiaries, no_of_aadhaarseeded_beneficiries, no_of_beneficiaries_whom_mobile_no_captured, total_no_transactions_electronic_modes, payment_electronic_modes, total_no_transactions_other_modes, payment_other_modes, remarks, month, year, requestid, created) VALUES('$state_code', '$state_name', '$scheme_code', '$total_no_of_beneficiaries', '$no_of_aadhaarseeded_beneficiries', '$no_of_beneficiaries_whom_mobile_no_captured', '$total_no_transactions_electronic_modes', '$payment_electronic_modes', '$total_no_transactions_other_modes', '$payment_other_modes', '$remarks', '$month', '$year', '$requestid', '$createddate')");
		} else {
			echo 'Data already available for rerquested scheme, month and state </br>';
		}
	}
	  echo 'Service Executed. '.$schemecode[$i].'</br>';
	  // print '<pre>'; echo 'Response Snapshot: </br>'; print_r($pfmsdata); die;
	  
  }
  
$i++; 
}
?>