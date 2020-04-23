<?php
ini_set("error_reporting","E_ALL");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
	require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
	require_once(realpath(__DIR__.'/../insertwebservicedatanewformat-state.php'));
	require_once(realpath(__DIR__.'/../sendmail.php'));
	require_once(realpath(__DIR__.'/state_webservice_functions.php'));
	
	
$webservice_execution_log_id = isset($_GET["id"]) > 0 ? (int)$_GET["id"] : 0;

$result = resume_state_web_service($webservice_execution_log_id);
print '<pre>'; print_r($result);

die;
function resume_state_web_service($webservice_execution_log_id)
{
	global $con;
	
	$request_ip = $_SERVER['REMOTE_ADDR'];
	$userid=isset($_SESSION["user_session"]["user_id"]) ? $_SESSION["user_session"]["user_id"] : 0;
	$userRole=isset($_SESSION["user_session"]["user_role"]) ? $_SESSION["user_session"]["user_role"] : 0;
	
	if(empty($request_ip) || $userid==0 || !in_array($userRole,array(1,3)) || $webservice_execution_log_id==0) {
		$data[0] = 1;
		$data[1] = "You are not in correct page. Please contact administrator.";
	}
	else
	{
		$webservice_execution_mode = 'ManualExecution';
		
		$sql=mysqli_query($con, "SELECT state_code, webservice_executed_for_date, financial_year FROM dbt_state_webservice_execution_log WHERE state_webservice_execution_log_id = $webservice_execution_log_id and error_code != 0");
		if(mysqli_num_rows($sql)<1)
		{
			$data[0] = 1;
			$data[1] = "center_webservice_execution_log_id is wrong.";
		}
		else
		{
			$query=mysqli_fetch_array($sql);
			$state_code=$query[0];
			$startdate=date("Y-m-d", strtotime($query[1]));
			$enddate=date("Y-m-d");
			
			$sql=mysqli_query($con, "SELECT w.*, s.state_name FROM dbt_webservice_details as w join dbt_state_master as s on w.api_owner = s.state_code WHERE w.api_owner = $state_code");
			
			if(mysqli_num_rows($sql)<1)
			{
				$data[0] = 1;
				$data[1] = "webservice not found.";
			}
			else
			{
				$webservice_url_info=mysqli_fetch_object($sql);
				
				//=======================================================================
				$startdate_d=date("d", strtotime($query[1]));
				$startdate_m=date("m", strtotime($query[1]));
				$startdate_y=date("Y", strtotime($query[1]));
				$fy_year = $query[2];
				if(intval($startdate_m) != 5) {
					$prev_month = date("m", strtotime("$startdate -2 month"));
					$sql_query=mysqli_query($con, "SELECT count(*) as count FROM dbt_state_scheme_beneficiary_data WHERE state_code = '$state_code' and reporting_month = '$prev_month' and financial_year = '$fy_year'");
					$data_count_arr = mysqli_fetch_array($sql_query);
					$data_count = $data_count_arr[0];
					if($data_count == 0){
						$sql_query=mysqli_query($con, "SELECT max(scheme_transaction_from_date) as tr_date FROM dbt_state_scheme_beneficiary_data WHERE state_code = '$state_code' and financial_year = '$fy_year'");
						$tr_date_arr = mysqli_fetch_array($sql_query);
						$tr_date = $tr_date_arr[0];
						
						if(isset($tr_date)){
							$startdate_m=date("m", strtotime("$tr_date +2 month"));
							$startdate=$startdate_y.'-'.$startdate_m.'-'.$startdate_d;
						} else {
							$startdate=date("Y-m-d", strtotime($startdate_y.'-05-'.$startdate_d));
						}
					}
				}
				if($webservice_url_info->execution_time == 'morning'){
					$execution_time = '06:00:00';
				} else {
					$execution_time = '21:00:00';
				}
				$current_date_time = date('Y-m-d H:i:s');
				$execution_end_date_time = $enddate.' '.$execution_time;
				//=======================================================================
					
				// execution_day
				if($webservice_url_info->webservice_frequency_id == 1) {
					if(strtotime($current_date_time) > strtotime($execution_end_date_time)){
						$enddate = date("Y-m-d", strtotime("$enddate"));
					} else {
						$enddate = date("Y-m-d", strtotime("$enddate -1 days"));
					}
					$datediff = strtotime($enddate) - strtotime($startdate);
					$loop = round($datediff / (60 * 60 * 24));
				}
				else {
					
					if($webservice_url_info->execution_day > date("d"))
					{
						$enddate = date("Y-m-d", strtotime("$enddate -1 month"));						
					} elseif($webservice_url_info->execution_day == date("d")){
						if(strtotime($current_date_time) > strtotime($execution_end_date_time)){
							$enddate = date("Y-m-d", strtotime("$enddate -1 month"));
						} else {
							$enddate = date("Y-m-d", strtotime("$enddate"));
						}
					}
					
					$startmonth = date('m', strtotime($startdate));
					$endmonth = date('m', strtotime("$enddate"));
					$startyear = date('Y', strtotime($startdate));
					$endyear = date('Y', strtotime("$enddate"));
					
					if($webservice_url_info->execution_day > date("d"))
					$loop = (($endyear - $startyear) * 12) + ($endmonth - $startmonth);
					else
					$loop = (($endyear - $startyear) * 12) + ($endmonth - $startmonth) + 1;
				}
				if($loop==0) $loop=1;
				
					$error_code = 0;
					$is_success = 'no';
					$is_updated = 'no';
					$error_message = '';
					$state_code = $webservice_url_info->api_owner;
					$state_name = $webservice_url_info->state_name;
					$webservice_url_ip	= $webservice_url_info->webservice_url_ip;
					$nodal_person_email	= $webservice_url_info->nodal_person_email;
					$officer_email		= $webservice_url_info->officer_email;
					$link_officer_email	= $webservice_url_info->link_officer_email;
					$nodalp_email		= array($nodal_person_email);
					$nodalp_email_to_log		= implode(",",array($nodal_person_email));
					$cc_email			= array($officer_email,$link_officer_email);
					$execution_day		= $webservice_url_info->execution_day;
					if(intval($execution_day) < 10){
						$execution_day = '0'.intval($execution_day);
					}
					
					$error_flag = 0;
				for($i=0;$i<$loop;$i++)
				{
					
					if($webservice_url_info->webservice_frequency_id == 1) {
						$execution_day =  date('d', strtotime("$startdate +$i day"));
						$execution_month = date('m', strtotime("$startdate +$i day"));
						$execution_year	= date('Y', strtotime("$startdate +$i day"));
						if(intval($execution_day) < 10){
							$execution_day = '0'.intval($execution_day);
						}
					}
					else
					{
						$execution_month	= date('m', strtotime("$startdate +$i month"));
						$execution_year		= date('Y', strtotime("$startdate +$i month"));
					}
					$error_code = 0;
					if($webservice_url_info->request_date_format == 'dd-mm-yyyy') {
						$requestdate = $execution_day.'-'.$execution_month.'-'.$execution_year;
					} else if($webservice_url_info->request_date_format == 'dd/mm/yyyy') {
						$requestdate = $execution_day.'/'.$execution_month.'/'.$execution_year;
					} else if($webservice_url_info->request_date_format == 'yyyy-mm-dd') {
						$requestdate = $execution_year.'-'.$execution_month.'-'.$execution_day;
					} else {
						$requestdate = $execution_day.'/'.$execution_month.'/'.$execution_year;
					}
					$servicedate = $execution_day.'-'.$execution_month.'-'.$execution_year;
					$ymd_servicedate = $execution_year.'-'.$execution_month.'-'.$execution_day;
					
					if($webservice_url_info->webservice_frequency_id == 1){
						$dataday = date("d", strtotime($ymd_servicedate));
						$datamonth = date("m",strtotime($ymd_servicedate));
						$datayear = date("Y",strtotime($ymd_servicedate));
					} else {
						$dataday = "00";
						$datamonth = date("m", strtotime("$ymd_servicedate -1 month"));
						$datayear = date("Y", strtotime("$ymd_servicedate -1 month"));
					}
					// financial_year
					if(intval($datamonth) <= 3) {
						$s_year = intval($datayear) - 1;
						$e_year = $datayear;
					} else {
						$s_year = $datayear;
						$e_year = intval($datayear) + 1;
					}
					$financial_year = $s_year.'_'.$e_year;
					
					// create log file
					$filename = $webservice_url_info->api_owner."_".time()."_".$servicedate.".txt";
					$rootpath = realpath(__DIR__.'/../../../')."/data/logs/stateservices/";
					$myfile = fopen($rootpath.$filename, "w") or die("Unable to open file!");
					chmod($rootpath.$filename, 0777);
					
					$errormsg = '';
					$txt = '';
					$txt .= "Execution Time: ".date("d/m/Y h:i:sa")."\n\n";
					$txt .= "Requested Date: ".$servicedate."\n\n";
					
					if($webservice_url_info->parameter_value_encode == 'y'){
						$requestdate = base64_encode($requestdate);
					}
					
					$get_ws_data = get_plain_ws_data($webservice_url_info,$requestdate,$txt);
					$responseData = $get_ws_data[0];
					$txt = $get_ws_data[1];
					if(empty($get_ws_data[2])){
						// calling data insertion function
						$returnvalue = insert_state_scheme_data($state_code, $responseData, $txt, $datamonth, $datayear, $nodalp_email, $cc_email, $userid);
						$txt = $returnvalue[1];
						$is_updated = $returnvalue[4];
						$validation_message = $returnvalue[2];
						$error_message = $returnvalue[0];
						if($is_updated == 'yes') {
						  $is_success = 'yes';
						}
						if(!empty($error_message)){
							$errormsg = $error_message;
							$error_code = 3;
						}
						if(!empty($validation_message)){
							$errormsg = $validation_message;
							$error_code = 3;
						}
					} else {
						$errormsg = $get_ws_data[2];
						$error_code = $get_ws_data[3];
					}
					

					// write log to file
					fwrite($myfile, $txt);
					fclose($myfile);
					
					//send mail functions
					$subject        = $state_name.' Web-service Update!';
					$cc = implode(',', $nodalp_email);
					$sender_name    = 'DBT Bharat';
					$message = 'Please find attached response file of webservice.';
					$sentmail = send_mail('feedback@dbtbharat.gov.in', $cc, $sender_name, $subject, $message, $rootpath, $filename);
							
					// log function
					$remarks = serialize(trim(str_replace( array( '\'', '"', ',' , ';', '<', '>' ), ' ', $errormsg)));
					$log_id = webserviceauditlogfunction($state_code, $dataday, $datamonth, $financial_year, $is_updated, $is_success, $remarks, $webservice_execution_mode, $ymd_servicedate, $request_ip, $error_code, $i, $webservice_execution_log_id, $error_message);
					if($error_code != 0){
						$error_flag++;
						$update_data = update_webservice_status($webservice_url_info->webservice_id);
						$temp_data = insert_data_in_temp_table($responseData,$state_code,$userid,$request_ip,$log_id);
						$data[1] = "Failed";
						break;
					}
					
				} // loop end
				if($error_flag==0){
					// update status of dbt_webservice_details
					$sql_update = mysqli_query($con, "update dbt_webservice_details set webservice_status = 1 where api_owner = $state_code");
					$data[1] = "Success";
				}
				// $msg="Web service resolved.";
				$data[0] = $error_flag;
				$getValue = $responseData = $state_code = $schemeid = $servicedate = $ymd_servicedate = $is_updated = $txt = $nodalp_email = $cc_email = $datamonth = $scheme_code = $remarks = '';
			}
		}
	}
	return $data;
}
	

