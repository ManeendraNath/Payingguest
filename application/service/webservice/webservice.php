<?php
error_reporting(E_ALL);
session_start();
require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
require_once(realpath(__DIR__.'/../common_functions.php'));
require_once(realpath(__DIR__.'/../insertwebservicedatanewformat.php'));
$request_ip = $_SERVER['REMOTE_ADDR'];
$webservice_execution_mode = !empty($request_ip) ? 'ManualExecution' : 'AutoExecution';

$userid=isset($_SESSION["user_session"]["user_id"]) ? $_SESSION["user_session"]["user_id"] : 0;
$userRole=isset($_SESSION["user_session"]["user_role"]) ? $_SESSION["user_session"]["user_role"] : 0;
//	user role array (1,3) must be coming from database.
if(empty($request_ip) || in_array($userRole,array(1,3))){
	if(empty($request_ip))	$userid=1;
}
else	die("You are not authorized to access this page. Please contact administrator.");

$time_val = (date('A') == 'AM') ? 'morning' : 'evening';
$day = date('d');


$webservice_url_info = get_webservice_url_details($day,$api_owner=0,$time_val);
$error_master_code = get_error_master(); //update entry for new error code in table 'dbt_webservice_error_code_master'

foreach($webservice_url_info as $url_details) {
if($url_details->api_version=="o")	require_once(realpath(__DIR__."/center_webservice_functions_old.php"));
if($url_details->api_version=="n")	require_once(realpath(__DIR__."/center_webservice_functions_new.php"));
	$nodal_person_email	= $url_details->nodal_person_email;
	$officer_email		= $url_details->officer_email;
	$link_officer_email	= $url_details->link_officer_email;
	$nodalp_email		= array($nodal_person_email);
	$nodalp_email_to_log= implode(",",array($nodal_person_email));
	$cc_email			= array($officer_email,$link_officer_email);
	$webservice_data_type = $url_details->webservice_data_type_id;
	
	$execution_day = (intval($url_details->execution_day) < 10) ? '0'.intval($url_details->execution_day) : $url_details->execution_day;	
	$execution_month	= date('m');
	$execution_year		= date('Y');
	$servicedate = $execution_year.'-'.$execution_month.'-'.$execution_day;
	
	
//	create log file
	$filename = $url_details->scheme_code."_".time()."_".$servicedate.".txt";
	$rootpath = realpath(__DIR__.'/../../../')."/data/logs/schemeservices/";
	$myfile = fopen($rootpath.$filename, "w") or die("Unable to open file!");
	chmod($rootpath.$filename, 0777);
	$errormsg = '';
	$is_updated = 'no';
	$is_success = 'no';
	$txt = "Execution Time: ".date("d/m/Y h:i:sa")."\n\n";
	$txt .= "Requested Date: ".date("d/m/Y", strtotime($servicedate))."\n\n";
	if($url_details->scheme_code<>"")
	{
		$schemeinfo = get_scheme_info($url_details->scheme_code);
		if($schemeinfo[0]==0)
		{
			$schemeid = $schemeinfo[1]->scheme_id;
			$schemename = $schemeinfo[1]->scheme_name;
			$scheme_code = $url_details->scheme_code;
			$webservice_frequency_id = $url_details->webservice_frequency_id;
			$request_date_format=$url_details->request_date_format;
		//	preparing request date format
			$requestdate = requestDate($request_date_format, $execution_day, $execution_month, $execution_year);
			if($webservice_frequency_id == 1)	//	If webservice is providing data in daily basis
			{
				if($url_details->scheme_id == 674)	//	If webservice is of MNREGA (7 day back data)
				{
					$current_date = date('Y-m-d');
					$requestdate = date('d/m/Y', strtotime("$current_date -7 days"));
					$servicedate = date('Y-m-d', strtotime("$current_date -7 days"));
				}
				else
				{
					$requestdate = date('d/m/Y');
					$servicedate = date('Y-m-d');
				}
				$dataday = date("d", strtotime($servicedate));
				$datamonth = date("m",strtotime($servicedate));
				$datayear = date("Y",strtotime($servicedate));
				$rqDate = dateFormationYMD($requestdate,'dd/mm/yyyy');
			}
			else		//	If webservice is providing data in monthly basis
			{
				$dataday = "00";
				$datamonth = date("m", strtotime("$servicedate -1 month"));
				$datayear = date("Y", strtotime("$servicedate -1 month"));
				$rqDate = dateFormationYMD($requestdate,$request_date_format);
			}
			$financial_year = financialYear($datamonth, $datayear);	//	financial_year
			
			$webservice_execution_log_id = createLogEntryBeforeAutoExecution($schemeid, $dataday, $datamonth, $financial_year,$webservice_execution_mode, $rqDate, $request_ip);
			$checkPreviousMonthData = previousMonthDataAvalability($schemeid, "center", $dataday, $datamonth, $datayear,$webservice_frequency_id,$servicedate);

			if($checkPreviousMonthData[0]==1)
			{
				$execution_date = explode("-", $checkPreviousMonthData[1]);
				$error_code=6;
				$is_updated='no';
				$is_success='no';
				$servicedate=$checkPreviousMonthData[1];
				$txt .= $errormsg = $error_msg_arr = $error_master_code[$error_code-1]["webservice_error_code_details"];
				$responseData="";
			}
			else
			{
				$statecode = "00";
				if($url_details->parameter_value_encode == 'y') {
					$scheme_code = base64_encode($scheme_code);
					$statecode = base64_encode($statecode);
					$requestdate = base64_encode($requestdate);
				}
				
				$get_ws_data = get_plain_ws_data($url_details,$scheme_code,$statecode,$requestdate);
				
				$txt.=$get_ws_data[1];
				$error_code=$get_ws_data[3];
				
			 	if($error_code==0) {
					$responseData = $get_ws_data[0];
					$returnvalue = insert_scheme_data($responseData, $schemeid, $servicedate, $is_updated, $txt, $nodalp_email, $cc_email, $dataday, $datamonth, $datayear, $scheme_code,$webservice_data_type, $userid);
					$txt = $returnvalue[0];
					$is_updated = $returnvalue[2];
					if($is_updated == 'yes') {
						$is_success = 'yes';
						$validation_message = "";
						$error_message = "";
						$error_msg_arr = "";
					}
					else
					{
						$validation_message = $returnvalue[3];
						$error_message = $returnvalue[4];
						$error_msg_arr = $returnvalue[5];
						if(!empty($error_message)){
							$errormsg = $error_message;
						}
						if(!empty($validation_message)){
							$errormsg = $validation_message;
							$error_code = 3;
						}
					}
				}
				else {
					$errormsg = $get_ws_data[2];
					$error_code = $get_ws_data[3];
				}
			}
		}
		else	
		{
			$txt .= $errormsg = $schemeinfo[1];	//	scheme code error message
			$error_code = 7;
		}
	}
	else	
	{
		$txt .= $errormsg = 'Scheme not found or deactivated';
		$error_code = 7;
	}
	// write log to file
	fwrite($myfile, $txt);
	fclose($myfile);
	//send mail functions
	$subject        = $scheme_code.' | Web-service Update!';
	$cc = implode(',', $nodalp_email);
	$sender_name    = 'DBT Bharat';
	$message = 'Please find attached response file of webservice.';
	$sentmail = send_mail('feedback@dbtbharat.gov.in', $cc, $sender_name, $subject, $message, $rootpath, $filename);
	
	$remarks = textValidation($errormsg);
	
	$log_id = webserviceauditlogfunction($schemeid, $dataday, $datamonth, $financial_year, $is_updated, $is_success, $remarks, $webservice_execution_mode, $servicedate, $request_ip, $error_code, $rowCount=null, $webservice_execution_log_id, $error_msg_arr);
	
	if($error_code != 0) {
		$update_data = inactive_webservice_status($url_details->webservice_id);
	}
	if($responseData<>"")	$temp_data = insert_data_in_temp_table($responseData,$schemeid,$userid,$request_ip,$webservice_execution_log_id);
	
	$getValue = $responseData = $schemeid = $servicedate = $is_updated = $txt = $nodalp_email = $cc_email = $datamonth = $scheme_code = $remarks = '';
}
?>