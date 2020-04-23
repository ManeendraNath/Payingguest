<?php
ini_set("error_reporting","E_ALL");
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
	require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
	require_once(realpath(__DIR__.'/../insertwebservicedatanewformat-state.php'));
	require_once(realpath(__DIR__.'/../sendmail.php'));
	require_once(realpath(__DIR__.'/state_webservice_functions.php'));
	
	global $con;
	$request_ip = $_SERVER['REMOTE_ADDR'];
	if(isset($_REQUEST['mode']) || !empty($request_ip)){
		$webservice_execution_mode = 'ManualExecution';
	} else {
		$webservice_execution_mode = 'AutoExecution';
	}
	$day = date('d');
	$newDateTime = date('A', strtotime(date('Y-m-d H:i:s')));
	if ($newDateTime == 'AM') {
		$time_val = 'morning';
	} elseif ($newDateTime == 'PM') {
		$time_val = 'evening';
	}
	$userid = 1;
	// get web-service url details
	$webservice_url_info = get_webservice_url_details($day,$api_owner=1,$time_val);
	foreach($webservice_url_info as $url_details){
		$data_flag = 1;
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
		$execution_day		= $url_details->execution_day;
		if(intval($execution_day) < 10){
			$execution_day = '0'.intval($execution_day);
		}
		$execution_month	= date('m');
		$execution_year		= date('Y');
		
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
			$txt .= $error_message = 'Previous month/day data not available!';
			$get_ws_data[2] = 'Previous month/day data not available!';
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
		if($error_code != 0){
			$update_data = update_webservice_status($url_details->webservice_id);
			$temp_data = insert_data_in_temp_table($responseData,$state_code,$userid,$request_ip,$log_id);
		}
		
		
		
		$getValue = $responseData = $state_code = $schemeid = $servicedate = $ymd_servicedate = $is_updated = $txt = $nodalp_email = $cc_email = $datamonth = $scheme_code = $remarks = '';
	} // end foreach
	

die('Done');
