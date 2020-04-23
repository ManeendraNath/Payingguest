<?php
require_once(realpath(__DIR__.'/sendmail.php'));
/*---(Web-service Functions According To New Database )---|
  |	 * Purpose:-Prevent data discrepancy                  |
  |-------------------------------------------------------|
*/

// get scheme id
function get_scheme_info($schemecode){
	global $con;
	$result = mysqli_query($con,"SELECT s.scheme_id as scheme_id, sd.scheme_name as scheme_name FROM dbt_scheme_master as s INNER JOIN dbt_scheme_details as sd on s.scheme_id = sd.scheme_id where s.scheme_code = '$schemecode' and s.scheme_status = 1");
	$schemeinfo = mysqli_fetch_object($result);
	return $schemeinfo;
}

// get web-service URL details
function get_webservice_url_details($execution_day,$api_owner,$time_val){
	global $con;
	if($api_owner == 0){
		$result = mysqli_query($con,"SELECT w.*, s.scheme_code FROM dbt_webservice_details as w join dbt_scheme_master as s on s.scheme_id = w.scheme_id where w.execution_day = '$execution_day' and w.webservice_schedule_status = 'S' and w.execution_time = '$time_val' and w.webservice_status = 1 and w.api_owner = 0");
	} else {
		$result = mysqli_query($con,"SELECT w.*, s.state_name FROM dbt_webservice_details as w join dbt_state_master as s on s.state_code = w.api_owner where w.execution_day = '$execution_day' and w.webservice_schedule_status = 'S' and w.execution_time = '$time_val' and w.webservice_status = 1 and w.api_owner > 0");
	}
	$webservice_url_details = array();
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

// process data for validation, log, insertion and updation
function insert_state_scheme_data($state_code, $responsedata, $txt, $datamonth, $datayear, $nodalp_email, $cc_email, $userid = null) {
	global $con;
	$ip_address = $_SERVER['REMOTE_ADDR'];
	if(empty($userid)){ $userid = 1; }
	$returnvalue = array();
	$errormsg = '';
	$is_updated = 'no';
	
	$day_val = 1;
	$month = intval($datamonth);
	$year = intval($datayear);
	if(intval($month) <= 3) {
		$s_year = intval($year) - 1;
		$e_year = $year;
	} else {
		$s_year = $year;
		$e_year = intval($year) + 1;
	}
	$financial_year = $s_year.'_'.$e_year;
	
	if($responsedata){
		$txt = dataCreateLog($responsedata, $txt);		//	generate log
		
		$validation_response = generalValidation($responsedata,$txt,$state_code,$datamonth,$s_year,$e_year, $datayear);
		if($validation_response['error_result']>0){
			$email_response = validation_error_email($validation_response['error'],$state_code,$datamonth,$s_year,$e_year,$nodalp_email,$cc_email);
			$txt .= $errormsg .= $validation_message = $validation_response['validation_message'];
			$error_msg_arr = $validation_response["err_msg"];
		} else {
			$error_msg_arr = '';
			foreach($responsedata as $data_val){
				$schemeinfo = get_scheme_info(trim($data_val['general-information']['schemecode']));
				$schemeid = isset($schemeinfo) ? $schemeinfo->scheme_id : 0;
				if($schemeid>0){
					$benefit_type_query = mysqli_query($con,"SELECT scheme_benefit_type_id FROM dbt_scheme_benefit_type_relation where scheme_id = '$schemeid'");
					$benefit_type = array();
					while($benefit_type_id = mysqli_fetch_object($benefit_type_query)){
						$benefit_type[] = $benefit_type_id->scheme_benefit_type_id;
					}
					// $state_code
					$check_data = mysqli_query($con,"SELECT * FROM dbt_state_scheme_beneficiary_data where state_code = $state_code and scheme_id = $schemeid and financial_year = '$financial_year'");
					$check_data_obj = mysqli_fetch_object($check_data);
					
					if(count($check_data_obj) == 0){
						// dbt_state_scheme_beneficiary_data data from web-service
						$ben_table_name = 'dbt_state_scheme_beneficiary_data';
						$insert_data = insert_update_webservice_data($data_val, $schemeid, $benefit_type, $ben_table_name, $userid, $state_code);
						
					} else if(count($check_data_obj) > 0){
						$data_month_avl = $check_data_obj->reporting_month;
						$data_date_avl = $check_data_obj->scheme_transaction_from_date;
						$data_month_date = $year.'-'.$month.'-'.$day_val.' 00:00:01';
						
						if(strtotime($data_month_date) == strtotime($data_date_avl)){
																					
							// dbt_state_scheme_beneficiary_data data from web-service
							$ben_table_name = 'dbt_state_scheme_beneficiary_data';
							$insert_data = insert_update_webservice_data($data_val, $schemeid, $benefit_type, $ben_table_name, $userid, $state_code);
							
						} else {
							$check_log_data = mysqli_query($con,"SELECT * FROM dbt_state_scheme_beneficiary_data_log1 where scheme_id = $schemeid and reporting_month = $month and financial_year = '$financial_year' and scheme_transaction_from_date = '$data_month_date'");			
							$check_log_data_obj = mysqli_fetch_object($check_log_data);
							if(isset($check_log_data_obj)){
								$data_month_avl = $check_log_data_obj->reporting_month;
								$data_date_avl = $check_log_data_obj->scheme_transaction_from_date;
							}
							
							if(count($check_log_data_obj) == 0 && strtotime($data_date_avl) < strtotime($data_month_date)){
								// dbt_state_scheme_beneficiary_data_log1 data from dbt_state_scheme_beneficiary_data
								$fetch_data = mysqli_query($con,"SELECT * FROM dbt_state_scheme_beneficiary_data where scheme_id = $schemeid and reporting_month = $data_month_avl and financial_year = '$financial_year' and scheme_transaction_from_date = '$data_date_avl' and state_code = '$state_code'");
								while($fetch_data_obj = mysqli_fetch_object($fetch_data)){
									
									$ben_query = "INSERT INTO dbt_state_scheme_beneficiary_data_log1 (scheme_id, state_code, no_beneficiaries_normative, no_beneficiaries_additional_state, no_beneficiaries_digitised, no_beneficiaries_aadhaar, no_beneficiaries_mobile, no_group, no_beneficiaries_ghost, no_beneficiaries_deduplicate, savings, other_savings,  beneficiary_data_log1_status, data_mode_id, scheme_transaction_from_date, scheme_transaction_to_date, reporting_month, financial_year, updated_by, created, updated, ip_address) VALUES ($fetch_data_obj->scheme_id, $state_code, '$fetch_data_obj->no_beneficiaries_normative', '$fetch_data_obj->no_beneficiaries_additional_state', '$fetch_data_obj->no_beneficiaries_digitised', '$fetch_data_obj->no_beneficiaries_aadhaar', '$fetch_data_obj->no_beneficiaries_mobile', '$fetch_data_obj->no_group','$fetch_data_obj->no_beneficiaries_ghost', '$fetch_data_obj->no_beneficiaries_deduplicate', '$fetch_data_obj->savings', '$fetch_data_obj->other_savings', '1', '2', '$fetch_data_obj->scheme_transaction_from_date', '$fetch_data_obj->scheme_transaction_to_date', '$fetch_data_obj->reporting_month', '$fetch_data_obj->financial_year', $userid, '$fetch_data_obj->created', '$fetch_data_obj->updated', '$ip_address')";
									$insert_ben_query = mysqli_query($con, $ben_query);
								}
								
								// delete data from dbt_state_scheme_beneficiary_data
								$delete_data = mysqli_query($con,"DELETE FROM dbt_state_scheme_beneficiary_data where scheme_id = $schemeid and state_code = $state_code and reporting_month = $data_month_avl and financial_year = '$financial_year' and scheme_transaction_from_date = '$data_date_avl'");
								
								// dbt_state_scheme_beneficiary_data data from web-service
								$ben_table_name = 'dbt_state_scheme_beneficiary_data';
								$insert_data = insert_update_webservice_data($data_val, $schemeid, $benefit_type, $ben_table_name, $userid, $state_code);
							
							}
														
						}
					}
					
				}
			
			} // foreach end
			$is_updated = 'yes';
		}
	} else {
		$txt .= $errormsg = 'Data not found!';
	} // end if

	$returnvalue[0] = $error_msg_arr;
	$returnvalue[1] = $txt;
	$returnvalue[2] = $validation_message;
	$returnvalue[3] = $errormsg;
	$returnvalue[4] = $is_updated;
	
	return $returnvalue;

} //insert_state_scheme_data end here


// Update web-service data insertion and updation
function insert_update_webservice_data($dataval, $schemeid, $benefit_type, $ben_table_name, $userid, $state_code)
{
	global $con;
	
	$day = $month = $year = '';
	$no_beneficiaries_normative = $no_beneficiaries_additional_state = $no_beneficiaries_digitised = $no_beneficiaries_aadhaar = $no_beneficiaries_mobile = $no_group = $no_beneficiaries_ghost =$no_beneficiaries_deduplicate = $savings = $other_savings = '';
	$total_fund_transferred_normative = $fund_transferred_center_normative = $fund_transferred_state_normative = $total_fund_transfered_state_additional_x = $total_fund_transfered_state_additional_y = $total_fund_electronic_authenticated = $total_fund_non_electronic_authenticated = $no_transaction_electronic_authenticated = $no_transaction_non_electronic_authenticated = $unit_of_measurement = $no_quantity = $remarks = '';
	
	$month = $dataval['scheme-progress']['month'];
	$year = $dataval['scheme-progress']['year'];
	if(isset($dataval['scheme-progress']['day'])){
		$day = $dataval['scheme-progress']['day'];
		$to_day = $dataval['scheme-progress']['day'];
	} else {
		$day = '01';
		$temp_date_val = $year.'-'.$month.'-01 00:00:01';
		$to_day = date('t', strtotime($temp_date_val));
	}
	
	//beneficiary-details
	if(isset($dataval['beneficiary-details']['total_no_of_beneficiaries']) && !empty($dataval['beneficiary-details']['total_no_of_beneficiaries'])){
		$no_beneficiaries_normative = $dataval['beneficiary-details']['total_no_of_beneficiaries'];
	}
	if(isset($dataval['beneficiary-details']['no_of_additional_beneficiaries_state_allocation']) && !empty($dataval['beneficiary-details']['no_of_additional_beneficiaries_state_allocation'])){
		$no_beneficiaries_additional_state = $dataval['beneficiary-details']['no_of_additional_beneficiaries_state_allocation'];
	}
	if(isset($dataval['beneficiary-details']['no_of_beneficiaries_record_digitized']) && !empty($dataval['beneficiary-details']['no_of_beneficiaries_record_digitized'])){
		$no_beneficiaries_digitised = $dataval['beneficiary-details']['no_of_beneficiaries_record_digitized'];
	}
	if(isset($dataval['beneficiary-details']['no_of_authenticated_seeded_beneficiaries']) && !empty($dataval['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'])){
		$no_beneficiaries_aadhaar = $dataval['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'];
	}
	if(isset($dataval['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured']) && !empty($dataval['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'])){
		$no_beneficiaries_mobile = $dataval['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'];
	}
	
	// fundtransfer-details and transaction-details
	if (in_array(1, $benefit_type)){
		// fundtransfer-details
		if(isset($dataval['fundtransfer-details']['total_fund_transferred_cash']) && !empty($dataval['fundtransfer-details']['total_fund_transferred_cash'])){
			$total_fund_transferred_normative = $dataval['fundtransfer-details']['total_fund_transferred_cash'];
		}
		if(isset($dataval['fundtransfer-details']['central_share_fund_transferred_cash']) && !empty($dataval['fundtransfer-details']['central_share_fund_transferred_cash'])){
			$fund_transferred_center_normative = $dataval['fundtransfer-details']['central_share_fund_transferred_cash'];
		}
		if(isset($dataval['fundtransfer-details']['fund_transferred_state_normative']) && !empty($dataval['fundtransfer-details']['fund_transferred_state_normative'])){
			$fund_transferred_state_normative = $dataval['fundtransfer-details']['fund_transferred_state_normative'];
		}
		if(isset($dataval['fundtransfer-details']['total_fund_transfered_state_additional_x']) && !empty($dataval['fundtransfer-details']['total_fund_transfered_state_additional_x'])){
			$total_fund_transfered_state_additional_x = $dataval['fundtransfer-details']['total_fund_transfered_state_additional_x'];
		}
		if(isset($dataval['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash']) && !empty($dataval['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'])){
			$total_fund_transfered_state_additional_y = $dataval['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'];
		}
		
		// transaction-details
		if(isset($dataval['transaction-details']['payment_electronic_modes_cash']) && !empty($dataval['transaction-details']['payment_electronic_modes_cash'])){
			$total_fund_electronic_authenticated = $dataval['transaction-details']['payment_electronic_modes_cash'];
		}
		if(isset($dataval['transaction-details']['payment_other_modes_cash']) && !empty($dataval['transaction-details']['payment_other_modes_cash'])){
			$total_fund_non_electronic_authenticated = $dataval['transaction-details']['payment_other_modes_cash'];
		}
		if(isset($dataval['transaction-details']['total_no_transactions_electronic_modes_cash']) && !empty($dataval['transaction-details']['total_no_transactions_electronic_modes_cash'])){
			$no_transaction_electronic_authenticated = $dataval['transaction-details']['total_no_transactions_electronic_modes_cash'];
		}
		if(isset($dataval['transaction-details']['total_no_transactions_other_modes_cash']) && !empty($dataval['transaction-details']['total_no_transactions_other_modes_cash'])){
			$no_transaction_non_electronic_authenticated = $dataval['transaction-details']['total_no_transactions_other_modes_cash'];
		}
	}
	if (in_array(2, $benefit_type)){
		// fundtransfer-details
		if(isset($dataval['fundtransfer-details']['total_expenditure_incurred_inkind']) && !empty($dataval['fundtransfer-details']['total_expenditure_incurred_inkind'])){
			$total_fund_transferred_normative = $dataval['fundtransfer-details']['total_expenditure_incurred_inkind'];
		}
		if(isset($dataval['fundtransfer-details']['central_share_expenditure_incurred_inkind']) && !empty($dataval['fundtransfer-details']['central_share_expenditure_incurred_inkind'])){
			$fund_transferred_center_normative = $dataval['fundtransfer-details']['central_share_expenditure_incurred_inkind'];
		}
		if(isset($dataval['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind']) && !empty($dataval['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'])){
			$fund_transferred_state_normative = $dataval['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'];
		}
		if(isset($dataval['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind']) && !empty($dataval['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'])){
			$total_fund_transfered_state_additional_x = $dataval['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'];
		}
		if(isset($dataval['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind']) && !empty($dataval['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'])){
			$total_fund_transfered_state_additional_y = $dataval['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'];
		}
		
		// transaction-details
		if(isset($dataval['transaction-details']['dbt_expenditure_incurred_inkind']) && !empty($dataval['transaction-details']['dbt_expenditure_incurred_inkind'])){
			$total_fund_electronic_authenticated = $dataval['transaction-details']['dbt_expenditure_incurred_inkind'];
		}
		if(isset($dataval['transaction-details']['no_of_authenticated_transactions_inkind']) && !empty($dataval['transaction-details']['no_of_authenticated_transactions_inkind'])){
			$total_fund_non_electronic_authenticated = $dataval['transaction-details']['no_of_authenticated_transactions_inkind'];
		}
		if(isset($dataval['transaction-details']['unit_of_measurement_inkind']) && !empty($dataval['transaction-details']['unit_of_measurement_inkind'])){
			$unit_of_measurement = $dataval['transaction-details']['unit_of_measurement_inkind'];
		}
		if(isset($dataval['transaction-details']['quantity_transferred_inkind']) && !empty($dataval['transaction-details']['quantity_transferred_inkind'])){
			$no_quantity = $dataval['transaction-details']['quantity_transferred_inkind'];
		}
	}
	
	// savings-details
	if(isset($dataval['savings-details']['savings']) && !empty($dataval['savings-details']['savings'])){
		$savings = $dataval['savings-details']['savings'];
	}
	if(isset($dataval['savings-details']['other_saving_process_improvement']) && !empty($dataval['savings-details']['other_saving_process_improvement'])){
		$other_savings = $dataval['savings-details']['other_saving_process_improvement'];
	}
	if(isset($dataval['savings-details']['no_of_ghost_fake_beneficiary']) && !empty($dataval['savings-details']['no_of_ghost_fake_beneficiary'])){
		$no_beneficiaries_ghost = $dataval['savings-details']['no_of_ghost_fake_beneficiary'];
	}
	if(isset($dataval['savings-details']['no_of_duplicate_record']) && !empty($dataval['savings-details']['no_of_duplicate_record'])){
		$no_beneficiaries_deduplicate = $dataval['savings-details']['no_of_duplicate_record'];
	}
	if(isset($dataval['savings-details']['remarks']) && !empty($dataval['savings-details']['remarks'])){
		$remarks = $dataval['savings-details']['remarks'];
	}
	
	
	// Set transaction data from date and to date 
	$scheme_transaction_from_date = $year.'-'.$month.'-'.$day.' 00:00:01';
	$scheme_transaction_to_date = $year.'-'.$month.'-'.$to_day.' 23:59:59';
	
	if(intval($month) <= 3) {
		$s_year = intval($year) - 1;
		$e_year = $year;
	} else {
		$s_year = $year;
		$e_year = intval($year) + 1;
	}
	$financial_year = $s_year.'_'.$e_year;
	
	$created = date('Y-m-d H:i:s');
	$updated = date('Y-m-d H:i:s');
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	
	$check_ben_query = "SELECT COUNT(*) as count FROM ".$ben_table_name." WHERE scheme_id = '$schemeid' and reporting_month = '$month' and financial_year = '$financial_year' and state_code = '$state_code'";
	$check_ben_query = mysqli_query($con, $check_ben_query);
	$check_ben_data_obj = mysqli_fetch_array($check_ben_query);
	
	if($ben_table_name == 'dbt_state_scheme_beneficiary_data_log1'){
		$ben_status_field_name = 'beneficiary_data_log1_status';
	} else {
		$ben_status_field_name = 'beneficiary_data_status';
	}

	// update beneficiary data
	$ben_query = "";
	if($check_ben_data_obj[0] == 0){
		$ben_query = "INSERT INTO ".$ben_table_name." SET ";
		$where="";
		$extra=", created = '$created', scheme_id = '$schemeid', state_code = $state_code, reporting_month = '$month', financial_year = '$financial_year'";
	} else {
		$ben_query = "UPDATE ".$ben_table_name." SET ";
		$extra="";
		$where = "WHERE scheme_id = '$schemeid' and reporting_month = '$month' and financial_year = '$financial_year' and state_code = '$state_code'";
	}
	$ben_query .= " no_beneficiaries_normative = '$no_beneficiaries_normative', no_beneficiaries_additional_state = '$no_beneficiaries_additional_state', no_beneficiaries_digitised = '$no_beneficiaries_digitised', no_beneficiaries_aadhaar = '$no_beneficiaries_aadhaar', no_beneficiaries_mobile = '$no_beneficiaries_mobile', no_group = '$no_group', no_beneficiaries_ghost = '$no_beneficiaries_ghost', no_beneficiaries_deduplicate = '$no_beneficiaries_deduplicate', savings = '$savings', other_savings = '$other_savings', $ben_status_field_name = '1', data_mode_id = '2', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', updated_by = '$userid', ip_address = '$ip_address' $extra $where ";
	mysqli_query($con, $ben_query);
		

	// Transaction data
	
	$data_query = "";
	if (in_array(1, $benefit_type)){
		$check_tr_cash_query = "SELECT COUNT(*) as count FROM dbt_state_scheme_beneficiary_b_typewise_data WHERE scheme_id = '$schemeid' and scheme_benefit_type_id = '1'  and state_code = '$state_code' and reporting_month = '$month' and financial_year = '$financial_year'";
		
		$check_tr_cash_query = mysqli_query($con, $check_tr_cash_query);
		$check_tr_cash_data_obj = mysqli_fetch_array($check_tr_cash_query);
		
		if($check_tr_cash_data_obj[0] == 0){
			$data_query = "INSERT INTO dbt_state_scheme_beneficiary_b_typewise_data SET ";
			$where="";
			$extra=", created = '$created', scheme_id = '$schemeid', scheme_benefit_type_id = '1', state_code = $state_code, reporting_month = '$month', financial_year = '$financial_year'";
		} else {
			$data_query = "UPDATE dbt_state_scheme_beneficiary_b_typewise_data SET ";
			$extra="";
			$where=" WHERE scheme_id = '$schemeid' and scheme_benefit_type_id = '1' and state_code = '$state_code' and reporting_month = '$month' and financial_year = '$financial_year'";
		}
		$data_query.=" total_fund_transferred_normative = '$total_fund_transferred_normative', fund_transferred_center_normative = '$fund_transferred_center_normative', fund_transferred_state_normative = '$fund_transferred_state_normative', total_fund_transfered_state_additional_x = '$total_fund_transfered_state_additional_x', total_fund_transfered_state_additional_y = '$total_fund_transfered_state_additional_y', total_fund_electronic_authenticated = '$total_fund_electronic_authenticated', total_fund_non_electronic_authenticated = '$total_fund_non_electronic_authenticated', no_transaction_electronic_authenticated = '$no_transaction_electronic_authenticated', no_transaction_non_electronic_authenticated = '$no_transaction_non_electronic_authenticated', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', b_typewise_data_status = '1', updated_by = '$userid', ip_address = '$ip_address' $extra $where";
		mysqli_query($con, $data_query);

	}
	
	if (in_array(2, $benefit_type)){
		$check_tr_cash_query = "SELECT COUNT(*) as count FROM dbt_state_scheme_beneficiary_b_typewise_data WHERE scheme_id = '$schemeid' and scheme_benefit_type_id = '2'  and state_code = '$state_code' and reporting_month = '$month' and financial_year = '$financial_year'";
		
		$check_tr_cash_query = mysqli_query($con, $check_tr_cash_query);
		$check_tr_cash_data_obj = mysqli_fetch_array($check_tr_cash_query);
		
		if($check_tr_cash_data_obj[0] == 0){
			$data_query = "INSERT INTO dbt_state_scheme_beneficiary_b_typewise_data SET ";
			$where="";
			$extra=", created = '$created', scheme_id = '$schemeid', scheme_benefit_type_id = '2', state_code = $state_code, reporting_month = '$month', financial_year = '$financial_year'";
		} else {
			$data_query = "UPDATE dbt_state_scheme_beneficiary_b_typewise_data SET ";
			$extra="";
			$where=" WHERE scheme_id = '$schemeid' and scheme_benefit_type_id = '2' and state_code = '$state_code' and reporting_month = '$month' and financial_year = '$financial_year'";
		}
		$data_query.=" total_fund_transferred_normative = '$total_fund_transferred_normative', fund_transferred_center_normative = '$fund_transferred_center_normative', fund_transferred_state_normative = '$fund_transferred_state_normative', total_fund_transfered_state_additional_x = '$total_fund_transfered_state_additional_x', total_fund_transfered_state_additional_y = '$total_fund_transfered_state_additional_y', total_fund_electronic_authenticated = '$total_fund_electronic_authenticated', unit_of_measurement = '$unit_of_measurement', no_transaction_electronic_authenticated = '$no_transaction_electronic_authenticated', no_quantity = '$no_quantity', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', b_typewise_data_status = '1', updated_by = '$userid', ip_address = '$ip_address' $extra $where";
		mysqli_query($con, $data_query);

	}
	
	$check_remarks_query = "SELECT COUNT(*) as count FROM dbt_scheme_info_monthwise WHERE scheme_id = '$schemeid' and reporting_month = '$month' and financial_year = '$financial_year'";
	$check_remarks_query = mysqli_query($con, $check_remarks_query);
	$check_remarks_data_obj = mysqli_fetch_array($check_remarks_query);

	// update beneficiary data
	$remarks_query = "";
	if($check_remarks_data_obj[0] == 0){
		$remarks_query = "INSERT INTO dbt_scheme_info_monthwise SET ";
		$where="";
		$extra=", created = '$created', scheme_id = '$schemeid', reporting_month = '$month', financial_year = '$financial_year'";
	} else {
		$remarks_query = "UPDATE dbt_scheme_info_monthwise SET ";
		$extra="";
		$where = "WHERE scheme_id = '$schemeid' and reporting_month = '$month' and financial_year = '$financial_year'";
	}
	$remarks_query .= " remarks = '$remarks', updated_by = '$userid', ip_address = '$ip_address' $extra $where ";
	mysqli_query($con, $remarks_query);
	
} // function insert_update_webservice_data



// data log and validation
function dataCreateLog($responsedata, $txt)
{
	global $con;
	$i = 1;
	foreach ($responsedata AS $data) {
		// Create Log
		$schemeinfo = get_scheme_info(trim($data['general-information']['schemecode']));
		$sid = 0;
		$txt .= "#: ".$i."\n";
		if(isset($schemeinfo->scheme_id)){
			$sid = $schemeinfo->scheme_id;		
			$benefit_type_query = mysqli_query($con,"SELECT scheme_benefit_type_id FROM dbt_scheme_benefit_type_relation where scheme_id = '$sid'");
			$benefit_type = array();
			while($benefit_type_id = mysqli_fetch_object($benefit_type_query)){
				$benefit_type[] = $benefit_type_id->scheme_benefit_type_id;
			}
			
			//general-information
			if($data['general-information']['schemecode']){
				$schemecode = trim($data['general-information']['schemecode']);
			} else {
				$schemecode = '';
			}
			if(isset($data['general-information']['onboarded_status_on_state_dbt_portal'])){
				$onboarded_status_on_state_dbt_portal = $data['general-information']['onboarded_status_on_state_dbt_portal'];
			} else {
				$onboarded_status_on_state_dbt_portal = '';
			}
			if($data['general-information']['scheme_mis_status']){
				$scheme_mis_status = $data['general-information']['scheme_mis_status'];
			} else {
				$scheme_mis_status = '';
			}
			if(isset($data['general-information']['mis_integration_status_with_state_dbt_portal'])){
				$mis_integration_status_with_state_dbt_portal = $data['general-information']['mis_integration_status_with_state_dbt_portal'];
			} else {
				$mis_integration_status_with_state_dbt_portal = '';
			}
			if($data['general-information']['central_allocation_for_state']){
				$central_allocation_for_state = $data['general-information']['central_allocation_for_state'];
			} else {
				$central_allocation_for_state = '';
			}
			if($data['general-information']['state_normative_allocation']){
				$state_normative_allocation = $data['general-information']['state_normative_allocation'];
			} else {
				$state_normative_allocation = '';
			}
			if($data['general-information']['additional_state_allocation']){
				$additional_state_allocation = $data['general-information']['additional_state_allocation'];
			} else {
				$additional_state_allocation = '';
			}
			if($data['general-information']['remarks']){
				$g_remarks = $data['general-information']['remarks'];
			} else {
				$g_remarks = '';
			}
			$txt .= "Scheme Name: ".$schemeinfo->scheme_name."\n";
			$txt .= "Scheme Code: ".$schemecode."\n";
			$txt .= "Onboarded Status On State DBT Portal: ".$onboarded_status_on_state_dbt_portal."\n";
			$txt .= "Scheme Mis Status: ".$scheme_mis_status."\n";
			$txt .= "Mis Integration Status with State DBT Portal: ".$mis_integration_status_with_state_dbt_portal."\n";
			$txt .= "Central Allocation for State: ".$central_allocation_for_state."\n";
			$txt .= "State Normative Allocation: ".$state_normative_allocation."\n";
			$txt .= "Additional State Allocation: ".$additional_state_allocation."\n";
			$txt .= "General Remarks: ".$g_remarks."\n\n";
			
			//scheme-progress
			if($data['scheme-progress']['year']){
				$year = $data['scheme-progress']['year'];
			} else {
				$year = '';
			}
			if($data['scheme-progress']['month']){
				$month = $data['scheme-progress']['month'];
			} else {
				$month = '';
			}
			if($data['scheme-progress']['requestid']){
				$requestid = $data['scheme-progress']['requestid'];
			} else {
				$requestid = '';
			}
			$txt .= "Year: ".$year."\n";
			$txt .= "Month: ".$month."\n";
			$txt .= "Requestid: ".$requestid."\n\n";
			
			//beneficiary-details
			if($data['beneficiary-details']['no_of_beneficiaries_central_allocation']){
				$no_of_beneficiaries_central_allocation = $data['beneficiary-details']['no_of_beneficiaries_central_allocation'];
			} else {
				$no_of_beneficiaries_central_allocation = 0;
			}
			if($data['beneficiary-details']['no_of_additional_beneficiaries_state_allocation']){
				$no_of_additional_beneficiaries_state_allocation = $data['beneficiary-details']['no_of_additional_beneficiaries_state_allocation'];
			} else {
				$no_of_additional_beneficiaries_state_allocation = 0;
			}
			if($data['beneficiary-details']['total_no_of_beneficiaries']){
				$total_no_of_beneficiaries = $data['beneficiary-details']['total_no_of_beneficiaries'];
			} else {
				$total_no_of_beneficiaries = 0;
			}
			if($data['beneficiary-details']['no_of_beneficiaries_record_digitized']){
				$no_of_beneficiaries_record_digitized = $data['beneficiary-details']['no_of_beneficiaries_record_digitized'];
			} else {
				$no_of_beneficiaries_record_digitized = 0;
			}
			if($data['beneficiary-details']['no_of_authenticated_seeded_beneficiaries']){
				$no_of_authenticated_seeded_beneficiaries = $data['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'];
			} else {
				$no_of_authenticated_seeded_beneficiaries = 0;
			}
			if($data['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured']){
				$no_of_beneficiaries_whom_mobile_no_captured = $data['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'];
			} else {
				$no_of_beneficiaries_whom_mobile_no_captured = 0;
			}
			
			$txt .= "No. of Beneficiaries Central Allocation: ".$no_of_beneficiaries_central_allocation."\n";
			$txt .= "No. of Beneficiaries State Allocation: ".$no_of_additional_beneficiaries_state_allocation."\n";
			$txt .= "Total No. of Beneficiaries: ".$total_no_of_beneficiaries."\n";
			$txt .= "No. of Beneficiaries Record Digitized: ".$no_of_beneficiaries_record_digitized."\n";
			$txt .= "No. of Authenticated Seeded Beneficiaries: ".$no_of_authenticated_seeded_beneficiaries."\n";
			$txt .= "No. of Beneficiaries Whom Mobile No. Captured: ".$no_of_beneficiaries_whom_mobile_no_captured."\n\n";
			
			//fundtransfer-details for cash
			if (in_array(1, $benefit_type)){
				if($data['fundtransfer-details']['central_share_fund_transferred_cash']){
					$central_share_fund_transferred_cash = $data['fundtransfer-details']['central_share_fund_transferred_cash'];
				} else {
					$central_share_fund_transferred_cash = 0;
				}
				if($data['fundtransfer-details']['normative_state_share_fund_transferred_cash']){
					$normative_state_share_fund_transferred_cash = $data['fundtransfer-details']['normative_state_share_fund_transferred_cash'];
				} else {
					$normative_state_share_fund_transferred_cash = 0;
				}
				if($data['fundtransfer-details']['additional_state_share_fund_transferred_cash']){
					$additional_state_share_fund_transferred_cash = $data['fundtransfer-details']['additional_state_share_fund_transferred_cash'];
				} else {
					$additional_state_share_fund_transferred_cash = 0;
				}
				if($data['fundtransfer-details']['total_fund_transferred_cash']){
					$total_fund_transferred_cash = $data['fundtransfer-details']['total_fund_transferred_cash'];
				} else {
					$total_fund_transferred_cash = 0;
				}
				if($data['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash']){
					$state_share_fund_transferred_to_additional_beneficiaries_cash = $data['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'];
				} else {
					$state_share_fund_transferred_to_additional_beneficiaries_cash = 0;
				}
				$txt .= "Central Share Fund Transferred cash: ".$central_share_fund_transferred_cash."\n";
				$txt .= "Normative State Share Fund Transferred cash: ".$normative_state_share_fund_transferred_cash."\n";
				$txt .= "Additional State Share Fund Transferred cash: ".$additional_state_share_fund_transferred_cash."\n";
				$txt .= "Total Fund Transferred cash: ".$total_fund_transferred_cash."\n";
				$txt .= "State Share Fund Transferred to Additional Beneficiaries cash: ".$state_share_fund_transferred_to_additional_beneficiaries_cash."\n";
			}
			
			// fundtransfer details for inkind 
			if(in_array(2, $benefit_type)){
				if($data['fundtransfer-details']['central_share_expenditure_incurred_inkind']){
					$central_share_expenditure_incurred_inkind = $data['fundtransfer-details']['central_share_expenditure_incurred_inkind'];
				} else {
					$central_share_expenditure_incurred_inkind = 0;
				}	
				if($data['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind']){
					$normative_state_share_expenditure_incurred_inkind = $data['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'];
				} else {
					$normative_state_share_expenditure_incurred_inkind = 0;
				}
				if($data['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind']){
					$additional_state_share_expenditure_incurred_inkind = $data['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'];
				} else {
					$additional_state_share_expenditure_incurred_inkind = 0;
				}
				if($data['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind']){
					$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = $data['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'];
				} else {
					$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = 0;
				}
				if($data['fundtransfer-details']['total_expenditure_incurred_inkind']){
					$total_expenditure_incurred_inkind = $data['fundtransfer-details']['total_expenditure_incurred_inkind'];
				} else {
					$total_expenditure_incurred_inkind = 0;
				}
				$txt .= "Central Share expenditure incurred inkind: ".$central_share_expenditure_incurred_inkind."\n";
				$txt .= "normative Share expenditure incurred inkind: ".$normative_state_share_expenditure_incurred_inkind."\n";
				$txt .= "additional Share share expenditure incurred inkind: ".$additional_state_share_expenditure_incurred_inkind."\n";
				$txt .= "state Share expenditure incurred to additional beneficiaries inkind: ".$state_share_expenditure_incurred_to_additional_beneficiaries_inkind."\n";
				$txt .= "total expenditure incurred inkind: ".$total_expenditure_incurred_inkind."\n";
			}
			
			//transaction-details
			if(in_array(1, $benefit_type)){
				if($data['transaction-details']['total_no_transactions_electronic_modes_cash']){
					$total_no_transactions_electronic_modes_cash = $data['transaction-details']['total_no_transactions_electronic_modes_cash'];
				} else {
					$total_no_transactions_electronic_modes_cash = 0;
				}
				if($data['transaction-details']['payment_electronic_modes_cash']){
					$payment_electronic_modes_cash = $data['transaction-details']['payment_electronic_modes_cash'];
				} else {
					$payment_electronic_modes_cash = 0;
				}
				if($data['transaction-details']['total_no_transactions_other_modes_cash']){
					$total_no_transactions_other_modes_cash = $data['transaction-details']['total_no_transactions_other_modes_cash'];
				} else {
					$total_no_transactions_other_modes_cash = 0;
				}
				if($data['transaction-details']['payment_other_modes_cash']){
					$payment_other_modes_cash = $data['transaction-details']['payment_other_modes_cash'];
				} else {
					$payment_other_modes_cash = 0;
				}
				$txt .= "No. of Electronic Mode Transactions cash: ".$total_no_transactions_electronic_modes_cash."\n";
				$txt .= "Electronic Mode Payment cash: ".$payment_electronic_modes_cash."\n";
				$txt .= "No. of Others Mode Transactions cash: ".$total_no_transactions_other_modes_cash."\n";
				$txt .= "Others Mode Payment cash: ".$payment_other_modes_cash."\n\n";
			}
			
			if(in_array(2, $benefit_type)){
				if($data['transaction-details']['unit_of_measurement_inkind']){
					$unit_of_measurement_inkind = $data['transaction-details']['unit_of_measurement_inkind'];
				} else {
					$unit_of_measurement_inkind = '';
				}
				if($data['transaction-details']['quantity_transferred_inkind']){
					$quantity_transferred_inkind = $data['transaction-details']['quantity_transferred_inkind'];
				} else {
					$quantity_transferred_inkind = '';
				}
				if($data['transaction-details']['dbt_expenditure_incurred_inkind']){
					$dbt_expenditure_incurred_inkind = $data['transaction-details']['dbt_expenditure_incurred_inkind'];
				} else {
					$dbt_expenditure_incurred_inkind = 0;
				}
				if($data['transaction-details']['no_of_authenticated_transactions_inkind']){
					$no_of_authenticated_transactions_inkind = $data['transaction-details']['no_of_authenticated_transactions_inkind'];
				} else {
					$no_of_authenticated_transactions_inkind = 0;
				}
				$txt .= "Unit of Measurement inkind: ".$unit_of_measurement_inkind."\n";
				$txt .= "Quantity Transferred inkind: ".$quantity_transferred_inkind."\n";
				$txt .= "dbt expenditure incurred inkind: ".$dbt_expenditure_incurred_inkind."\n";
				$txt .= "Total No. of Authenticated Transactions inkind: ".$no_of_authenticated_transactions_inkind."\n";
			}
			
			//savings-details
			if($data['savings-details']['no_of_duplicate_record']){
				$no_of_duplicate_record = $data['savings-details']['no_of_duplicate_record'];
			} else {
				$no_of_duplicate_record = 0;
			}
			if($data['savings-details']['no_of_ghost_fake_beneficiary']){
				$no_of_ghost_fake_beneficiary = $data['savings-details']['no_of_ghost_fake_beneficiary'];
			} else {
				$no_of_ghost_fake_beneficiary = 0;
			}
			if($data['savings-details']['other_saving_process_improvement']){
				$other_saving_process_improvement = $data['savings-details']['other_saving_process_improvement'];
			} else {
				$other_saving_process_improvement = 0;
			}
			if($data['savings-details']['savings']){
				$savings = $data['savings-details']['savings'];
			} else {
				$savings = 0;
			}
			if($data['savings-details']['remarks']){
				$remarks = $data['savings-details']['remarks'];
			} else {
				$remarks = '';
			}
			$txt .= "No. of Duplicate Record: ".$no_of_duplicate_record."\n";
			$txt .= "No. of Ghost Fake Beneficiary: ".$no_of_ghost_fake_beneficiary."\n";
			$txt .= "Other Saving Process Improvement: ".$other_saving_process_improvement."\n";
			$txt .= "Savings: ".$savings."\n";
			$txt .= "Remarks: ".$remarks."\n\n";
		} else {
			$schemecode = $data['general-information']['schemecode'];
			$txt .= "Scheme Code: ".$schemecode." is Inactive or Not available on DBT Bharat Portal \n";
		}
		
		$i++;
	} // end foreach

	return $txt;
	
}


function generalValidation($responsedata,$txt,$state_code,$datamonth,$start,$end,$datayear)
{
	global $con;
	$i=1;
	$error_report_array = array();
	
	$err_flag=0;
	foreach($responsedata as $key=>$val){
		$no_of_beneficiaries_central_allocation = $no_of_additional_beneficiaries_state_allocation = $total_no_of_beneficiaries = $no_of_beneficiaries_record_digitized = $no_of_authenticated_seeded_beneficiaries = $no_of_beneficiaries_whom_mobile_no_captured = '';
		$check_s_code = substr(trim($val['general-information']['schemecode']),0,1);
		$schemeinfo = get_scheme_info(trim($val['general-information']['schemecode']));
		$sid = 0;
		if(isset($schemeinfo->scheme_id)){
			$sid = $schemeinfo->scheme_id;		
			$benefit_type_query = mysqli_query($con,"SELECT scheme_benefit_type_id FROM dbt_scheme_benefit_type_relation where scheme_id = '$sid'");
			$benefit_type = array();
			while($benefit_type_id = mysqli_fetch_object($benefit_type_query)){
				$benefit_type[] = $benefit_type_id->scheme_benefit_type_id;
			}
			
			if(!is_array($val['beneficiary-details']['no_of_beneficiaries_central_allocation'])){
				$no_of_beneficiaries_central_allocation = $val['beneficiary-details']['no_of_beneficiaries_central_allocation'];
			} else {
				$no_of_beneficiaries_central_allocation = '';
			}
			if(!is_array($val['beneficiary-details']['no_of_additional_beneficiaries_state_allocation'])){
				$no_of_additional_beneficiaries_state_allocation = $val['beneficiary-details']['no_of_additional_beneficiaries_state_allocation'];
			} else {
				$no_of_additional_beneficiaries_state_allocation = '';
			}
			if(!is_array($val['beneficiary-details']['total_no_of_beneficiaries'])){
				$total_no_of_beneficiaries = $val['beneficiary-details']['total_no_of_beneficiaries'];
			} else {
				$total_no_of_beneficiaries = '';
			}
			if(!is_array($val['beneficiary-details']['no_of_beneficiaries_record_digitized'])){
				$no_of_beneficiaries_record_digitized = $val['beneficiary-details']['no_of_beneficiaries_record_digitized'];
			} else {
				$no_of_beneficiaries_record_digitized = '';
			}
			if(!is_array($val['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'])){
				$no_of_authenticated_seeded_beneficiaries = $val['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'];
			} else {
				$no_of_authenticated_seeded_beneficiaries = '';
			}
			if(!is_array($val['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'])){
				$no_of_beneficiaries_whom_mobile_no_captured = $val['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'];
			} else {
				$no_of_beneficiaries_whom_mobile_no_captured = '';
			}
			
			// start cash
			if(in_array(1, $benefit_type)){
				// fundtransfer-details
				if(!is_array($val['fundtransfer-details']['central_share_fund_transferred_cash'])){
				$central_share_fund_transferred_cash = $val['fundtransfer-details']['central_share_fund_transferred_cash'];
				} else {
					$central_share_fund_transferred_cash = '';
				}
				if(!is_array($val['fundtransfer-details']['normative_state_share_fund_transferred_cash'])){
				$normative_state_share_fund_transferred_cash = $val['fundtransfer-details']['normative_state_share_fund_transferred_cash'];
				} else {
					$normative_state_share_fund_transferred_cash = '';
				}
				if(!is_array($val['fundtransfer-details']['additional_state_share_fund_transferred_cash'])){
				$additional_state_share_fund_transferred_cash = $val['fundtransfer-details']['additional_state_share_fund_transferred_cash'];
				} else {
					$additional_state_share_fund_transferred_cash = '';
				}
				if(!is_array($val['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'])){
				$state_share_fund_transferred_to_additional_beneficiaries_cash = $val['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'];
				} else {
					$state_share_fund_transferred_to_additional_beneficiaries_cash = '';
				}
				if(!is_array($val['fundtransfer-details']['total_fund_transferred_cash'])){
				$total_fund_transferred_cash = $val['fundtransfer-details']['total_fund_transferred_cash'];
				} else {
					$total_fund_transferred_cash = '';
				}
				
				// transaction-details
				if(!is_array($val['transaction-details']['total_no_transactions_electronic_modes_cash'])){
				$total_no_transactions_electronic_modes_cash = $val['transaction-details']['total_no_transactions_electronic_modes_cash'];
				} else {
					$total_no_transactions_electronic_modes_cash = '';
				}
				if(!is_array($val['transaction-details']['payment_electronic_modes_cash'])){
				$payment_electronic_modes_cash = $val['transaction-details']['payment_electronic_modes_cash'];
				} else {
					$payment_electronic_modes_cash = '';
				}
				if(!is_array($val['transaction-details']['total_no_transactions_other_modes_cash'])){
				$total_no_transactions_other_modes_cash = $val['transaction-details']['total_no_transactions_other_modes_cash'];
				} else {
					$total_no_transactions_other_modes_cash = '';
				}
				if(!is_array($val['transaction-details']['payment_other_modes_cash'])){
				$payment_other_modes_cash = $val['transaction-details']['payment_other_modes_cash'];
				} else {
					$payment_other_modes_cash = '';
				}
			} // end cash
			
			// start in-kind
			if(in_array(2, $benefit_type)){
				// fundtransfer-details
				if(!is_array($val['fundtransfer-details']['central_share_expenditure_incurred_inkind'])){
				$central_share_expenditure_incurred_inkind = $val['fundtransfer-details']['central_share_expenditure_incurred_inkind'];
				} else {
					$central_share_expenditure_incurred_inkind = '';
				}
				if(!is_array($val['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'])){
				$normative_state_share_expenditure_incurred_inkind = $val['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'];
				} else {
					$normative_state_share_expenditure_incurred_inkind = '';
				}
				if(!is_array($val['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'])){
				$additional_state_share_expenditure_incurred_inkind = $val['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'];
				} else {
					$additional_state_share_expenditure_incurred_inkind = '';
				}
				if(!is_array($val['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'])){
				$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = $val['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'];
				} else {
					$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = '';
				}
				if(!is_array($val['fundtransfer-details']['total_expenditure_incurred_inkind'])){
				$total_expenditure_incurred_inkind = $val['fundtransfer-details']['total_expenditure_incurred_inkind'];
				} else {
					$total_expenditure_incurred_inkind = '';
				}
				
				// transaction-details
				if(!is_array($val['transaction-details']['no_of_authenticated_transactions_inkind'])){
				$no_of_authenticated_transactions_inkind = $val['transaction-details']['no_of_authenticated_transactions_inkind'];
				} else {
					$no_of_authenticated_transactions_inkind = '';
				}
				if(!is_array($val['transaction-details']['dbt_expenditure_incurred_inkind'])){
				$dbt_expenditure_incurred_inkind = $val['transaction-details']['dbt_expenditure_incurred_inkind'];
				} else {
					$dbt_expenditure_incurred_inkind = '';
				}
			} // end in-kind
			
			// check validation
			
			// Check Data Duration
			if(intval($datamonth) != intval($val['scheme-progress']['month']) && $err_flag==0) {
				$error++;
				$res[1]['error'][$val['general-information']['schemecode']]['error'][$error] = array(
					'label'=>'Month not matched : '.$val['scheme-progress']['month'].' &#8800; '.$datamonth,
					'values'=>'Responce data month not matched for requested data month'
				);
				$err_flag=1;
			}
			elseif(intval($datayear) != intval($val['scheme-progress']['year']) && $err_flag==0) {
				$error++;
				$res[1]['error'][$val['general-information']['schemecode']]['error'][$error] = array(
					'label'=>'Year not matched : '.$val['scheme-progress']['year'].' &#8800; '.$datayear,
					'values'=>'Responce data year not matched for requested data year'
				);
				$err_flag=1;
			}
	
			// start beneficiaries validation check
			elseif(intval($no_of_beneficiaries_central_allocation) && $no_of_beneficiaries_central_allocation < 0 && $err_flag==0) 
			{
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'No of Beneficiaries Central Allocation : '.$no_of_beneficiaries_central_allocation,
				'values'=>'Should not be negative(-ve). Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_additional_beneficiaries_state_allocation) && $no_of_additional_beneficiaries_state_allocation < 0 && $err_flag==0) 
			{
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'No of Additional Beneficiaries State Allocation : '.$no_of_additional_beneficiaries_state_allocation,
				'values'=>'Should not be negative(-ve). Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed',
				);
				$err_flag=1;
			}
			elseif(intval($total_no_of_beneficiaries) && $total_no_of_beneficiaries < 0 && $err_flag==0) 
			{
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'Total No of Beneficiaries : '.$total_no_of_beneficiaries,
				'values'=>'Should not be negative(-ve). Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_beneficiaries_record_digitized) && $no_of_beneficiaries_record_digitized < 0 && $err_flag==0) 
			{
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'No of Beneficiaries Record Digitized : '.$no_of_beneficiaries_record_digitized,
				'values'=>'Should not be negative(-ve). Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_authenticated_seeded_beneficiaries) && $no_of_authenticated_seeded_beneficiaries < 0 && $err_flag==0) 
			{
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'No of Authenticated Seeded Beneficiaries : '.$no_of_authenticated_seeded_beneficiaries,
				'values'=>'Should not be negative(-ve). Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_beneficiaries_whom_mobile_no_captured) && $no_of_beneficiaries_whom_mobile_no_captured < 0 && $err_flag==0) 
			{
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'No of Beneficiaries Whom Mobile No Captured : '.$no_of_beneficiaries_whom_mobile_no_captured,
				'values'=>'Should not be negative(-ve). Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_beneficiaries_record_digitized) > intval($total_no_of_beneficiaries) && $err_flag==0){
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'Total no of beneficiaries : '.intval($total_no_of_beneficiaries).' >= '.intval($no_of_beneficiaries_record_digitized),
				'values'=>'Total number of beneficiaries should be greater than or equal to number of beneficiary records digitized',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_authenticated_seeded_beneficiaries) > intval($total_no_of_beneficiaries) && $err_flag==0){
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'Total no of beneficiaries : '.intval($total_no_of_beneficiaries).' >= '.intval($no_of_authenticated_seeded_beneficiaries),
				'values'=>'Total number of beneficiaries should be greater than or equal to number of authenticated seeded beneficiaries',
				);
				$err_flag=1;
			}
			elseif(intval($no_of_beneficiaries_whom_mobile_no_captured) > intval($total_no_of_beneficiaries) && $err_flag==0){
				$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'Total no of beneficiaries : '.intval($total_no_of_beneficiaries).' >= '.intval($no_of_beneficiaries_whom_mobile_no_captured),
				'values'=>'Total number of beneficiaries should be greater than or equal to number of beneficiaries whom mobile number captured',
				);
				$err_flag=1;
			}
			elseif(($total_no_of_beneficiaries || $no_of_beneficiaries_central_allocation || $no_of_additional_beneficiaries_state_allocation) && $err_flag==0){
				if($check_s_code == 'E'){
					$check_total_no_of_beneficiaries = 0;
					$check_total_no_of_beneficiaries = trim(($no_of_beneficiaries_central_allocation) ? $no_of_beneficiaries_central_allocation :0) + trim(($no_of_additional_beneficiaries_state_allocation) ? $no_of_additional_beneficiaries_state_allocation : 0);
					if($total_no_of_beneficiaries != $check_total_no_of_beneficiaries){
						$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
							'label'=>'Total no of beneficiaries : '.$total_no_of_beneficiaries.' &#8800; '.$check_total_no_of_beneficiaries,
							'values'=>'Total no of beneficiaries should be equal to sum of No of Beneficiaries Central Allocation and No of Additional Beneficiaries State Allocation',
						);
						$err_flag=1;
					}
				}
			}
			
			
			// end beneficiaries validation check
			
			// start transaction validation check
			if(in_array(1, $benefit_type) && $err_flag==0){
				if($central_share_fund_transferred_cash && $central_share_fund_transferred_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Central Share Fund Transferred Cash : '.$central_share_fund_transferred_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($normative_state_share_fund_transferred_cash && $normative_state_share_fund_transferred_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Normative State Share Fund Transferred Cash : '.$normative_state_share_fund_transferred_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($additional_state_share_fund_transferred_cash && $additional_state_share_fund_transferred_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Additional State Share Fund Transferred Cash : '.$additional_state_share_fund_transferred_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($state_share_fund_transferred_to_additional_beneficiaries_cash && $state_share_fund_transferred_to_additional_beneficiaries_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'State Share Fund Transferred To Additional Beneficiaries Cash : '.$state_share_fund_transferred_to_additional_beneficiaries_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
				}
				elseif($total_fund_transferred_cash && $total_fund_transferred_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Total Fund Transferred Cash : '.$total_fund_transferred_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($total_fund_transferred_cash || $central_share_fund_transferred_cash || $normative_state_share_fund_transferred_cash || $additional_state_share_fund_transferred_cash || $state_share_fund_transferred_to_additional_beneficiaries_cash){
					if($check_s_code == 'E'){
						$check_total_fund_transferred_cash = 0;
						$check_total_fund_transferred_cash = trim(($central_share_fund_transferred_cash) ? $central_share_fund_transferred_cash : 0) + trim(($normative_state_share_fund_transferred_cash) ? $normative_state_share_fund_transferred_cash : 0) + trim(($additional_state_share_fund_transferred_cash) ? $additional_state_share_fund_transferred_cash : 0) + trim(($state_share_fund_transferred_to_additional_beneficiaries_cash) ? $state_share_fund_transferred_to_additional_beneficiaries_cash : 0);
						if($check_total_fund_transferred_cash != $total_fund_transferred_cash){
							$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
								'label'=>'Total fund transfred cash : '.$total_fund_transferred_cash.' &#8800; '.$check_total_fund_transferred_cash,
								'values'=>'Total fund transfred cash should be equal to sum of Central share fund transferred cash and Normative state share fund transferred cash and Additional state share fund transferred cash and State share fund transferred to additional beneficiaries cash',
							);
							$err_flag=1;
						}
					}
				}
				
				elseif($total_no_transactions_electronic_modes_cash && $total_no_transactions_electronic_modes_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Total No Transactions Electronic Modes Cash : '.$total_no_transactions_electronic_modes_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($payment_electronic_modes_cash && $payment_electronic_modes_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Payment Electronic Modes Cash : '.$payment_electronic_modes_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($total_no_transactions_other_modes_cash && $total_no_transactions_other_modes_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Total No Transactions Other Modes Cash : '.$total_no_transactions_other_modes_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($payment_other_modes_cash && $payment_other_modes_cash < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Payment Other Modes Cash : '.$payment_other_modes_cash,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				
				elseif($total_fund_transferred_cash || $payment_electronic_modes_cash || $payment_other_modes_cash){
					$check_total_fund_transferred_cash = 0;
					$check_total_fund_transferred_cash = trim(($payment_electronic_modes_cash) ? $payment_electronic_modes_cash : 0) + trim(($payment_other_modes_cash) ? $payment_other_modes_cash : 0);
					if($check_total_fund_transferred_cash != $total_fund_transferred_cash){
						$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
						'label'=>'Total fund transfred : '.$total_fund_transferred_cash.' &#8800; '.$check_total_fund_transferred_cash,
						'values'=>'Total fund transfred cash should be equal to sum of Payment electronic mode cash and Payment other modes cash',
						);
						$err_flag=1;
					}
				}
				
			}
			if(in_array(2, $benefit_type) && $err_flag==0){
				if($central_share_expenditure_incurred_inkind && $central_share_expenditure_incurred_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Central Share Expenditure Incurred Inkind : '.$central_share_expenditure_incurred_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($normative_state_share_expenditure_incurred_inkind && $normative_state_share_expenditure_incurred_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Normative State Share Expenditure Incurred Inkind : '.$normative_state_share_expenditure_incurred_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($additional_state_share_expenditure_incurred_inkind && $additional_state_share_expenditure_incurred_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Additional State Share Expenditure Incurred Inkind : '.$additional_state_share_expenditure_incurred_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($state_share_expenditure_incurred_to_additional_beneficiaries_inkind && $state_share_expenditure_incurred_to_additional_beneficiaries_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'State Share Expenditure Incurred To Additional Beneficiaries Inkind : '.$state_share_expenditure_incurred_to_additional_beneficiaries_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($total_expenditure_incurred_inkind && $total_expenditure_incurred_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'Total Expenditure Incurred Inkind : '.$total_expenditure_incurred_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($total_expenditure_incurred_inkind || $central_share_expenditure_incurred_inkind || $normative_state_share_expenditure_incurred_inkind || $additional_state_share_expenditure_incurred_inkind || $state_share_expenditure_incurred_to_additional_beneficiaries_inkind){
					if($check_s_code == 'E'){
						$check_total_expenditure_incurred_inkind = 0;
						$check_total_expenditure_incurred_inkind = trim(($central_share_expenditure_incurred_inkind) ? $central_share_expenditure_incurred_inkind : 0) + trim(($normative_state_share_expenditure_incurred_inkind) ? $normative_state_share_expenditure_incurred_inkind : 0) + trim(($additional_state_share_expenditure_incurred_inkind) ? $additional_state_share_expenditure_incurred_inkind : 0) + trim(($state_share_expenditure_incurred_to_additional_beneficiaries_inkind) ? $state_share_expenditure_incurred_to_additional_beneficiaries_inkind : 0);
						if($total_expenditure_incurred_inkind != $check_total_expenditure_incurred_inkind){
							$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
								'label'=>'Total expenditure incurred inkind : '.$total_expenditure_incurred_inkind.' &#8800; '.$check_total_expenditure_incurred_inkind,
								'values'=>'Total expenditure incurred inkind should be equal to sum of Central share expenditure incurred inkind and Normative state share expenditure incurred inkind and Additional state share expenditure incurred inkind and State share expenditure incurred to additional beneficiaries inkind',
							);
							$err_flag=1;
						}
					}
				}
				
				elseif($no_of_authenticated_transactions_inkind && $no_of_authenticated_transactions_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'No of Authenticated Transactions Inkind : '.$no_of_authenticated_transactions_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				elseif($dbt_expenditure_incurred_inkind && $dbt_expenditure_incurred_inkind < 0) 
				{
					$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
					'label'=>'DBT Expenditure Incurred Inkind : '.$dbt_expenditure_incurred_inkind,
					'values'=>'Should not be negative(-ve). Also should not contain any alphabet character, only positive(+ve) numeric value is allowed',
					);
					$err_flag=1;
				}
				
				elseif($total_expenditure_incurred_inkind || $dbt_expenditure_incurred_inkind){
					$check_total_expenditure_incurred_inkind = trim(($total_expenditure_incurred_inkind) ? $total_expenditure_incurred_inkind : 0);
					$check_dbt_expenditure_incurred_inkind = trim(($dbt_expenditure_incurred_inkind) ? $dbt_expenditure_incurred_inkind : 0);
					if($check_total_expenditure_incurred_inkind < $check_dbt_expenditure_incurred_inkind){
						$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
						'label'=>'Total Expenditure : '.$check_total_expenditure_incurred_inkind.' < '.$check_dbt_expenditure_incurred_inkind,
						'values'=>'Total expenditure should be greater than equal to Total DBT expenditure',
						);
						$err_flag=1;
					}
				}
			}
			// end transaction validation check
			
			// cumulative check start
			$data_month = intval($datamonth);
			$prev_month_data_array = array();
			if($data_month != 4 && $err_flag==0){
				global $con;
				$fy_year = $start.'_'.$end;
				$result = mysqli_query($con,"SELECT SUM(no_beneficiaries_normative) as total_no_beneficiaries_normative, SUM(no_beneficiaries_digitised) as total_no_beneficiaries_digitised, SUM(no_beneficiaries_aadhaar) as total_no_beneficiaries_aadhaar, SUM(no_beneficiaries_mobile) as total_no_beneficiaries_mobile, reporting_month FROM dbt_state_scheme_beneficiary_data where scheme_id = $sid and financial_year = '$fy_year' group by reporting_month");				
				while($ben_data_res = mysqli_fetch_object($result)){
					$prev_month_data_array[$ben_data_res->reporting_month] = array(
						'total_no_beneficiaries_normative' => $ben_data_res->total_no_beneficiaries_normative,
						'total_no_beneficiaries_digitised' => $ben_data_res->total_no_beneficiaries_digitised,
						'total_no_beneficiaries_aadhaar' => $ben_data_res->total_no_beneficiaries_aadhaar,
						'total_no_beneficiaries_mobile' => $ben_data_res->total_no_beneficiaries_mobile
					);
				}
				
				$result_log = mysqli_query($con,"SELECT SUM(no_beneficiaries_normative) as total_no_beneficiaries_normative, SUM(no_beneficiaries_digitised) as total_no_beneficiaries_digitised, SUM(no_beneficiaries_aadhaar) as total_no_beneficiaries_aadhaar, SUM(no_beneficiaries_mobile) as total_no_beneficiaries_mobile, reporting_month FROM dbt_state_scheme_beneficiary_data_log1 where scheme_id = $sid and financial_year = '$fy_year' group by reporting_month");
				while($ben_log_data_res = mysqli_fetch_object($result_log)){
					$prev_month_data_array[$ben_log_data_res->reporting_month] = array(
						'total_no_beneficiaries_normative' => $ben_log_data_res->total_no_beneficiaries_normative,
						'total_no_beneficiaries_digitised' => $ben_log_data_res->total_no_beneficiaries_digitised,
						'total_no_beneficiaries_aadhaar' => $ben_log_data_res->total_no_beneficiaries_aadhaar,
						'total_no_beneficiaries_mobile' => $ben_log_data_res->total_no_beneficiaries_mobile
					);
				}
				krsort($prev_month_data_array);
				if($data_month < 4){
					$year = $end;
				} else {
					$year = $start;
				}
				$data_month_day = '1-'.$data_month.'-'.$year;
				if($prev_month_data_array){
					$check_data_arr = array();
					for($j=1;$j<=12;$j++){
						$check_month = intval(date("m", strtotime("$data_month_day -1 month")));
						if(isset($prev_month_data_array[$check_month])){
							$check_data_arr = $prev_month_data_array[$check_month];
						}
						if($check_data_arr){
							$j=15;
						} else {
							$data_month_day = date("d-m-Y", strtotime("$data_month_day -1 month"));
						}
					} // end for
					
					if($check_data_arr){
						if($check_data_arr['total_no_beneficiaries_normative']>intval($total_no_of_beneficiaries))
						{											
							$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
						   'label'=>'Total No of Beneficiary : '.$total_no_of_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_normative'],
						   
							'values'=>'Total no of beneficiary should be greater than or equal to last reported month total beneficiary for this financial year',
							);
							$err_flag=1;							
						}
						elseif($check_data_arr['total_no_beneficiaries_digitised']>intval($no_of_beneficiaries_record_digitized))
						{											
							$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
						   'label'=>'Total No of Digitised Beneficiary : '.$no_of_beneficiaries_record_digitized.' < '.$check_data_arr['total_no_beneficiaries_digitised'],
						   
							'values'=>'Total no of digitised beneficiary should be greater than or equal to last reported month total digitised beneficiary for this financial year',
							);	
							$err_flag=1;
						}
						elseif($check_data_arr['total_no_beneficiaries_aadhaar']>intval($no_of_authenticated_seeded_beneficiaries))
						{											
							$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
						   'label'=>'Total No of Aadhaar Beneficiary : '.$no_of_authenticated_seeded_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_aadhaar'],
						   
							'values'=>'Total no of aadhaar beneficiary should be greater than or equal to last reported month total aadhaar beneficiary for this financial year',
							);		
							$err_flag=1;
						}
						elseif($check_data_arr['total_no_beneficiaries_mobile']>intval($no_of_beneficiaries_whom_mobile_no_captured))
						{											
							$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
						   'label'=>'Total No of Beneficiary Whome Mobile No Captured : '.$no_of_beneficiaries_whom_mobile_no_captured.' < '.$check_data_arr['total_no_beneficiaries_mobile'],
						   
							'values'=>'Total no of beneficiary whome mobile no captured should be greater than or equal to last reported month total beneficiary whome mobile no captured for this financial year',
							);		
							$err_flag=1;
						}
					}
				} // end prev month data check
			} // end data month check
			// end of cumulative check
			
		} else {
			$error_report_array[$val['general-information']['schemecode']]['error'][$i++]=array(
				'label'=>'Scheme Not Found : '.(empty($val['general-information']['schemecode']) ? '': $val['general-information']['schemecode']),
				'values'=>'Scheme Code '.$val['general-information']['schemecode'].' Not Found On DBT Portal',
				);
			$err_flag=1;
		}
		
	} // end foreach
	
	$error = 0;
	$returnvalue = array();
	foreach($error_report_array as $key_error =>$val_error){
		if(count($val_error['error']) > 0){
			$error++;
		}
		foreach($val_error["error"] AS $e)
		{
			$err_msg = $e["values"];
		}
	}
	
	$returnvalue['error_result'] = $error;
	
	if($error > 0 ) {
		$returnvalue['error'] = $error_report_array;
		$returnvalue['validation_message'] = 'Web-service validation failed';
		$returnvalue['err_msg'] = $err_msg;
	} 
	else {
		$returnvalue['error'] = '';
		$returnvalue['validation_message'] = '';
	}
	return $returnvalue;
	
} // end of generalValidation


