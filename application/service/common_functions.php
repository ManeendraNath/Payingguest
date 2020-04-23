<?php
error_reporting(E_ALL);

function textValidation($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function requestToken($webservice_execution_log_id, $api_token, $length = 8)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet);
    for($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }
//	$api_token will be "center" or "state"
	$tablename="dbt_".$api_token."_webservice_execution_log";
	$fieldname=$api_token."_webservice_execution_log_id";
	$checkExistance = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT (*) FROM $tablename WHERE requestToken='$token'"));
	if($checkExistance[0]>0)	requestToken($webservice_execution_log_id, $api_token, $length);
	else
	{
	//	alter table $tablename add column requestToken varchar(50);
		mysqli_query($con, "UPDATE $tablename SET requestToken='$token' WHERE $fieldname = $webservice_execution_log_id");
		return $token;
	}
}

function checkPositiveNumber($number) {
	if(!is_numeric($number) || $number<0)	return 1;
	else									return 0;
}

function dateFormationYMD($request_date,$format)	// request date must be in dmy format
{
	if($format=='dd-mm-yyyy' || $format=='dd/mm/yyyy')
	{
		if(strpos($request_date, '/') !== false)	$delimeter="/";
		else										$delimeter="-";
		$date=explode($delimeter, $request_date);
		return $date[2]."-".$date[1]."-".$date[0];
	}
	else if($format=='yyyy-mm-dd' || $format=='yyyy/mm/dd')
	{
		if(strpos($request_date, '/') !== false)	$delimeter="/";
		else										$delimeter="-";
		$date=explode($delimeter, $request_date);
		return $date[0]."-".$date[1]."-".$date[2];
	}
}
//	error master
function get_error_master() {
	global $con;
	$result = mysqli_query($con,"SELECT webservice_error_code_master_id, webservice_error_code_details FROM dbt_webservice_error_code_master");
	while($res=mysqli_fetch_array($result))	$error[]=$res;
	return $error;
}
//	get scheme id
function get_scheme_info($schemecode) {
	global $con;
	$result = mysqli_query($con,"SELECT s.scheme_id as scheme_id, sd.scheme_name as scheme_name FROM dbt_scheme_master as s INNER JOIN dbt_scheme_details as sd on s.scheme_id = sd.scheme_id where s.scheme_code = '$schemecode' and s.scheme_status = 1");
	if(mysqli_num_rows($result)==0)
	{
		$data[0]=1;
		$data[1]="No scheme is available of this scheme code.";
	}
	elseif(mysqli_num_rows($result)==1)
	{
		$data[0]=0;
		$data[1]=mysqli_fetch_object($result);
	}
	else
	{
		$data[0]=1;
		$data[1]="More than one schemes are found with scheme code : ".$schemecode;
	}
	return $data;
}

// get web-service URL details
function get_webservice_url_details($execution_day,$api_owner,$time_val){
	global $con;
	if($api_owner == 0)
		$sql = "SELECT w.*, s.scheme_code FROM dbt_webservice_details as w join dbt_scheme_master as s on s.scheme_id = w.scheme_id where w.execution_day IN ($execution_day,0) and w.webservice_schedule_status = 'S' and w.webservice_status = 1 and w.execution_time = '$time_val' and w.api_owner = 0";
	else
		$sql = "SELECT w.*, s.state_name FROM dbt_webservice_details as w join dbt_state_master as s on s.state_code = w.api_owner where w.execution_day = '$execution_day' and w.webservice_schedule_status = 'S' and w.execution_time = '$time_val' and w.webservice_status = 1 and w.api_owner > 0";
	$result = mysqli_query($con,$sql);
	while($webservice_url_info = mysqli_fetch_object($result)){
		$webservice_url_details[] = $webservice_url_info;
	}
	return $webservice_url_details;
}

// get web-service parameters details
function get_parameters_details($webservice_id){
	global $con;
	$result = mysqli_query($con,"SELECT * FROM dbt_webservice_parameter where webservice_id = '$webservice_id' order by webservice_parameter_id");
	$parameters_details = array();
	while($parameters_info = mysqli_fetch_object($result)){
		$parameters_details[] = $parameters_info;
	}
	return $parameters_details;
}

function requestDate($format, $day, $month, $year)
{
	$requestdate="";
	if($format=='dd-mm-yyyy')		$requestdate = $day.'-'.$month.'-'.$year;
	elseif($format=='dd/mm/yyyy')	$requestdate = $day.'/'.$month.'/'.$year;
	elseif($format=='yyyy-mm-dd')	$requestdate = $year.'-'.$month.'-'.$day;
	elseif($format=='yyyy/mm/dd')	$requestdate = $year.'/'.$month.'/'.$day;
	return $requestdate;
}

