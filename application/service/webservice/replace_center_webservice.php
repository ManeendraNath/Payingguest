<?php
error_reporting(E_ALL);
session_start();
require_once(realpath(__DIR__.'/../../configs/connection_mysqli_new.php'));
require_once(realpath(__DIR__.'/../common_functions.php'));
require_once(realpath(__DIR__.'/../replace_functions.php'));
$data_replacement_request_id = isset($_GET[data_replacement_request_id]) > 0 ? (int)$_GET[data_replacement_request_id] : 0;
$result = replace_center_web_service($data_replacement_request_id);
$data=json_encode($result);
echo $data;
die;

function replace_center_web_service($data_replacement_request_id)
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
		$sql=mysqli_query($con, "SELECT scheme_id, from_month, to_month, financial_year FROM dbt_data_replacement_request  WHERE data_replacement_request_id = $data_replacement_request_id and status = 'a' and data_replaced = 'v'");
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
			
			$sql=mysqli_query($con, "SELECT w.*, s.scheme_code, s.scheme_type_id FROM dbt_webservice_details as w join dbt_scheme_master as s on s.scheme_id = w.scheme_id WHERE w.scheme_id = $scheme_id");
			
			if(mysqli_num_rows($sql)<1)
			{
				$data[0] = 1;
				$data[1] = "webservice not found.";
			}
			else
			{
				$webservice_url_info=mysqli_fetch_object($sql);
				$webservice_data_type = $webservice_url_info->webservice_data_type_id;
				$scheme_type_id=$webservice_url_info->scheme_type_id;
				$temp_data_arr=gettempbeneficiary_transaction_data($scheme_id,$from_month,$to_month,$financialYear);
				
				$tempdatacount=count($temp_data_arr);
				
				if($tempdatacount>0)
				{
					$insertedcount=0;
					foreach($temp_data_arr AS $key=>$value)
					{
						$reporting_month=$value['reporting_month'];
						$state_code=$value['state_code'];
						if($reporting_month==$to_month)
						{
							$data_type="latest";
						}
						else
						{
							$data_type="previous";
						}
						
						$temp=delete_beneficiary_data($scheme_id,$reporting_month,$financialYear,$state_code,$data_type);
						$lastinsertedid=insert_beneficiary_data($scheme_id,$reporting_month,$financialYear,$state_code,$data_type);
						if($lastinsertedid>0)
						{
							$insertedcount++;
						}
						else
						{
							$data[0] = 1;
							$data[1] = "Some error occured in inserting beneficiary data";
							die("Some error occured in inserting beneficiary data");
						}
						
						
					}//loop end
					
					if($insertedcount==$tempdatacount)
					{
						$temp=update_replace_validation_status($data_replacement_request_id,"y");// update replace request table set data_replaced='y' (means data replaced)
						if($temp>0)
						{
							 $temp=deletetempdata($scheme_id,$from_month,$to_month,$financialYear);//delete from both temp table(benificiary data & transaction data) for the scheme_id, financialYear,reporting month between from_month and to_month	after successfully replacement of all data
							if($temp>0)
							{
								$data[0] = 0;
								$data[1] = "Data Replaced successfully";
							}
							else
							{
								$data[0] = 1;
								$data[1] = "Some error occured in temp data deletion";
							}
						}
						else
						{
							$data[0] = 1;
							$data[1] = "Some error occured in validation status updation";
						}
					}
					else
					{
						$data[0] = 1;
						$data[1] = "Some error occured ";
					}
				}
				else
				{
					$data[0] = 1;
					$data[1] = "Data in temp table is not found";
				}
			}
		}
	}
	return $data;
}
?>