//Send validation error email
function validation_error_email ($error_report_array,$state_code,$datamonth,$start,$end,$nodalp_email,$cc_email){
	
	global $con;	
	$msg_body = '';	
	
	$datainfo = mysqli_query($con,"SELECT state_name FROM dbt_state_master where state_code = '".$state_code."'");
	
	$datainfocount = mysqli_fetch_object($datainfo);
	$nodalp_email = implode(',', $nodalp_email); 
	$cc_email = implode(',', $cc_email); 
	$state_name = $datainfocount->state_name;
	$data_updated = date('d/m/Y h:i:s a');

	$month_num = $datamonth;
	$month_name = date("M", mktime(0, 0, 0, $month_num, 10));
	$year_name = date("Y");
	if(intval($month_num)<=3){$f_year= $end;} else{$f_year= $start;}


	$subject = "DBT Web-service rejection alert: ".$state_name.", ".$month_name."-".$f_year; 

	$message = '<span style="font-size:14px">Dear User,
	<br>Web-service response for below mentioned month has been rejected by DBT Bharat Portal. Please find the web-service response validation alert details updated on '.$data_updated.'.</span><br><br>';
	
	$message .= '<table style="font-family:Trebuchet MS, Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;">';
	$message .= '<tr><td width="52%" style="border: 1px solid #ddd;padding: 8px;"><b>State Name</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$state_name.'</td></tr>';
	$message .= '<tr><td style="border: 1px solid #ddd;padding: 8px;"><b>Month, Financial Year</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$month_name.', '.$start.'-'.substr($end,2,7).'</td></tr>';	
	$message .='</table><br>';
	$msg_body .= "<table style='border-collapse: collapse;width: 100%;'><tr style='border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;color: white;background-color:#999999'><th style='border: 1px solid #ddd; font-size:14px'>Scheme Code</th><th style='border: 1px solid #ddd; font-size:14px'> WEB-SERVICE RESPONSE VALIDATION DETAILS </th></tr>";
		
	foreach($error_report_array as $key_error =>$val_error){
		// echo $key_error;
		if(count($val_error['error']) > 0){
			 $response = true; 
			$msg_body .='<tr><td style="text-align:center;border: 1px solid #c3c2c2;padding: 8px;background-color:#ddd"><b>'.$key_error.'</b></td><td  style="border: 1px solid #c3c2c2;padding: 8px; background-color: #ddd;" ><b>COMMENTS FOR CORRECTION</b></td></tr>'; 
				foreach($val_error as $key_error1 =>$val_error1){
					if(is_array($val_error1)){
						foreach($val_error1 as $key_error2 =>$val_error2){
							$msg_body .='<tr>';
							$msg_body .='<td  style="border: 1px solid #ddd;padding: 8px;">'.$val_error2['label'].'</td>'; 
							$msg_body .='<td style="border: 1px solid #ddd;padding: 8px;">'.$val_error2['values'].'</td>';
							$msg_body .='</tr>';
						}
					}
			}
		}	
	}

	$msg_body .='</table>';

    $msg_body .= '<span style="font-size:14px"><br>You are requested to take corrective action and confirm DBT Mission Technical Team for submitting the data through manual web-service mode.<br>We recommend to implement similar validations at your end to avoid rejection. <br><br>Thanks and Regards,<br>DBT Mission<br> DBT helpline: 011-2374-0715<br> Mail-ID: feedback@dbtbharat.gov.in <br><br>[This is a system generated email]</span>';
	$body =$message.$msg_body;
	
	$recipient_email    = $nodalp_email;
	$cc 				= $cc_email;
	$sender_name   		= 'MPR Alert';
	$message        	= $body;
	
	$sentMail = send_mail($recipient_email, $cc, $sender_name, $subject, $message);		
	return $sentMail;
} // validation_error_email end


