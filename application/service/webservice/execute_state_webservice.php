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
	
	
$state_code = isset($_GET['sid']) > 0 ? (int)$_GET['sid'] : 0;
$date_val = isset($_GET['date']) ? $_GET['date'] : 0;

$result = execute_state_web_service($state_code,$date_val);
print_r($result);

die;
function execute_state_web_service($state_code,$date_val)
{
	global $con;
	
	$request_ip = $_SERVER['REMOTE_ADDR'];
	$userid=isset($_SESSION["user_session"]["user_id"]) ? $_SESSION["user_session"]["user_id"] : 0;
	$userRole=isset($_SESSION["user_session"]["user_role"]) ? $_SESSION["user_session"]["user_role"] : 0;
	$data_flag = 1;
	
	if(empty($request_ip) || $userid==0 || !in_array($userRole,array(1,3)) || $state_code==0) {
		$data[0] = 1;
		$data[1] = "You are not in correct page. Please contact administrator.";
	}
	else
	{
		if(isset($state_code) && isset($date_val)) {
			$d = DateTime::createFromFormat('Y-m-d', $date_val);
			$check_val = $d && $d->format('Y-m-d') === $date_val;
			
			$date_now = date("Y-m-d"); // this format is string comparable
			
			if ($check_val == 1 && strtotime($date_now) >= strtotime($date_val)) {
				$webservice_execution_mode = 'ManualExecution';
				
				$sql=mysqli_query($con, "SELECT state_code, webservice_executed_for_date, financial_year FROM dbt_state_webservice_execution_log WHERE state_code = $state_code and error_code != 0");
				if(mysqli_num_rows($sql)>0)
				{
					$data[0] = 1;
					$data[1] = "This web-service executed previously and not resumed yet.";
				}
				else
				{
					$sql=mysqli_query($con, "SELECT w.*, s.state_name FROM dbt_webservice_details as w join dbt_state_master as s on w.api_owner = s.state_code WHERE w.api_owner = $state_code and w.webservice_status = 1 and w.webservice_schedule_status = 'S'");
					
					if(mysqli_num_rows($sql)<1)
					{
						$data[0] = 1;
						$data[1] = "webservice not found.";
					}
					else
					{
						$url_details=mysqli_fetch_object($sql);
						$error_code = 0;
						$is_success = 'no';
						$is_updated = 'no';
						$state_code = $url_details->api_owner;
						$state_name = $url_details->state_name;
						$webservice_url_ip	= $url_details->webservice_url_ip;
						$nodal_person_email	= $url_details->nodal_person_email;
						$officer_email		= $url_details->officer_email;
						$link_officer_email	= $url_details->link_officer_email;
						$nodalp_email		= array($nodal_person_email);
						$nodalp_email_to_log		= implode(",",array($nodal_person_email));
						$cc_email			= array($officer_email,$link_officer_email);
						$execution_day		= date("d", strtotime("$date_val"));
						if(intval($execution_day) < 10){
							$execution_day = '0'.intval($execution_day);
						}
						$execution_month	= date("m", strtotime("$date_val"));
						$execution_year		= date("Y", strtotime("$date_val"));
						
						if($url_details->request_date_format == 'dd-mm-yyyy') {
							$requestdate = $execution_day.'-'.$execution_month.'-'.$execution_year;
						} else if($url_details->request_date_format == 'dd/mm/yyyy') {
							$requestdate = $execution_day.'/'.$execution_month.'/'.$execution_year;
						} else if($url_details->request_date_format == 'yyyy-mm-dd') {
							$requestdate = $execution_year.'-'.$execution_month.'-'.$execution_day;
						} else {
							$requestdate = $execution_day.'/'.$execution_month.'/'.$execution_year;
						}
						$servicedate = $execution_day.'-'.$execution_month.'-'.$execution_year;
						$ymd_servicedate = $execution_year.'-'.$execution_month.'-'.$execution_day;
						
						if($url_details->webservice_frequency_id == 1){
							$requestdate = date('d/m/Y');
							$servicedate = date('d-m-Y');
							$ymd_servicedate = date('Y-m-d');
							$dataday = date("d", strtotime($ymd_servicedate));
							$datamonth = date("m",strtotime($ymd_servicedate));
							$datayear = date("Y",strtotime($ymd_servicedate));
							
							// check prev day data
							$data_month_date = $ymd_servicedate.' 00:00:01';
							$data_prev_date = date("Y-m-d H:i:s", strtotime("$data_month_date -1 day"));
							$prev_month_data_year = date("Y", strtotime("$data_prev_date"));
							$prev_month = date("m", strtotime("$data_prev_date"));
							if(intval($prev_month) <= 3){
								$fy_year = ($prev_month_data_year - 1).'_'.$prev_month_data_year;
							} else {
								$fy_year = $prev_month_data_year.'_'.($prev_month_data_year + 1);
							}
							$sql_query=mysqli_query($con, "SELECT count(*) as count FROM dbt_state_scheme_beneficiary_data WHERE state_code = '$state_code' and financial_year = '$fy_year' and scheme_transaction_from_date = '$data_prev_date'");
							$data_count_arr = mysqli_fetch_array($sql_query);
							$data_count = $data_count_arr[0];
							if($data_count <= 0){
								$data_flag = 0;
							}
							
						} else {
							$dataday = "00";
							$datamonth = date("m", strtotime("$ymd_servicedate -1 month"));
							$datayear = date("Y", strtotime("$ymd_servicedate -1 month"));
							
							// check prev month data
							if(intval($datamonth) != 5) {
								$prev_month = date("m", strtotime("$ymd_servicedate -2 month"));
								$prev_month_data_year = date("Y", strtotime("$ymd_servicedate -2 month"));
								if(intval($prev_month) <= 3){
									$fy_year = ($prev_month_data_year - 1).'_'.$prev_month_data_year;
								} else {
									$fy_year = $prev_month_data_year.'_'.($prev_month_data_year + 1);
								}
								$sql_query=mysqli_query($con, "SELECT count(*) as count FROM dbt_state_scheme_beneficiary_data WHERE state_code = '$state_code' and reporting_month = '$prev_month' and financial_year = '$fy_year'");
								$data_count_arr = mysqli_fetch_array($sql_query);
								$data_count = $data_count_arr[0];
								if($data_count <= 0){
									$data_flag = 0;
								}
							}
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
						$filename = $url_details->api_owner."_".time()."_".$servicedate.".txt";
						$rootpath = realpath(__DIR__.'/../../../')."/data/logs/stateservices/";
						$myfile = fopen($rootpath.$filename, "w") or die("Unable to open file!");
						chmod($rootpath.$filename, 0777);
						
						$errormsg = '';
						$txt = '';
						$txt .= "Execution Time: ".date("d/m/Y h:i:sa")."\n\n";
						$txt .= "Requested Date: ".$servicedate."\n\n";
						
						if($url_details->parameter_value_encode == 'y'){
							$requestdate = base64_encode($requestdate);
						}
						
						$get_ws_data = array();
						if($data_flag == 1){
							$get_ws_data = get_plain_ws_data($url_details,$requestdate,$txt);
							$responseData = $get_ws_data[0];
							$txt = $get_ws_data[1];
						} else {
							$txt .= $error_message = 'Previous month or day data not available!';
							$get_ws_data[2] = 'Previous month or day data not available!';
							$get_ws_data[3] = 5;
						}
						
						if(empty($get_ws_data[2])){
							// calling data insertion function
							$returnvalue = insert_state_scheme_data($state_code, $responseData, $txt, $datamonth, $datayear, $nodalp_email, $cc_email, $userid);
							$txt = $returnvalue[1];
							$is_updated = $returnvalue[4];
							$validation_message = $returnvalue[2];
							$error_message = $returnvalue[5];
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
						$log_id = webserviceauditlogfunction($state_code, $dataday, $datamonth, $financial_year, $is_updated, $is_success, $remarks, $webservice_execution_mode, $ymd_servicedate, $request_ip, $error_code, $rowCount=null, $webservice_execution_log_id=null,$error_message);
						$data[0] = 0;
						$data[1] = "Success.";
						if($error_code != 0){
							$update_data = update_webservice_status($url_details->webservice_id);
							$temp_data = insert_data_in_temp_table($responseData,$state_code,$userid,$request_ip,$log_id);
							$data[0] = 1;
							$data[1] = "Failed.";
						}
						
						$getValue = $responseData = $state_code = $schemeid = $servicedate = $ymd_servicedate = $is_updated = $txt = $nodalp_email = $cc_email = $datamonth = $scheme_code = $remarks = '';
					}
				}
			}else{
				$data[0] = 1;
				$data[1] = "Date format is not correct or greater than current date.";
			}
		} else {
			$data[0] = 1;
			$data[1] = "Required request parameters are not found.";
		}
	}
	return $data;
}
	

