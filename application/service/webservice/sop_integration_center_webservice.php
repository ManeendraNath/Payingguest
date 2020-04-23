<?php
//header('Content-Type: application/json');
error_reporting(E_ALL);
session_start();
require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
require_once(realpath(__DIR__.'/../common_functions.php'));
require_once(realpath(__DIR__.'/../insertwebservicedatanewformat.php'));
$webservice_id = isset($_GET[id]) > 0 ? (int)$_GET[id] : 0;
$result = sop_integration_center_webservice($webservice_id);
$data=json_encode($result);
echo $data;
function sop_integration_center_webservice($webservice_id)
{
	global $con;
	$request_ip = $_SERVER['REMOTE_ADDR'];
	$userid=isset($_SESSION["user_session"]["user_id"]) ? $_SESSION["user_session"]["user_id"] : 0;
	$userRole=isset($_SESSION["user_session"]["user_role"]) ? $_SESSION["user_session"]["user_role"] : 0;
	
	if(empty($request_ip) || $userid==0 || !in_array($userRole,array(1,3)) || $webservice_id==0) {
		$data[0] = 1;
		$data[1] = "You are not in correct page. Please contact administrator.";
	}
	else
	{
		$webservice_execution_mode = 'ManualExecution';
		$statecode = '00';
		$sql=mysqli_query($con, "SELECT w.*, s.scheme_code FROM dbt_webservice_details as w join dbt_scheme_master as s on s.scheme_id = w.scheme_id WHERE w.webservice_id = $webservice_id");
		if(mysqli_num_rows($sql)<1)
		{
			$data[0] = 1;
			$data[1] = "Web service not found.";
		}
		else
		{
			$webservice_url_info = mysqli_fetch_object($sql);
			if($webservice_url_info->api_version=="o")	require_once(realpath(__DIR__."/center_webservice_functions_old.php"));
			if($webservice_url_info->api_version=="n")	require_once(realpath(__DIR__."/center_webservice_functions_new.php"));
			$request_date_format = $webservice_url_info->request_date_format;
			$webservice_data_type = $webservice_url_info->webservice_data_type_id;
			$webservice_frequency_id = $webservice_url_info->webservice_frequency_id;
			$execution_day = $webservice_url_info->execution_day;
			if($execution_day==0)	$execution_day=1;
			$scheme_id=$webservice_url_info->scheme_id;
			if(date("m")==4 || (date("m")==5 && date("d")<$execution_day))
			{
				$data[0] = 1;
				$data[1] = "Web service will run on scheduled date.";
			}
			else
			{
				$startdate="";
				$fy_year=explode("_",financialYear(date("m"), date("Y")));
				$sql=mysqli_query($con, "SELECT reporting_month, scheme_transaction_from_date FROM dbt_scheme_beneficiary_data WHERE scheme_id = $scheme_id");
				if(mysqli_num_rows($sql)<1)
				{
				//	If data is not available in main table, we will check into log table
					$sql=mysqli_query($con, "SELECT reporting_month, scheme_transaction_from_date FROM dbt_scheme_beneficiary_data_log1 WHERE scheme_id = $scheme_id");
					if(mysqli_num_rows($sql)<1)
					{
					//	If data is not available in log table we will take data from 1st April.
						$startdate=$fy_year[0]."-4-".$execution_day;
					}
					else	$query=mysqli_fetch_array($sql);
				}
				else	$query=mysqli_fetch_array($sql);
				if($startdate=="")
				{
					if($webservice_frequency_id==1)
					{
					//	Daily webservice
						$startdate=date("Y-m-d", strtotime("$query[1] +1 day"));
					}
					else
					{
					//	Monthly webservice
						$getYear=explode("-", $query[1]);	// get from beneficiary table
						$startdate=$getYear[0]."-".($query[0]+1)."-".$execution_day;
					}
				}
				$enddate=date("Y-m-d");
				$nodal_person_email	= $webservice_url_info->nodal_person_email;
				$officer_email		= $webservice_url_info->officer_email;
				$link_officer_email	= $webservice_url_info->link_officer_email;
				$nodalp_email		= array($nodal_person_email);
				$nodalp_email_to_log= implode(",",array($nodal_person_email));
				$cc_email			= array($officer_email, $link_officer_email);
				$scheme_code		= $webservice_url_info->scheme_code;
				if(intval($execution_day) < 10) {
					$execution_day = '0'.intval($execution_day);
				}
				$schemeinfo = get_scheme_info($scheme_code);
				$error_flag = 0;
				$i=0;
				if($webservice_frequency_id == 1)	$interval="day";
				else 								$interval="month";
				for($stdate=$startdate;strtotime($stdate)<strtotime($enddate);)
				{
					if($webservice_frequency_id == 1) {
						$execution_day =  date('d', strtotime($stdate));
						$execution_month = date('m', strtotime($stdate));
						$execution_year	= date('Y', strtotime($stdate));
						if(intval($execution_day) < 10){
							$execution_day = '0'.intval($execution_day);
						}
					}
					else
					{
						$execution_month = date('m', strtotime("$stdate +1 month"));
						$execution_year = date('Y', strtotime("$stdate +1 month"));
					}
					$error_code = 0;
					$requestdate = requestDate($request_date_format, $execution_day, $execution_month, $execution_year);
					$servicedate = $execution_year.'-'.$execution_month.'-'.$execution_day;
					
					if($webservice_frequency_id == 1){
						$dataday = date("d", strtotime($servicedate));
						$datamonth = date("m",strtotime($servicedate));
						$datayear = date("Y",strtotime($servicedate));
						$rqDate = dateFormationYMD($requestdate,'dd/mm/yyyy');
					} else {
						$dataday = "00";
						$datamonth = date("m", strtotime($servicedate));
						$datayear = date("Y", strtotime($servicedate));
						$rqDate = dateFormationYMD($requestdate,$request_date_format);
					}
					if($webservice_url_info->parameter_value_encode == 'y') {
						$scheme_code = base64_encode($scheme_code);
						$statecode = base64_encode($statecode);
						$requestdate = base64_encode($requestdate);
					}
					$financial_year = financialYear($datamonth, $datayear);	//	financial_year
					$webservice_execution_log_id = createLogEntryBeforeAutoExecution($scheme_id, $dataday, $datamonth, $financial_year,$webservice_execution_mode, $rqDate, $request_ip);
					// create log file
					$filename = $webservice_url_info->scheme_code."_".time()."_".date("d-m-Y", strtotime($servicedate)).".txt";
					$rootpath = $rootpath = realpath(__DIR__.'/../../../')."/data/logs/schemeservices/";
					$myfile = fopen($rootpath.$filename, "w") or die("Unable to open file!");
					chmod($rootpath.$filename, 0777);
					
					$errormsg = '';
					$scheme_id = '';
					$is_updated = 'no';
					$is_success = 'no';
					$txt = '';
					$txt .= "Execution Time: ".date("d/m/Y h:i:sa")."\n\n";
					$txt .= "Requested Date: ".$servicedate."\n\n";
					if($schemeinfo[0]==0)	//	scheme validation has no error
					{
						$scheme_id = $schemeinfo[1]->scheme_id;
						$get_ws_data = get_plain_ws_data($webservice_url_info,$scheme_code,$statecode,$requestdate,$txt);
						$txt.=$get_ws_data[1];
						$error_code=$get_ws_data[3];
						if($error_code==0) {
							$responseData = $get_ws_data[0];
							$returnvalue = insert_scheme_data($responseData, $scheme_id, $servicedate, $is_updated, $txt, $nodalp_email, $cc_email, $dataday, $datamonth, $datayear, $scheme_code,$webservice_data_type, $userid);
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
						} else {
							$errormsg = $get_ws_data[2];
							$error_code = $get_ws_data[3];
						}
					} else {
						$txt .= $errormsg = $schemeinfo[1];	//	scheme code error message
						$error_code = 7;
					}
					// write log to file
					fwrite($myfile, $txt);
					fclose($myfile);
					
					//send mail functions
					$subject = $scheme_code.' | Web-service Update!';
					$cc = implode(',', $nodalp_email);
					$sender_name = 'DBT Bharat';
					$message = 'Please find attached response file of webservice.';
					$sentmail = send_mail('feedback@dbtbharat.gov.in', $cc, $sender_name, $subject, $message, $rootpath, $filename);
					
					//	log function
					$remarks = textValidation($errormsg);
					
					$log_id = webserviceauditlogfunction($scheme_id, $dataday, $datamonth, $financial_year, $is_updated, $is_success, $remarks, $webservice_execution_mode, $servicedate, $request_ip, $error_code, $i, $webservice_execution_log_id,$error_msg_arr);
					if($responseData<>"")	$temp_data = insert_data_in_temp_table($responseData,$scheme_id,$userid,$request_ip,$webservice_execution_log_id);
					if($error_code != 0) {
						$error_flag++;
						$update_data = inactive_webservice_status($webservice_url_info->webservice_id);
						$data[1] = "Failed";
						break;
					}
					$responseData = $scheme_id = $servicedate = $txt = $datamonth = $remarks = $webservice_execution_log_id = '';
					$stdate = date("Y-m-d", strtotime("$stdate +1 $interval"));
					$i++;
				}	//	loop end

				if($error_flag==0){
				//	update status of dbt_webservice_details
					$sql_update = mysqli_query($con, "update dbt_webservice_details set webservice_status = 1 where scheme_id = $scheme_id");
					$data[1] = "Success";
				}
				$data[0] = $error_flag;
			}
		}
	}
	return $data;
}
?>