// web-service execution log function
function webserviceauditlogfunction($state_code, $data_day = null, $data_month, $financial_year,$is_updated, $is_success, $remarks, $webservice_execution_mode=null,$webservice_executed_for_date=null, $request_ip = null, $error_code = null, $rowCount=null, $webservice_execution_log_id, $error_message){
	global $con;
	$created = date('Y-m-d H:i:s');
	if($rowCount==0 && $webservice_execution_log_id>0)
	{
		// triger name trig_center_webservice_execution_log to creat log of thi record before update
		$sql = "UPDATE dbt_state_webservice_execution_log SET data_day='$data_day', data_month='$data_month', financial_year='$financial_year', is_updated='$is_updated', is_success='$is_success', error_code='$error_code', remarks='$remarks', webservice_execution_mode='$webservice_execution_mode', webservice_executed_for_date='$webservice_executed_for_date', ip_address='$request_ip', error_msg = '$error_message' WHERE state_webservice_execution_log_id=$webservice_execution_log_id";
		mysqli_query($con, $sql);
		$lastinsertedid = $webservice_execution_log_id;
		if($error_code == 0){
			$is_resolved = 'Y';
		} else {
			$is_resolved = 'N';
		}
		$sql_query = "update dbt_failed_state_webservice_details set is_resolved = '$is_resolved' where webservice_execution_log_id = $lastinsertedid";
		mysqli_query($con, $sql_query);
	}
	else
	{
		$sql = "INSERT INTO dbt_state_webservice_execution_log (state_code, data_day, data_month, financial_year, is_updated, is_success, error_code, remarks, webservice_execution_mode, webservice_executed_for_date, ip_address, created, error_msg) VALUES ('$state_code', '$data_day', '$data_month', '$financial_year', '$is_updated', '$is_success', '$error_code', '$remarks', '$webservice_execution_mode', '$webservice_executed_for_date', '$request_ip', '$created', '$error_message')";
		mysqli_query($con, $sql);
		$lastinsertedid = mysqli_insert_id($con);
		if($error_code > 0){
			$sql_query = "INSERT INTO dbt_failed_state_webservice_details (webservice_execution_log_id, is_resolved, ip_address, created, error_msg) VALUES ($lastinsertedid, 'N', '$request_ip', '$created', '$error_message')";
			mysqli_query($con, $sql_query);
		}
	}
	return $lastinsertedid;
}