function financialYear($datamonth, $datayear)
{
	if(intval($datamonth) <= 3) {
		$s_year = intval($datayear) - 1;
		$e_year = $datayear;
	} else {
		$s_year = $datayear;
		$e_year = intval($datayear) + 1;
	}
	$financial_year = $s_year.'_'.$e_year;
	return $financial_year;
}

function previousMonthDataAvalability($schemeid, $schemetype, $dataday, $datamonth, $datayear,$webservice_frequency_id,$servicedate)
{
	global $con;
	if($datamonth==1)	$datamonth = 12;
	else				$datamonth = $datamonth-1;
	if($schemetype=="center")	$tablename = "dbt_scheme_beneficiary_data";
	if($schemetype=="state")	$tablename = "dbt_state_scheme_beneficiary_data";
	$check = mysqli_query($con, "SELECT scheme_transaction_from_date, scheme_transaction_to_date,reporting_month FROM $tablename WHERE scheme_id=$schemeid ORDER BY created DESC LIMIT 1");
	
	if(mysqli_num_rows($check)>0)	
	{
		$res=mysqli_fetch_array($check);
		
		if($webservice_frequency_id==1)
		{
			$data_month_date = $servicedate.' 00:00:01';
		    $data_prev_date = date("Y-m-d H:i:s", strtotime("$data_month_date -1 day"));
			if($res[0]==$data_prev_date)
			{
				$data[0]=0;
				$data[1]=$datayear."-".$datamonth."-".$dataday;
			}
			else
			{
				$date=date("Y-m-d", strtotime("$res[0] +1 days"));
				$data[0]=1;
				$data[1]=$date;
			}
		}
		else
		{
		
			if($res[2]==$datamonth)//	previous month data found
			{
				$data[0]=0;
				$data[1]=$datayear."-".$datamonth."-".$dataday;
			}
			else
			{
				$datamonth=date("m", strtotime("$res[1] +2 months"));
				$datayear=date("Y", strtotime("$res[1] +2 months"));
				$dataday=date("d", strtotime("$servicedate"));
				$date=$datayear."-".$datamonth."-".$dataday;
			
				$data[0]=1;
				$data[1]=$date;
			}
		}
	}
	else
	{
		$datamonth=4;
		if($webservice_frequency_id==1)
		{
			$dataday=1;
		}
		else
		{
			$dataday=date("d", strtotime("$servicedate"));
		}
		$date=$datayear."-".$datamonth."-".$dataday;
		$data[0]=1;
	    $data[1]=$date;
	}
	return $data;
}

function getPreviousMonthData($schemeid, $webservice_data_type)
{
	global $con;
	$data="";
	$check = mysqli_query($con, "SELECT scheme_transaction_from_date, state_code, district_code FROM dbt_scheme_beneficiary_data WHERE scheme_id=$schemeid ORDER BY created DESC");
	if(mysqli_num_rows($check)>0)	
	{
		while($res=mysqli_fetch_array($check))
		{
			if($webservice_data_type==1)
			{
				$data[$res[1]]=$res[0];
			}
			elseif($webservice_data_type==2)
			{
				$data[$res[2]]=$res[0];
			}
			elseif($webservice_data_type==3 && mysqli_num_rows($check)==1)
			{
				$data[0]=$res[0];
			}
		}
	}
	return $data;
}
	
function send_mail($recipient_email, $cc, $sender_name, $subject, $message, $rootpath = null, $filename = null) {
	//Send E-Mail With Attachment	
	$from_email 	= 'noreply@dbtbharat.gov.in';
	$reply_to_email = 'noreply@dbtbharat.gov.in';
	$cc = 'feedback@dbtbharat.gov.in,bk.pujari@nic.in,'.$cc;
	if($filename){
		//Get uploaded file data
		$handle = fopen($rootpath.$filename, "r");
		$content = fread($handle, 2000000);
		fclose($handle);
		$encoded_content = chunk_split(base64_encode($content));
	}

	$boundary = md5("sanwebe");
	//header
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "From: ".$sender_name." <".$from_email."> \r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "Reply-To: ".$reply_to_email."" . "\r\n";
	$headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";
   
	//plain text
	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=utf-8\r\n";
	$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$body .= chunk_split(base64_encode($message));
   
	if($filename){
		// attachment
		$body .= "--$boundary\r\n";
		$body .="Content-Type: txt; name=".$filename."\r\n";
		$body .="Content-Disposition: attachment; filename=".$filename."\r\n";
		$body .="Content-Transfer-Encoding: base64\r\n";
		$body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n";
		$body .= $encoded_content;
	}
//	$sentMail = @mail($recipient_email,$subject, $body, $headers);
	$sentMail = "";
	return $sentMail;
}
function getyearformonth($datamonth,$s_year,$e_year){
	
	if($datamonth>3)
	{
		$datayear = $s_year;
	}
	else
	{
		$datayear = $e_year;
	}
	return $datayear;	
}
