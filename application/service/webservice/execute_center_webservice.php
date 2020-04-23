<?php
error_reporting(E_ALL);
session_start();
require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
require_once(realpath(__DIR__.'/../insertwebservicedatanewformat.php'));
require_once(realpath(__DIR__.'/../sendmail.php'));
require_once(realpath(__DIR__.'/center_webservice_functions.php'));
$scheme_id = isset($_GET['sid']) > 0 ? (int)$_GET['sid'] : 0;
$date_val = isset($_GET['date']) ? $_GET['date'] : 0;

$result = execute_center_web_service($scheme_id, $date_val);
print '<pre>'; print_r($result);
die;

function execute_center_web_service($scheme_id, $date_val)
{
	global $con;
	$request_ip = $_SERVER['REMOTE_ADDR'];
	$userid=isset($_SESSION["user_session"]["user_id"]) ? $_SESSION["user_session"]["user_id"] : 0;
	$userRole=isset($_SESSION["user_session"]["user_role"]) ? $_SESSION["user_session"]["user_role"] : 0;
	$data_flag = 1;
	
	if(empty($request_ip) || $userid==0 || !in_array($userRole,array(1,3)) || $scheme_id==0) {
		$data[0] = 1;
		$data[1] = "You are not in correct page. Please contact administrator.";
	}
	else
	{
		if(isset($scheme_id) && isset($date_val)) {
			
			$d = DateTime::createFromFormat('Y-m-d', $date_val);
			$check_val = $d && $d->format('Y-m-d') === $date_val;
			
			$date_now = date("Y-m-d"); // this format is string comparable
			if ($check_val == 1 && strtotime($date_now) >= strtotime($date_val)) {
						
				$webservice_execution_mode = 'ManualExecution';
				$statecode = '00';
				$sql=mysqli_query($con, "SELECT scheme_id, webservice_executed_for_date, financial_year FROM dbt_center_webservice_execution_log WHERE scheme_id = $scheme_id and error_code != 0");
				
				if(mysqli_num_rows($sql)>0)
				{
					$data[0] = 1;
					$data[1] = "This web-service executed previously and not resumed yet.";
				}
				else
				{
					$sql=mysqli_query($con, "SELECT w.*, s.scheme_code FROM dbt_webservice_details as w join dbt_scheme_master as s on s.scheme_id = w.scheme_id WHERE w.scheme_id = $scheme_id and w.webservice_status = 1 and w.webservice_schedule_status = 'S'");
					
					if(mysqli_num_rows($sql)<1)
					{
						$data[0] = 1;
						$data[1] = "webservice not found.";
					}
					else
					{
						$url_details=mysqli_fetch_object($sql);
						
						$error_msg_arr = '';
						$webservice_url_ip	= $url_details->webservice_url_ip;
						$nodal_person_email	= $url_details->nodal_person_email;
						$officer_email		= $url_details->officer_email;
						$link_officer_email	= $url_details->link_officer_email;
						$nodalp_email		= array($nodal_person_email);
						$nodalp_email_to_log= implode(",",array($nodal_person_email));
						$cc_email			= array($officer_email,$link_officer_email);
						$scheme_code		= $url_details->scheme_code;
						$execution_day		= date("d", strtotime("$date_val"));
						if(intval($execution_day) < 10){
							$execution_day = '0'.intval($execution_day);
						}
						$execution_month	= date("m", strtotime("$date_val"));
						$execution_year		= date("Y", strtotime("$date_val"));
						$error_code = 0;
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
						
						$scheme_id = $url_details->scheme_id;
						if($url_details->webservice_frequency_id == 1){
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
							$sql_query=mysqli_query($con, "SELECT count(*) as count FROM dbt_scheme_beneficiary_data WHERE scheme_id = '$scheme_id' and financial_year = '$fy_year' and scheme_transaction_from_date = '$data_prev_date'");
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
								$sql_query=mysqli_query($con, "SELECT count(*) as count FROM dbt_scheme_beneficiary_data WHERE scheme_id = '$scheme_id' and reporting_month = '$prev_month' and financial_year = '$fy_year'");
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
						$filename = $scheme_code."_".time()."_".$servicedate.".txt";
						$rootpath = realpath(__DIR__.'/../../../')."/data/logs/schemeservices/";
						$myfile = fopen($rootpath.$filename, "w") or die("Unable to open file!");
						chmod($rootpath.$filename, 0777);
						
						$errormsg = '';
						$schemeid = '';
						$is_updated = 'no';
						$is_success = 'no';
						$txt = '';
						$txt .= "Execution Time: ".date("d/m/Y h:i:sa")."\n\n";
						$txt .= "Requested Date: ".$servicedate."\n\n";
						
						$schemeinfo = get_scheme_info($scheme_code);
						if($url_details->parameter_value_encode == 'y'){
							$scheme_code = base64_encode($scheme_code);
							$statecode = base64_encode($statecode);
							$requestdate = base64_encode($requestdate);
						}
						if($schemeinfo){
							$get_ws_data = array();
							if($data_flag == 1){
								$schemeid = $schemeinfo->scheme_id;		
								$get_ws_data = get_plain_ws_data($url_details,$scheme_code,$statecode,$requestdate,$txt);
								$txt = isset($get_ws_data[1]) ? $get_ws_data[1] : "";
							} else {
								$txt .= $error_msg_arr = 'Previous month or day data not available!';
								$get_ws_data[2] = 'Previous month or day data not available!';
								$get_ws_data[3] = 5;
							}
						} else {
							$txt .= $errormsg = 'Scheme not found or deactivated';
						}
						$webservice_data_type = $url_details->webservice_data_type_id;
						// calling data insertion function
						if(empty($get_ws_data[2])) {
							$responseData = $get_ws_data[0];
							$returnvalue = insert_scheme_data($responseData, $schemeid, $servicedate, $is_updated, $txt, $nodalp_email, $cc_email, $dataday, $datamonth, $datayear, $scheme_code,$webservice_data_type);
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
						// write log to file
						fwrite($myfile, $txt);
						fclose($myfile);
						
						//send mail functions
						$subject        = $scheme_code.' | Web-service Update!';
						$cc = implode(',', $nodalp_email);
						$sender_name    = 'DBT Bharat';
						$message = 'Please find attached response file of webservice.';
						$sentmail = send_mail('feedback@dbtbharat.gov.in', $cc, $sender_name, $subject, $message, $rootpath, $filename);
						
						// log function
						$remarks = serialize(trim(str_replace( array( '\'', '"', ',' , ';', '<', '>' ), ' ', $errormsg)));
						$log_id = webserviceauditlogfunction($schemeid, $dataday, $datamonth, $financial_year, $is_updated, $is_success, $remarks, $webservice_execution_mode, $ymd_servicedate, $request_ip, $error_code, $rowCount=null, $webservice_execution_log_id=null,$error_msg_arr);
						$data[0] = 0;
						$data[1] = "Success.";
						if($error_code != 0){
							$update_data = update_webservice_status($url_details->webservice_id);
							$temp_data = insert_data_in_temp_table($responseData,$schemeid,$userid,$request_ip,$log_id);
							$data[0] = 1;
							$data[1] = "Failed.";
						}
						
						$getValue = $responseData = $schemeid = $servicedate = $ymd_servicedate = $is_updated = $txt = $nodalp_email = $cc_email = $datamonth = $scheme_code = $remarks = '';
						
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
?>