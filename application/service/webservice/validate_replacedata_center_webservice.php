<?php
error_reporting(E_ALL);
//ini_set('display_errors', 1);

session_start();
require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
require_once(realpath(__DIR__.'/../common_functions.php'));
require_once(realpath(__DIR__.'/../replace_functions.php'));
$data_replacement_request_id = isset($_GET[data_replacement_request_id]) > 0 ? (int)$_GET[data_replacement_request_id] : 0;

$result = validate_replacedata($data_replacement_request_id);

 $data=json_encode($result);
 echo $data;
 die;

function validate_replacedata($data_replacement_request_id)
{
	global $con;
	$request_ip = $_SERVER['REMOTE_ADDR'];
	$userid=isset($_SESSION["user_session"]["user_id"]) ? $_SESSION["user_session"]["user_id"] : 0;
	$userRole=isset($_SESSION["user_session"]["user_role"]) ? $_SESSION["user_session"]["user_role"] : 0;
	
	if(empty($request_ip) || $userid==0 || !in_array($userRole,array(1,3)) || $data_replacement_request_id==0) {
		$data[0] = 1;
		$data[1] = "You are not in correct page. Please contact administrator.";
	}
	else
	{
		$webservice_execution_mode = 'ManualExecution';
		$statecode = '00';
		$sql=mysqli_query($con, "SELECT scheme_id, from_month, to_month, financial_year FROM dbt_data_replacement_request  WHERE data_replacement_request_id = $data_replacement_request_id and status = 'p' and data_replaced = 'n'");
		if(mysqli_num_rows($sql)<1)
		{
			$data[0] = 1;
			$data[1] = "data_replacement_request_id is wrong.";
		}
		else
		{
			$query=mysqli_fetch_array($sql);
			
			$scheme_id=$query[0];
			$from_month=$query[1];
			$to_month=$query[2];
			$financialYear=$query[3];
			
			$sql=mysqli_query($con, "SELECT w.*, s.scheme_code FROM dbt_webservice_details as w join dbt_scheme_master as s on s.scheme_id = w.scheme_id WHERE w.scheme_id = $scheme_id");
			
			if(mysqli_num_rows($sql)<1)
			{
				$data[0] = 1;
				$data[1] = "webservice not found.";
			}
			else
			{
				$webservice_url_info=mysqli_fetch_object($sql);
				if($webservice_url_info->api_version=="o")	require_once(realpath(__DIR__."/center_webservice_functions_old.php"));
				if($webservice_url_info->api_version=="n")	require_once(realpath(__DIR__."/center_webservice_functions_new.php"));
				$webservice_frequency_id = $webservice_url_info->webservice_frequency_id;
				$request_date_format = $webservice_url_info->request_date_format;
				$webservice_data_type = $webservice_url_info->webservice_data_type_id;
				$execution_day=$webservice_url_info->execution_day;
				$webservice_url_ip	= $webservice_url_info->webservice_url_ip;
				$nodal_person_email	= $webservice_url_info->nodal_person_email;
				$officer_email		= $webservice_url_info->officer_email;
				$link_officer_email	= $webservice_url_info->link_officer_email;
				$nodalp_email		= array($nodal_person_email);
				$nodalp_email_to_log= implode(",",array($nodal_person_email));
				$cc_email			= array($officer_email,$link_officer_email);
				$scheme_code		= $webservice_url_info->scheme_code;
				$fyear=explode("_", $financialYear);
				$s_year = $fyear[0];
				$e_year = $fyear[1];
				$from_year=getyearformonth($from_month,$s_year,$e_year);
				$to_year=getyearformonth($to_month,$s_year,$e_year);
				//$execution_year=getyearformonth($reporting_month,$s_year,$e_year);
				if(intval($execution_day) < 10){
						$execution_day = '0'.intval($execution_day);
					}
					
				$startdate=$from_year."-".$from_month."-".$execution_day;
			    $enddate=$to_year."-".$to_month."-".$execution_day;
				$startdate=date('Y-m-d', strtotime("$startdate +1 month"));
				$enddate=date('Y-m-d', strtotime("$enddate +1 month"));
				$loopcount=0;
				if($webservice_frequency_id == 2){
					$interval="month";
				for($stdate=$startdate;strtotime($stdate)<=strtotime($enddate);)
					{
						$loopcount++;
						
						$schemeinfo = get_scheme_info($scheme_code);
						$execution_month = date('m', strtotime($stdate));
						$execution_year = date('Y', strtotime($stdate));
						
						$error_code = 0;
						$requestdate = requestDate($request_date_format, $execution_day, $execution_month, $execution_year);
						$servicedate = $execution_year.'-'.$execution_month.'-'.$execution_day;
						$servicedate=date('Y-m-d', strtotime("$servicedate -1 month"));
						
						$dataday = "00";
						$datamonth = date("m", strtotime($servicedate));
						$datayear = date("Y", strtotime($servicedate));
						$rqDate = dateFormationYMD($requestdate,$request_date_format);
						
						// create log file
						$filename = $webservice_url_info->scheme_code."_".time()."_".date("d-m-Y", strtotime($rqDate)).".txt";
						$rootpath = $rootpath = realpath(__DIR__.'/../../../')."/data/logs/schemeservices/replace/";
						$myfile = fopen($rootpath.$filename, "w") or die("Unable to open file!");
						chmod($rootpath.$filename, 0777);
						
						
						$errormsg = '';
						$txt = '';
						$txt .= "Execution Time: ".date("d/m/Y h:i:sa")."\n\n";
						$txt .= "Requested Date: ".$requestdate."\n\n";
						if($schemeinfo[0]==0)	//	scheme validation has no error
						{							
							if($webservice_url_info->parameter_value_encode == 'y'){
								$scheme_code = base64_encode($scheme_code);
								$state_code = base64_encode($statecode);
								$requestdate = base64_encode($requestdate);
							}
							
							$scheme_id = $schemeinfo[1]->scheme_id;
							$get_ws_data = get_plain_ws_data($webservice_url_info,$scheme_code,$statecode,$requestdate,$txt);
							
							 //print_r($get_ws_data);
							 //die;
							$txt.=$get_ws_data[1];
							$error_code=$get_ws_data[3];
							if($error_code==0) {
								
								$responseData = $get_ws_data[0];
								
								$benefit_type_query = mysqli_query($con,"SELECT scheme_benefit_type_id FROM dbt_scheme_benefit_type_relation where scheme_id = '$scheme_id'");
								$benefit_type = array();
								while($benefit_type_id = mysqli_fetch_object($benefit_type_query)){
								$benefit_type[] = $benefit_type_id->scheme_benefit_type_id;
								}
															
								$validation_response = generalValidation_replace($responseData,$scheme_id,$datamonth,$benefit_type,$s_year,$e_year,$txt,$loopcount);
								
								$txt = $validation_response['log'];
								if($validation_response['error_result']>0)
								{
									$txt .= $validation_message = $errormsg .= $validation_response['validation_message'];
									$validation_message = $validation_response['validation_message'];
									$error_msg_arr = $validation_response["err_msg"];
									if(!empty($validation_message)){
										$errormsg = $validation_message;
										$error_code = 3;
									}
									$data[0] = 1;
									$data[1] = $validation_message." : ".$error_msg_arr;
									$errormsg=$data[1];
								}
								else
								{
									$res=insert_temp_replace_data($responseData, $scheme_id, $benefit_type);
									if($res>0)
									{
										$data[0] = 0;
										$data[1] = "General Validation completed,Data inserted in temp table.";
									}
									else	die("not inserted.");
								}
							} else {
								$errormsg = $get_ws_data[2];
								$error_code = $get_ws_data[3];
								$data[0] = 1;
								$data[1] = "Response contains error : ".$errormsg;
								//$errormsg="Response contains error";
							}
						} else {
							$txt .= $errormsg = $schemeinfo[1];	//	scheme code error message
							$error_code = 7;
							$data[0] = 1;
							$data[1] = "scheme code not found or deactivated.";
						}
						// write log to file
						fwrite($myfile, $txt);
						fclose($myfile);
						$errormsg=textValidation($errormsg);
						$errormsg=str_replace("'",' ',$errormsg);
						$errormsg=str_replace('"',' ',$errormsg);
						
						//die;
					//create log entry 
					$webservice_execution_log_id = createLogEntry($scheme_id,$dataday,$datamonth,$financialYear,$webservice_execution_mode,$rqDate,$request_ip,"r",$data_replacement_request_id,$error_code, $errormsg);
					if($webservice_execution_log_id==0)
					{
						$data[0] = 1;
						$data[1] = "System error occured.";
					}
					if($data[0] == 1)
					{
						break;
					}
					else
					{
						$stdate = date("Y-m-d", strtotime("$stdate +1 $interval"));
					}
				}//loop end
				    $temp=0;
					if($data[0] == 0)
					{
						$data_replaced="v";
						$temp=update_replace_validation_status($data_replacement_request_id,$data_replaced);// update replace request state table set data_replaced='v' (means validated)
					}
					else
					{
						deletetempdata($scheme_id,$from_month,$to_month,$financialYear);//delete from both temp table(benificiary data & transaction data) 
						$data_replaced="f";
						$temp=update_replace_validation_failed_status($data_replacement_request_id,$data_replaced,$errormsg);// update replace request state table set data_replaced='f' (means validation failed)
					}
					
					if($temp==0)
					{
						$data[0] = 1;
						$data[1] = "System error occured.";
						$error_code = 5;
						//$errormsg = $data[1];
					}
				}
				else
				{
					$data[0] = 1;
		            $data[1] = "Data Replace facility is available only for Schemes which gives data monthly.";
				}
			}
		}
	}
	return $data;
}
?>