// update webservice status if service failed
function update_webservice_status($webservice_id){
	global $con;
	if($webservice_id){
		$sql = "UPDATE dbt_webservice_details SET webservice_status = 0 WHERE webservice_id = $webservice_id";
		mysqli_query($con, $sql);
	}
}


// insert_data_in_temp_table
function insert_data_in_temp_table($responsedata,$state_code,$userid,$ip_address,$log_id){
	global $con;
	foreach($responsedata as $data_val){
		$schemecode = !is_array($data_val["general-information"]["schemecode"]) ? $data_val["general-information"]["schemecode"] : "";
		if(isset($data_val["general-information"]["onboarded_status_on_state_dbt_portal"])){
			$onboarded_status_on_state_dbt_portal = !is_array($data_val["general-information"]["onboarded_status_on_state_dbt_portal"]) ? $data_val["general-information"]["onboarded_status_on_state_dbt_portal"] : "";
		} else {
			$onboarded_status_on_state_dbt_portal = "";
		}		
		$scheme_mis_status = !is_array($data_val["general-information"]["scheme_mis_status"]) ? $data_val["general-information"]["scheme_mis_status"] : "";
		if(isset($data_val["general-information"]["mis_integration_status_with_state_dbt_portal"])){
			$mis_integration_status_with_state_dbt_portal = !is_array($data_val["general-information"]["mis_integration_status_with_state_dbt_portal"]) ? $data_val["general-information"]["mis_integration_status_with_state_dbt_portal"] : "";
		} else {
			$mis_integration_status_with_state_dbt_portal = "";
		}
		
		$central_allocation_for_state = !is_array($data_val["general-information"]["central_allocation_for_state"]) ? $data_val["general-information"]["central_allocation_for_state"] : "";
		$state_normative_allocation = !is_array($data_val["general-information"]["state_normative_allocation"]) ? $data_val["general-information"]["state_normative_allocation"] : "";
		$additional_state_allocation = !is_array($data_val["general-information"]["additional_state_allocation"]) ? $data_val["general-information"]["additional_state_allocation"] : "";
		$remarks = !is_array($data_val["general-information"]["remarks"]) ? $data_val["general-information"]["remarks"] : "";
		
		$year = !is_array($data_val["scheme-progress"]["year"]) ? $data_val["scheme-progress"]["year"] : "";
		$month = !is_array($data_val["scheme-progress"]["month"]) ? $data_val["scheme-progress"]["month"] : "";
		$requestid = !is_array($data_val["scheme-progress"]["requestid"]) ? $data_val["scheme-progress"]["requestid"] : "";
		
		$no_of_beneficiaries_central_allocation = !is_array($data_val["beneficiary-details"]["no_of_beneficiaries_central_allocation"]) ? $data_val["beneficiary-details"]["no_of_beneficiaries_central_allocation"] : "";
		$no_of_additional_beneficiaries_state_allocation = !is_array($data_val["beneficiary-details"]["no_of_additional_beneficiaries_state_allocation"]) ? $data_val["beneficiary-details"]["no_of_additional_beneficiaries_state_allocation"] : "";
		$total_no_of_beneficiaries = !is_array($data_val["beneficiary-details"]["total_no_of_beneficiaries"]) ? $data_val["beneficiary-details"]["total_no_of_beneficiaries"] : "";
		$no_of_beneficiaries_record_digitized = !is_array($data_val["beneficiary-details"]["no_of_beneficiaries_record_digitized"]) ? $data_val["beneficiary-details"]["no_of_beneficiaries_record_digitized"] : "";
		$no_of_authenticated_seeded_beneficiaries = !is_array($data_val["beneficiary-details"]["no_of_authenticated_seeded_beneficiaries"]) ? $data_val["beneficiary-details"]["no_of_authenticated_seeded_beneficiaries"] : "";
		$no_of_beneficiaries_whom_mobile_no_captured = !is_array($data_val["beneficiary-details"]["no_of_beneficiaries_whom_mobile_no_captured"]) ? $data_val["beneficiary-details"]["no_of_beneficiaries_whom_mobile_no_captured"] : "";
		
		$central_share_fund_transferred_cash = !is_array($data_val["fundtransfer-details"]["central_share_fund_transferred_cash"]) ? $data_val["fundtransfer-details"]["central_share_fund_transferred_cash"] : "";
		$normative_state_share_fund_transferred_cash = !is_array($data_val["fundtransfer-details"]["normative_state_share_fund_transferred_cash"]) ? $data_val["fundtransfer-details"]["normative_state_share_fund_transferred_cash"] : "";
		$additional_state_share_fund_transferred_cash = !is_array($data_val["fundtransfer-details"]["additional_state_share_fund_transferred_cash"]) ? $data_val["fundtransfer-details"]["additional_state_share_fund_transferred_cash"] : "";
		$state_share_fund_transferred_to_additional_beneficiaries_cash = !is_array($data_val["fundtransfer-details"]["state_share_fund_transferred_to_additional_beneficiaries_cash"]) ? $data_val["fundtransfer-details"]["state_share_fund_transferred_to_additional_beneficiaries_cash"] : "";
		$total_fund_transferred_cash = !is_array($data_val["fundtransfer-details"]["total_fund_transferred_cash"]) ? $data_val["fundtransfer-details"]["total_fund_transferred_cash"] : "";
		$central_share_expenditure_incurred_inkind = !is_array($data_val["fundtransfer-details"]["central_share_expenditure_incurred_inkind"]) ? $data_val["fundtransfer-details"]["central_share_expenditure_incurred_inkind"] : "";
		$normative_state_share_expenditure_incurred_inkind = !is_array($data_val["fundtransfer-details"]["normative_state_share_expenditure_incurred_inkind"]) ? $data_val["fundtransfer-details"]["normative_state_share_expenditure_incurred_inkind"] : "";
		$additional_state_share_expenditure_incurred_inkind = !is_array($data_val["fundtransfer-details"]["additional_state_share_expenditure_incurred_inkind"]) ? $data_val["fundtransfer-details"]["additional_state_share_expenditure_incurred_inkind"] : "";
		$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = !is_array($data_val["fundtransfer-details"]["state_share_expenditure_incurred_to_additional_beneficiaries_inkind"]) ? $data_val["fundtransfer-details"]["state_share_expenditure_incurred_to_additional_beneficiaries_inkind"] : "";
		$total_expenditure_incurred_inkind = !is_array($data_val["fundtransfer-details"]["total_expenditure_incurred_inkind"]) ? $data_val["fundtransfer-details"]["total_expenditure_incurred_inkind"] : "";
		
		$total_no_transactions_electronic_modes_cash = !is_array($data_val["transaction-details"]["total_no_transactions_electronic_modes_cash"]) ? $data_val["transaction-details"]["total_no_transactions_electronic_modes_cash"] : "";
		$payment_electronic_modes_cash = !is_array($data_val["transaction-details"]["payment_electronic_modes_cash"]) ? $data_val["transaction-details"]["payment_electronic_modes_cash"] : "";
		$total_no_transactions_other_modes_cash = !is_array($data_val["transaction-details"]["total_no_transactions_other_modes_cash"]) ? $data_val["transaction-details"]["total_no_transactions_other_modes_cash"] : "";
		$payment_other_modes_cash = !is_array($data_val["transaction-details"]["payment_other_modes_cash"]) ? $data_val["transaction-details"]["payment_other_modes_cash"] : "";
		$unit_of_measurement_inkind = !is_array($data_val["transaction-details"]["unit_of_measurement_inkind"]) ? $data_val["transaction-details"]["unit_of_measurement_inkind"] : "";
		$quantity_transferred_inkind = !is_array($data_val["transaction-details"]["quantity_transferred_inkind"]) ? $data_val["transaction-details"]["quantity_transferred_inkind"] : "";
		$no_of_authenticated_transactions_inkind = !is_array($data_val["transaction-details"]["no_of_authenticated_transactions_inkind"]) ? $data_val["transaction-details"]["no_of_authenticated_transactions_inkind"] : "";
		$dbt_expenditure_incurred_inkind = !is_array($data_val["transaction-details"]["dbt_expenditure_incurred_inkind"]) ? $data_val["transaction-details"]["dbt_expenditure_incurred_inkind"] : "";
		
		$no_of_duplicate_record = !is_array($data_val["savings-details"]["no_of_duplicate_record"]) ? $data_val["savings-details"]["no_of_duplicate_record"] : "";
		$no_of_ghost_fake_beneficiary = !is_array($data_val["savings-details"]["no_of_ghost_fake_beneficiary"]) ? $data_val["savings-details"]["no_of_ghost_fake_beneficiary"] : "";
		$other_saving_process_improvement = !is_array($data_val["savings-details"]["other_saving_process_improvement"]) ? $data_val["savings-details"]["other_saving_process_improvement"] : "";
		$savings = !is_array($data_val["savings-details"]["savings"]) ? $data_val["savings-details"]["savings"] : "";
		$savings_remarks = !is_array($data_val["savings-details"]["remarks"]) ? $data_val["savings-details"]["remarks"] : "";
		
		$created = date('Y-m-d H:i:s');
		
		$query = mysqli_query($con,"INSERT INTO dbt_state_webservice_temp_data SET
			state_code = $state_code,
			state_webservice_execution_log_id = $log_id,
			field_1 = '$schemecode',
			field_2 = '$onboarded_status_on_state_dbt_portal',
			field_3 = '$scheme_mis_status',
			field_4 = '$mis_integration_status_with_state_dbt_portal',
			field_5 = '$central_allocation_for_state',
			field_6 = '$state_normative_allocation',
			field_7 = '$additional_state_allocation',
			field_8 = '$remarks',
			field_9 = '$year',
			field_10 = '$month',
			field_11 = '$requestid',
			field_12 = '$no_of_beneficiaries_central_allocation',
			field_13 = '$no_of_additional_beneficiaries_state_allocation',
			field_14 = '$total_no_of_beneficiaries',
			field_15 = '$no_of_beneficiaries_record_digitized',
			field_16 = '$no_of_authenticated_seeded_beneficiaries',
			field_17 = '$no_of_beneficiaries_whom_mobile_no_captured',
			field_18 = '$central_share_fund_transferred_cash',
			field_19 = '$normative_state_share_fund_transferred_cash',
			field_20 = '$additional_state_share_fund_transferred_cash',
			field_21 = '$state_share_fund_transferred_to_additional_beneficiaries_cash',
			field_22 = '$total_fund_transferred_cash',
			field_23 = '$central_share_expenditure_incurred_inkind',
			field_24 = '$normative_state_share_expenditure_incurred_inkind',
			field_25 = '$additional_state_share_expenditure_incurred_inkind',
			field_26 = '$state_share_expenditure_incurred_to_additional_beneficiaries_inkind',
			field_27 = '$total_expenditure_incurred_inkind',
			field_28 = '$total_no_transactions_electronic_modes_cash',
			field_29 = '$payment_electronic_modes_cash',
			field_30 = '$total_no_transactions_other_modes_cash',
			field_31 = '$payment_other_modes_cash',
			field_32 = '$unit_of_measurement_inkind',
			field_33 = '$quantity_transferred_inkind',
			field_34 = '$no_of_authenticated_transactions_inkind',
			field_35 = '$dbt_expenditure_incurred_inkind',
			field_36 = '$no_of_duplicate_record',
			field_37 = '$no_of_ghost_fake_beneficiary',
			field_38 = '$other_saving_process_improvement',
			field_39 = '$savings',
			field_40 = '$savings_remarks',
			updated_by = '$userid',
			created = '$created',
			ip_address = '$ip_address'
		");
	}
}

