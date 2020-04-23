<?php
error_reporting(E_ALL);
// process data for validation, log, insertion and updation
function insert_scheme_data($responsedata, $schemeid, $servicedate, $is_updated, $txt, $nodalp_email, $cc_email, $dataday, $datamonth, $datayear, $scheme_code, $webservice_data_type, $userid) {
	global $con;
	$ip_address = $_SERVER['REMOTE_ADDR'];
	$returnvalue = array();
	$errormsg = $validation_message = '';
	
	$benefit_type_query = mysqli_query($con,"SELECT scheme_benefit_type_id FROM dbt_scheme_benefit_type_relation where scheme_id = '$schemeid'");
	$benefit_type = array();
	while($benefit_type_id = mysqli_fetch_object($benefit_type_query)){
		$benefit_type[] = $benefit_type_id->scheme_benefit_type_id;
	}
	if($responsedata){	
		if(isset($responsedata[0]['scheme-progress']['day'])){
			$day_val = intval($responsedata[0]['scheme-progress']['day']);
		} else {
			$day_val = 1;
		}
		$month = intval($responsedata[0]['scheme-progress']['month']);
		$year = intval($responsedata[0]['scheme-progress']['year']);
		$financial_year = financialYear($month, $year);
		$fy=explode("_", $financial_year);
		$s_year=$fy[0];
		$e_year=$fy[1];
		
		if(isset($responsedata[0]['message'])){
			$txt .= "Scheme Code : ".$responsedata[0]['schemecode']."\n";
			$txt .= "Response Message : ".$responsedata[0]['message']."\n\n";
			$errormsg .= $responsedata[0]['message'];
		} else {
			$error=0;
			switch ($webservice_data_type) {
				case 1:
					$res=stateValidation($responsedata, $dataday, $datamonth, $datayear);
					break;
				case 2:
					$res=districtValidation($responsedata, $dataday, $datamonth, $datayear);
					break;
				case 3:
					$res=nationValidation($responsedata, $dataday, $datamonth, $datayear);
					break;
				default:
					$errormsg .= "Not match with location.";
			}
			
			if($res[0]>0 && $res[2]==1)	{
				$error++;
				if(is_array($res[1])) {
					$errormsg .= 'Web-service validation failed';
					$email_response = validation_error_email($res[1]['error'],$schemeid,$datamonth,$benefit_type,$s_year,$e_year,$nodalp_email,$cc_email);
				} else {
					$txt .= $validation_message = $errormsg = $res[1];
				}
			}
			if($error==0)
			{
				$validation_message = "";
				$execution_date = date('Y-m-d', strtotime($servicedate));
				$validation_response = generalValidation($responsedata,$schemeid,$month,$benefit_type,$s_year,$e_year,$txt, $webservice_data_type, $execution_date);

				$invalidResponse = $validation_response["invalidResponse"];
				if(($res[0]>0 && $res[2]==0) || $invalidResponse<>"")
				{
					$state_district_matched="";
					if($res[0]>0)
					{
						$state_district_matched='no';
					}
					else
					{
						$state_district_matched='yes';
					}
					
					$created = date('Y-m-d H:i:s');
					
					$sql_query = "INSERT INTO dbt_state_district_validation_failed (scheme_id,execution_date, created, ip_address, validation_failed, state_district_matched) VALUES ($schemeid,'$execution_date','$created','$ip_address','$invalidResponse','$state_district_matched')";
					mysqli_query($con, $sql_query);
				}
				if($validation_response['error_result']>0)
				{
					$txt = $validation_response['log'];
					$email_response = validation_error_email($validation_response['error'],$schemeid,$datamonth,$benefit_type,$s_year,$e_year,$nodalp_email,$cc_email);
					$txt .= $validation_message = $errormsg .= $validation_response['validation_message'];
					$error_msg_arr = $validation_response["err_msg"];
				}
				if(count($validation_response['validatedResponse'])>0)
				{
					$error_msg_arr = '';
					$txt = $validation_response['log'];
					$responsedata=$validation_response["validatedResponse"];	//	this is the validated response. It will be insert or update in the database
					
					foreach($responsedata as $dataval) {
						$month = $dataval['scheme-progress']['month']-1;
						if(isset($dataval['location']['district_code']) && $dataval['location']['district_code'] != 0)	$district_code = $dataval['location']['district_code'];
						else					$district_code = 'NULL';
						if(isset($dataval['location']['state_code']) && $dataval['location']['state_code'] != 0)	$state_code = $dataval['location']['state_code'];
						else					$state_code = 'NULL';
						
						$sql = "SELECT * FROM dbt_scheme_beneficiary_data where scheme_id = $schemeid and financial_year = '$financial_year' and reporting_month = '$month'";
						if($state_code=='NULL')	$sql .= " and state_code is null and district_code is null";
						elseif($district_code=='NULL')	$sql .= " and state_code = '$state_code' and district_code is null";
						else	$sql .= " and state_code = '$state_code' and district_code = '$district_code'";
						$check_data = mysqli_query($con, $sql);
						if(mysqli_num_rows($check_data)>0) {
							$fetch_data_obj = mysqli_fetch_object($check_data);
						//	dbt_scheme_beneficiary_data_log1 data from dbt_scheme_beneficiary_data
							$ben_query = "INSERT INTO dbt_scheme_beneficiary_data_log1 (scheme_id, state_code, district_code, no_beneficiaries_normative, no_beneficiaries_additional_state, no_beneficiaries_digitised, no_beneficiaries_aadhaar, no_beneficiaries_mobile, no_group, beneficiary_data_log1_status, data_mode_id, scheme_transaction_from_date, scheme_transaction_to_date, reporting_month, financial_year, updated_by, created, updated, ip_address) VALUES ($fetch_data_obj->scheme_id, $state_code, $district_code, '$fetch_data_obj->no_beneficiaries_normative', '$fetch_data_obj->no_beneficiaries_additional_state', '$fetch_data_obj->no_beneficiaries_digitised', '$fetch_data_obj->no_beneficiaries_aadhaar', '$fetch_data_obj->no_beneficiaries_mobile', '$fetch_data_obj->no_group', '1', '2', '$fetch_data_obj->scheme_transaction_from_date', '$fetch_data_obj->scheme_transaction_to_date', '$fetch_data_obj->reporting_month', '$fetch_data_obj->financial_year', $userid, '$fetch_data_obj->created', '$fetch_data_obj->updated', '$ip_address')";
							mysqli_query($con, $ben_query);
							if(mysqli_insert_id($con)>0)
							{
								$valid=1;
								$existing_data_id=$fetch_data_obj->scheme_beneficiary_id;
							}
							else	$valid=0;
						}
						else
						{
							$existing_data_id=0;
							$valid=1;
						}
						if($valid==1)	$insert_data = insert_update_webservice_data($dataval, $schemeid, $benefit_type, $existing_data_id);

					}
					$is_updated = 'yes';
				}
			}
		}
	} else {
		$txt .= $errormsg = 'Data not found!';
	} // end if
	$returnvalue[0] = $txt;
	$returnvalue[1] = 'no';
	$returnvalue[2] = $is_updated;
	$returnvalue[3] = $validation_message;
	$returnvalue[4] = $errormsg;
	$returnvalue[5] = $error_msg_arr;
	$returnvalue[6] = $invalidResponse;
	
	return $returnvalue;
} // end function insertschemedata

function nationValidation($responsedata, $dataday, $datamonth, $datayear) {
	$error=0;
	$msg='';
	if(count($responsedata)==1 && $responsedata[0]['location']['state_code'] == '00')	{
		$validation_res = dataDurationValidation($responsedata, $dataday, $datamonth, $datayear);
		if($validation_res[0] > 0){
			$error++;
			$msg= $validation_res[1];
		}
	}
	else
	{
		$msg= "Only single record expected in nation wise data with state code 00.";
		$error++;
	}
	$res[0]=$error;
	$res[1]=$msg;
	$res[2]=0;
	return $res;
}

function stateValidation($responsedata, $dataday, $datamonth, $datayear) {
	global $con;
	$error=0;
	$validation_res = "";
	$msg='';
	if(count($responsedata)==1 && $responsedata[0]['location']['state_code'] == '00')	{
		$dataError=1;
		$msg="Response data type should be state wise not nation wise.";
	}
	elseif(isset($responsedata[0]['location']['district_code']))
	{
		$dataError=1;
		$msg="Response data type should be state wise not district wise.";
	}
	else
	{
		$dataError=0;
		foreach($responsedata as $value) {
			$s_code = $value['location']['state_code'];
			$sarray[]=$s_code;
		}
		$unique_states = array_unique($sarray, SORT_REGULAR);

		$sql=mysqli_query($con, "SELECT state_code FROM dbt_state_master where state_master_status = 1");
		
		if(mysqli_num_rows($sql)==count($unique_states))
		{
			while($res = mysqli_fetch_array($sql))	$statecode[]=$res[0];
			$msg= "state count match.<br/>";
			foreach($unique_states as $value) {
				if(!in_array($value, $statecode))
				{
					$error++;
					$msg= "state count not match.<br/>";
				}
				else
				{
					$validation_res = dataDurationValidation($responsedata, $dataday, $datamonth, $datayear);
					if($validation_res[0] > 0){
						$error++;
						$msg= $validation_res[1];
					}
				}
			}
		}
		else
		{
			
			$msg= "state count not match.";
			$error++;
		}
	}
	$res[0]=$error;
	$res[1]=$msg;
	$res[2]=$dataError;
	return $res;
}

function districtValidation($responsedata, $dataday, $datamonth, $datayear) {
	global $con;
	$error=0;
	$dataError=0;
	$msg='';
	$district = array();
	foreach($responsedata as $value) {
		if(isset($value['location']['district_code'])){
			$district[$value['location']['state_code']][] = $value['location']['district_code'];
		}
		else
		{
			$msg="Response data type should be district wise not district wise or nation wise.";
			$dataError++;
		}
	}
	if($dataError==0)	//	response has district wise data
	{
		ksort($district);
		foreach($district AS $k=>$v)	sort($district[$k]);

		$sql=mysqli_query($con, "SELECT district_code, state_code FROM dbt_district_master where district_master_status = 1 order by state_code, district_code");
		while($res = mysqli_fetch_object($sql))	$db_district[$res->state_code][] = $res->district_code;
		
		if($district==$db_district)
		{
			$validation_res = dataDurationValidation($responsedata, $dataday, $datamonth, $datayear);
			if($validation_res[0] > 0){
				$error++;
				$msg= $validation_res[1];
			}
		}
		else
		{
			$msg= "District count not match.";
			$error++;
		}
	}
	$res[0]=$error;
	$res[1]=$msg;
	$res[2]=$dataError;
	return $res;
}

function dataDurationValidation($responsedata, $dataday, $datamonth, $datayear)
{
	$error = 0;
	$res = array();
	foreach ($responsedata AS $data) {
		
		if(intval($dataday) != 0){
			if(intval($dataday) != intval($data['scheme-progress']['day'])) {
				$error++;
				$res[1]['error'][$data['location']['state_code']]['state_code'] = $data['location']['state_code'];
				$res[1]['error'][$data['location']['state_code']]['state_name'] = $data['location']['state_name'];
				$res[1]['error'][$data['location']['state_code']]['district_code'] = isset($data['location']['district_code']) ? $data['location']['district_code'] : "";
				$res[1]['error'][$data['location']['state_code']]['district_name'] = isset($data['location']['district_name']) ? $data['location']['district_name'] : "";
				$res[1]['error'][$data['location']['state_code']]['error'][$error] = array(
					'label'=>'Day not matched : '.$data['scheme-progress']['day'].' &#8800; '.$dataday,
					'values'=>'Response data day not matched for requested day'
				);
			}
		}
		if(intval($datamonth) != intval($data['scheme-progress']['month'])) {
			$error++;
			$res[1]['error'][$data['location']['state_code']]['state_code'] = $data['location']['state_code'];
			$res[1]['error'][$data['location']['state_code']]['state_name'] = $data['location']['state_name'];
			$res[1]['error'][$data['location']['state_code']]['district_code'] = isset($data['location']['district_code']) ? $data['location']['district_code'] : "";
			$res[1]['error'][$data['location']['state_code']]['district_name'] = isset($data['location']['district_name']) ? $data['location']['district_name'] : "";
			$res[1]['error'][$data['location']['state_code']]['error'][$error] = array(
				'label'=>'Month not matched : '.$data['scheme-progress']['month'].' &#8800; '.$datamonth,
				'values'=>'Response data month not matched for requested data month'
			);
		}
		if(intval($datayear) != intval($data['scheme-progress']['year'])) {
			$error++;
			$res[1]['error'][$data['location']['state_code']]['state_code'] = $data['location']['state_code'];
			$res[1]['error'][$data['location']['state_code']]['state_name'] = $data['location']['state_name'];
			$res[1]['error'][$data['location']['state_code']]['district_code'] = isset($data['location']['district_code']) ? $data['location']['district_code'] : "";
			$res[1]['error'][$data['location']['state_code']]['district_name'] = isset($data['location']['district_name']) ? $data['location']['district_name'] : "";
			$res[1]['error'][$data['location']['state_code']]['error'][$error] = array(
				'label'=>'Year not matched : '.$data['scheme-progress']['year'].' &#8800; '.$datayear,
				'values'=>'Response data year not matched for requested data year'
			);
		}
	}
	$res[0] = $error;
	return $res;
	
}


// Update web-service data insertion and updation
function insert_update_webservice_data($dataval, $schemeid, $benefit_type, $existing_data_id)
{
	global $con;
	// variable initialization
	$district_code = $district_name = $state_code = $state_name = $schemecode = $day = $month = $year = '';
	$no_of_beneficiaries_normative_central_and_state_share = $no_of_additional_beneficiaries_supported_by_state = $total_no_of_beneficiaries = $no_of_beneficiaries_record_digitized = $no_of_authenticated_seeded_beneficiaries = $no_of_beneficiaries_whom_mobile_no_captured = $no_of_shg_group = '';
	
	$fund_transferred_center_normative = $fund_transferred_state_normative = $total_fund_transfered_State_additional_x = $total_fund_transfered_state_additional_y = $total_fund_transferred_normative = $central_share_expenditure_incurred_inkind = $normative_state_share_expenditure_incurred_inkind = $additional_state_share_expenditure_incurred_inkind = $state_share_expenditure_incurred_to_additional_beneficiaries_inkind = $total_expenditure_incurred_inkind = '';
				
	$no_transaction_electronic_authenticated = $total_fund_electronic_authenticated = $no_transaction_non_electronic_authenticated = $total_fund_non_electronic_authenticated = $unit_of_measurement_inkind = $quantity_transferred_inkind = $no_of_authenticated_transactions_inkind = $dbt_expenditure_incurred_inkind = '';
	
	$additional_parameter1 = $additional_parameter2 = $additional_parameter3 = '';
	
	// set values
	if(isset($dataval['location']['district_code']) && $dataval['location']['district_code'] != 0){
		$district_code = $dataval['location']['district_code'];
	} else {
		$district_code = 'NULL';
	}
	if(isset($dataval['location']['district_name'])){
		$district_name = $dataval['location']['district_name'];
	} else {
		$district_name = 'NULL';
	}
	if(isset($dataval['location']['state_code']) && $dataval['location']['state_code'] != 0){
		$state_code = $dataval['location']['state_code'];
	} else {
		$state_code = 'NULL';
	}
	if(isset($dataval['location']['state_name'])){
		$state_name = $dataval['location']['state_name'];
	} else {
		$state_name = 'NULL';
	}
	
	$schemecode = $dataval['general-information']['schemecode'];
	$s_scheme_code = substr($schemecode,0,1);
			
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
	if(isset($dataval['beneficiary-details']['no_of_beneficiaries_normative_central_and_state_share'])){
		$no_of_beneficiaries_normative_central_and_state_share = (int)$dataval['beneficiary-details']['no_of_beneficiaries_normative_central_and_state_share'];
	}			
	if(isset($dataval['beneficiary-details']['no_of_additional_beneficiaries_supported_by_state'])){
		$no_of_additional_beneficiaries_supported_by_state = (int)$dataval['beneficiary-details']['no_of_additional_beneficiaries_supported_by_state'];
	}
	if(isset($dataval['beneficiary-details']['total_no_of_beneficiaries'])){
		$total_no_of_beneficiaries = (int)$dataval['beneficiary-details']['total_no_of_beneficiaries'];
	}
	if(isset($dataval['beneficiary-details']['no_of_beneficiaries_record_digitized'])){
		$no_of_beneficiaries_record_digitized = (int)$dataval['beneficiary-details']['no_of_beneficiaries_record_digitized'];
	}
	if(isset($dataval['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'])){
		$no_of_authenticated_seeded_beneficiaries = (int)$dataval['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'];
	}
	if(isset($dataval['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'])){
		$no_of_beneficiaries_whom_mobile_no_captured = (int)$dataval['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'];
	}
	if(isset($dataval['beneficiary-details']['number_of_groups_shg'])){
		$no_of_shg_group = (int)$dataval['beneficiary-details']['number_of_groups_shg'];
	}
				
	// cash fundtransfer-details
	if(isset($dataval['fundtransfer-details']['central_share_fund_transferred_cash'])){
		$fund_transferred_center_normative = floatval($dataval['fundtransfer-details']['central_share_fund_transferred_cash']);
	}
	if(isset($dataval['fundtransfer-details']['normative_state_share_fund_transferred_cash'])){
		$fund_transferred_state_normative = floatval($dataval['fundtransfer-details']['normative_state_share_fund_transferred_cash']);
	}
	if(isset($dataval['fundtransfer-details']['additional_state_share_fund_transferred_cash'])){
		$total_fund_transfered_State_additional_x = floatval($dataval['fundtransfer-details']['additional_state_share_fund_transferred_cash']);
	}
	if(isset($dataval['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'])){
		$total_fund_transfered_state_additional_y = floatval($dataval['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash']);
	}
	if(isset($dataval['fundtransfer-details']['total_fund_transferred_cash'])){
		$total_fund_transferred_normative = floatval($dataval['fundtransfer-details']['total_fund_transferred_cash']);
	}
	
	// inkind fundtransfer-details
	if(isset($dataval['fundtransfer-details']['central_share_expenditure_incurred_inkind'])){
		$central_share_expenditure_incurred_inkind = floatval($dataval['fundtransfer-details']['central_share_expenditure_incurred_inkind']);
	}
	if(isset($dataval['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'])){
		$normative_state_share_expenditure_incurred_inkind = floatval($dataval['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind']);
	}
	if(isset($dataval['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'])){
		$additional_state_share_expenditure_incurred_inkind = floatval($dataval['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind']);
	}
	if(isset($dataval['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'])){
		$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = floatval($dataval['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind']);
	}
	if(isset($dataval['fundtransfer-details']['total_expenditure_incurred_inkind'])){
		$total_expenditure_incurred_inkind = floatval($dataval['fundtransfer-details']['total_expenditure_incurred_inkind']);
	}
	
	// cash transaction-details
	if(isset($dataval['transaction-details']['total_no_transactions_electronic_modes_cash'])){
		$no_transaction_electronic_authenticated = (int)$dataval['transaction-details']['total_no_transactions_electronic_modes_cash'];
	}
	if(isset($dataval['transaction-details']['payment_electronic_modes_cash'])){
		$total_fund_electronic_authenticated = floatval($dataval['transaction-details']['payment_electronic_modes_cash']);
	}
	if(isset($dataval['transaction-details']['total_no_transactions_other_modes_cash'])){
		$no_transaction_non_electronic_authenticated = (int)$dataval['transaction-details']['total_no_transactions_other_modes_cash'];
	}
	if(isset($dataval['transaction-details']['payment_other_modes_cash'])){
		$total_fund_non_electronic_authenticated = floatval($dataval['transaction-details']['payment_other_modes_cash']);
	}
	
	// inkind transaction-details
	if(isset($dataval['transaction-details']['unit_of_measurement_inkind'])){
		$unit_of_measurement_inkind = textValidation($dataval['transaction-details']['unit_of_measurement_inkind']);
	}
	if(isset($dataval['transaction-details']['quantity_transferred_inkind'])){
		$quantity_transferred_inkind = (int)$dataval['transaction-details']['quantity_transferred_inkind'];
	}
	if(isset($dataval['transaction-details']['no_of_authenticated_transactions_inkind'])){
		$no_of_authenticated_transactions_inkind = (int)$dataval['transaction-details']['no_of_authenticated_transactions_inkind'];
	}
	if(isset($dataval['transaction-details']['dbt_expenditure_incurred_inkind'])){
		$dbt_expenditure_incurred_inkind = floatval($dataval['transaction-details']['dbt_expenditure_incurred_inkind']);
	}
	
	// additional-parameters
	if(isset($dataval['additional-parameters'])){
		if(!is_array($dataval['additional-parameters']['additional_parameter1'])){
			$additional_parameter1 = textValidation($dataval['additional-parameters']['additional_parameter1']);
		}
		if(!is_array($dataval['additional-parameters']['additional_parameter2'])){
			$additional_parameter2 = textValidation($dataval['additional-parameters']['additional_parameter2']);
		}
		if(!is_array($dataval['additional-parameters']['additional_parameter3'])){
			$additional_parameter3 = textValidation($dataval['additional-parameters']['additional_parameter3']);
		}
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
	
	$userid = 1;
	$created = date('Y-m-d H:i:s');
	$updated = date('Y-m-d H:i:s');
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	if($s_scheme_code == 'B'){
		$total_no_of_beneficiaries = $no_of_beneficiaries_normative_central_and_state_share;
	} elseif ($s_scheme_code == 'A' && $no_of_additional_beneficiaries_supported_by_state > 0){
		$no_of_additional_beneficiaries_supported_by_state = 0;
	}	
	
//	insert/update beneficiary data
	if($existing_data_id == 0) {
		$ben_query = "INSERT INTO dbt_scheme_beneficiary_data SET ";
		$where="";
		$extra=", created = '$created', scheme_id = '$schemeid', state_code = $state_code, district_code = $district_code, financial_year = '$financial_year'";
	} else {
		$ben_query = "UPDATE dbt_scheme_beneficiary_data SET ";
		$extra="";
		$where = "WHERE scheme_beneficiary_id='$existing_data_id' AND scheme_id = '$schemeid'";
	}
	$ben_query .= " no_beneficiaries_normative = '$total_no_of_beneficiaries', no_beneficiaries_additional_state = '$no_of_additional_beneficiaries_supported_by_state', no_beneficiaries_digitised = '$no_of_beneficiaries_record_digitized', no_beneficiaries_aadhaar = '$no_of_authenticated_seeded_beneficiaries', no_beneficiaries_mobile = '$no_of_beneficiaries_whom_mobile_no_captured', no_group = '$no_of_shg_group', data_mode_id = '2', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', beneficiary_data_status = '1', updated_by = '$userid', ip_address = '$ip_address', reporting_month = '$month', updated='$updated' $extra $where ";
	
	mysqli_query($con, $ben_query);
	
	// update transaction data
		
	
	if (in_array(1, $benefit_type)){
		$check_tr_query = "SELECT COUNT(*) as count FROM dbt_scheme_beneficiary_b_typewise_data WHERE scheme_id = '$schemeid'";
		if ($state_code == 'NULL') {
			$check_tr_query.=" and state_code is null and district_code is null";
		}
		elseif ($district_code == 'NULL') {
			$check_tr_query.=" and state_code = '$state_code' and district_code is null";
		}
		else {
			$check_tr_query.=" and state_code = '$state_code' and district_code = '$district_code'";
		}
		$check_tr_query.=" and reporting_month = '$month' and financial_year = '$financial_year'";
		$check_tr_query.= " and scheme_benefit_type_id = '1' ";
		$check_tr_query = mysqli_query($con, $check_tr_query);
		$check_tr_cash_data_obj = mysqli_fetch_array($check_tr_query);
		
	
		if($check_tr_cash_data_obj[0] == 0){
			$data_query = "INSERT INTO dbt_scheme_beneficiary_b_typewise_data SET ";
			$where="";
			$extra=", created = '$created', scheme_id = '$schemeid', scheme_benefit_type_id = '1', state_code = $state_code, district_code = $district_code, reporting_month = '$month', financial_year = '$financial_year'";
		} else {
			$data_query = "UPDATE dbt_scheme_beneficiary_b_typewise_data SET ";
			$extra="";
			$where=" WHERE scheme_id = '$schemeid' and scheme_benefit_type_id = '1'";
			if ($state_code == 'NULL'){
				$where.=" and state_code is null and district_code is null and reporting_month = '$month' and financial_year = '$financial_year'";
			} else if ($district_code == 'NULL'){
				$where.=" and state_code = '$state_code' and district_code is null and reporting_month = '$month' and financial_year = '$financial_year'";
			} else {
				$where.=" and state_code = '$state_code' and district_code = '$district_code' and reporting_month = '$month' and financial_year = '$financial_year'";
			}
		}
		$data_query.=" total_fund_transferred_normative = '$total_fund_transferred_normative', fund_transferred_state_normative = '$fund_transferred_state_normative', fund_transferred_center_normative = '$fund_transferred_center_normative', total_fund_transfered_State_additional_x = '$total_fund_transfered_State_additional_x', total_fund_transfered_state_additional_y = '$total_fund_transfered_state_additional_y', total_fund_electronic_authenticated = '$total_fund_electronic_authenticated', total_fund_non_electronic_authenticated = '$total_fund_non_electronic_authenticated', no_transaction_electronic_authenticated = '$no_transaction_electronic_authenticated', no_transaction_non_electronic_authenticated = '$no_transaction_non_electronic_authenticated', unit_of_measurement = '', no_quantity = '', additional_parameter1 = '$additional_parameter1', additional_parameter2 = '$additional_parameter2', additional_parameter3 = '$additional_parameter3', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', b_typewise_data_status = '1', updated_by = '$userid', ip_address = '$ip_address', updated='$updated' $extra $where";
		$sql=mysqli_query($con, $data_query);
	}
	if (in_array(2, $benefit_type)){
		$check_tr_query = "SELECT COUNT(*) as count FROM dbt_scheme_beneficiary_b_typewise_data WHERE scheme_id = '$schemeid'";
		if ($state_code == 'NULL') {
			$check_tr_query.=" and state_code is null and district_code is null";
		}
		elseif ($district_code == 'NULL') {
			$check_tr_query.=" and state_code = '$state_code' and district_code is null";
		}
		else {
			$check_tr_query.=" and state_code = '$state_code' and district_code = '$district_code'";
		}
		$check_tr_query.=" and reporting_month = '$month' and financial_year = '$financial_year'";
		$check_tr_query.=" and scheme_benefit_type_id = '2' ";
		$check_tr_query = mysqli_query($con, $check_tr_query);
		$check_tr_kind_data_obj = mysqli_fetch_array($check_tr_query);
		
		if($check_tr_kind_data_obj[0] == 0) {
			$data_query_kind = "INSERT INTO dbt_scheme_beneficiary_b_typewise_data SET ";
			$where="";
			$extra=", created = '$created', scheme_id = '$schemeid', scheme_benefit_type_id = '2', state_code = $state_code, district_code = $district_code, reporting_month = '$month', financial_year = '$financial_year'";
		} else {
			$data_query_kind = "UPDATE dbt_scheme_beneficiary_b_typewise_data SET ";
			$extra="";
			$where=" WHERE scheme_id = '$schemeid' and scheme_benefit_type_id = '2'";
			if ($state_code == 'NULL'){
				$where.=" and state_code is null and district_code is null and reporting_month = '$month' and financial_year = '$financial_year'";
			} else if ($district_code == 'NULL'){
				$where.=" and state_code = '$state_code' and district_code is null and reporting_month = '$month' and financial_year = '$financial_year'";
			} else {
				$where.=" and state_code = '$state_code' and district_code = '$district_code' and reporting_month = '$month' and financial_year = '$financial_year'";
			}
		}
		
		$data_query_kind .= " total_fund_transferred_normative = '$total_expenditure_incurred_inkind', fund_transferred_state_normative = '$normative_state_share_expenditure_incurred_inkind', fund_transferred_center_normative = '$central_share_expenditure_incurred_inkind', total_fund_transfered_State_additional_x = '$additional_state_share_expenditure_incurred_inkind', total_fund_transfered_state_additional_y = '$state_share_expenditure_incurred_to_additional_beneficiaries_inkind', total_fund_electronic_authenticated = '$dbt_expenditure_incurred_inkind', total_fund_non_electronic_authenticated = '', no_transaction_electronic_authenticated = '$no_of_authenticated_transactions_inkind', no_transaction_non_electronic_authenticated = '', unit_of_measurement = '$unit_of_measurement_inkind', no_quantity = '$quantity_transferred_inkind', additional_parameter1 = '$additional_parameter1', additional_parameter2 = '$additional_parameter2', additional_parameter3 = '$additional_parameter3', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', b_typewise_data_status = '1', updated_by = '$userid', ip_address = '$ip_address', updated='$updated' $extra $where";
		$sql=mysqli_query($con, $data_query_kind);
	}
	
	
} // function insert_update_webservice_data

/*---(Web-service Validation  )----------------------------------|
  |	 * Purpose:-Prevent data discrepancy                         |
  |--------------------------------------------------------------|
*/
function generalValidation($getValue,$schemeid,$datamonth,$benefit_type,$start,$end,$txt, $webservice_data_type, $execution_date)
{	
	global $con;
	$returnvalue=array();
	$response = false;	
	$msg_body = '';	
	
	$datainfo = mysqli_query($con,"SELECT sm.scheme_type_id as scheme_type, sm.scheme_code, sd.scheme_name, m.ministry_name, e.scheme_eligibility_type_id as dbt_eligibility_type FROM dbt_scheme_master as sm JOIN dbt_scheme_details as sd on sm.scheme_id = sd.scheme_id JOIN dbt_ministry_details as m on sm.ministry_id = m.ministry_id JOIN dbt_scheme_eligibility_type_relation as e on sm.scheme_id = e.scheme_id where sm.scheme_id = '".$schemeid."'");
	$datainfocount = mysqli_fetch_object($datainfo);
	$scheme_type = $datainfocount->scheme_type; 		
	$dbt_eligibility_type = $datainfocount->dbt_eligibility_type; 
	$scheme_name=$datainfocount->scheme_name;
	$ministry_name=$datainfocount->ministry_name;
	$scheme_code=$datainfocount->scheme_code;
	$data_updated = date('d/m/Y h:i:s a');

	$month_num = $datamonth;
	$month_name = date("M", mktime(0, 0, 0, $month_num, 10));
	$year_name = date("Y");
	if(intval($month_num)<=3)	$f_year= $end;
	else						$f_year= $start;
	
	if(count($benefit_type)==1 && in_array(1, $benefit_type))		$benefit_type_name = "CASH";
	elseif(count($benefit_type)==1 && in_array(2, $benefit_type))	$benefit_type_name = "In Kind";
	elseif(count($benefit_type)>1 && in_array(1, $benefit_type) && in_array(2, $benefit_type))	$benefit_type_name = "Cash and In Kind";
	else	$benefit_type_name = "";
	
	$unit_of_measurement_inkind_array = array();
	$subject = "DBT Web-service rejection alert: ".$ministry_name." (".$scheme_code."), " . $month_name."-".$f_year; 
	$i=1;
	$cont=1;
	$error_report_array=array();
	if(!is_array($getValue['0']['general-information']['schemecode'])) {
		$schemecode = $getValue['0']['general-information']['schemecode'];
	} else {
		$schemecode = '';
	}												
	$txt .= "Scheme Code : ".$schemecode."\n\n";
	
	$eligibility_check_query = mysqli_query($con, "SELECT scheme_eligibility_type_id as dbt_eligibility_type FROM dbt_scheme_eligibility_type_relation WHERE scheme_id = '".$schemeid."'");
	$eligibility_info = mysqli_fetch_object($eligibility_check_query);
	$dbt_eligibility_type = $eligibility_info->dbt_eligibility_type;
	$k = 0;
	
	$err_flag=0;
	$validatedResponse=array();
	$invalidResponse="";
	$previousMonthDataArray=getPreviousMonthData($schemeid, $webservice_data_type);
	
    foreach($getValue as $key=>$val)
    {
		$k++;
		$year = textValidation($val['scheme-progress']['year']);
		$month = (intval($val['scheme-progress']['month'])<10) ? textValidation('0'.intval($val['scheme-progress']['month'])) : textValidation($val['scheme-progress']['month']);
		$day = (isset($val['scheme-progress']['day'])) ? textValidation($val['scheme-progress']['day']) : '';
		
		$district_code = (isset($val['location']['district_code'])) ? (int)textValidation($val['location']['district_code']) : '';
		$district_name = (isset($val['location']['district_name'])) ? textValidation($val['location']['district_name']) : '';
		
		$state_code = (int)textValidation($val['location']['state_code']);
		$state_name = textValidation($val['location']['state_name']);
		if($k==1)
		{
			if($district_code>0)	$invalidResponse.="district_code//";
			elseif($state_code>0)	$invalidResponse.="state_code//";
		}
	//	beneficiary-details
		$beneficiary_type = '';
		$number_of_groups_shg = '';
		if($dbt_eligibility_type == 4) {
			if(isset($val['beneficiary-details']['beneficiary_type']) && !is_array($val['beneficiary-details']['beneficiary_type'])){
				$beneficiary_type = textValidation($val['beneficiary-details']['beneficiary_type']);
			}
			if(isset($val['beneficiary-details']['number_of_groups_shg']) && !is_array($val['beneficiary-details']['number_of_groups_shg'])){
				$number_of_groups_shg = textValidation($val['beneficiary-details']['number_of_groups_shg']);
			}
		}
		if(!is_array($val['beneficiary-details']['no_of_beneficiaries_normative_central_and_state_share'])){
			$no_of_beneficiaries_normative_central_and_state_share = textValidation($val['beneficiary-details']['no_of_beneficiaries_normative_central_and_state_share']);
		} else {
			$no_of_beneficiaries_normative_central_and_state_share = '';
		}
		if(!is_array($val['beneficiary-details']['no_of_additional_beneficiaries_supported_by_state'])){
			$no_of_additional_beneficiaries_supported_by_state = textValidation($val['beneficiary-details']['no_of_additional_beneficiaries_supported_by_state']);
		} else {
			$no_of_additional_beneficiaries_supported_by_state = '';
		}
		if(!is_array($val['beneficiary-details']['total_no_of_beneficiaries'])){
			$total_no_of_beneficiaries = textValidation($val['beneficiary-details']['total_no_of_beneficiaries']);
		
		} else {
			$total_no_of_beneficiaries = '';
		}
		if(!is_array($val['beneficiary-details']['no_of_beneficiaries_record_digitized'])){
			$no_of_beneficiaries_record_digitized = textValidation($val['beneficiary-details']['no_of_beneficiaries_record_digitized']);
		} else {
			$no_of_beneficiaries_record_digitized = '';
		}
		if(!is_array($val['beneficiary-details']['no_of_authenticated_seeded_beneficiaries'])){
			$no_of_authenticated_seeded_beneficiaries = textValidation($val['beneficiary-details']['no_of_authenticated_seeded_beneficiaries']);
		} else {
			$no_of_authenticated_seeded_beneficiaries = '';
		}
		if(!is_array($val['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured'])){
			$no_of_beneficiaries_whom_mobile_no_captured = textValidation($val['beneficiary-details']['no_of_beneficiaries_whom_mobile_no_captured']);
		} else {
			$no_of_beneficiaries_whom_mobile_no_captured = '';
		}			
		
	//	fundtransfer-details
			if(!is_array($val['fundtransfer-details']['central_share_fund_transferred_cash'])){
				$central_share_fund_transferred_cash = textValidation($val['fundtransfer-details']['central_share_fund_transferred_cash']);
			} else {
				$central_share_fund_transferred_cash = '';
			}
			if(!is_array($val['fundtransfer-details']['normative_state_share_fund_transferred_cash'])){
				$normative_state_share_fund_transferred_cash = textValidation($val['fundtransfer-details']['normative_state_share_fund_transferred_cash']);
			} else {
				$normative_state_share_fund_transferred_cash = '';
			}
			if(!is_array($val['fundtransfer-details']['additional_state_share_fund_transferred_cash'])){
				$additional_state_share_fund_transferred_cash = textValidation($val['fundtransfer-details']['additional_state_share_fund_transferred_cash']);
			} else {
				$additional_state_share_fund_transferred_cash = '';
			}
			if(!is_array($val['fundtransfer-details']['total_fund_transferred_cash'])){
				$total_fund_transferred_cash = textValidation($val['fundtransfer-details']['total_fund_transferred_cash']);
			} else {
				$total_fund_transferred_cash = '';
			}
			if(!is_array($val['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash'])){
				$state_share_fund_transferred_to_additional_beneficiaries_cash = textValidation($val['fundtransfer-details']['state_share_fund_transferred_to_additional_beneficiaries_cash']);
			} else {
				$state_share_fund_transferred_to_additional_beneficiaries_cash = '';
			}
	
	//	inkind in fundtransfer details
			if(!is_array($val['fundtransfer-details']['central_share_expenditure_incurred_inkind'])){
				$central_share_expenditure_incurred_inkind = textValidation($val['fundtransfer-details']['central_share_expenditure_incurred_inkind']);
			} else {
				$central_share_expenditure_incurred_inkind = '';
			}
			if(!is_array($val['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind'])){
				$normative_state_share_expenditure_incurred_inkind = textValidation($val['fundtransfer-details']['normative_state_share_expenditure_incurred_inkind']);
			} else {
				$normative_state_share_expenditure_incurred_inkind = '';
			}
			if(!is_array($val['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind'])){
				$additional_state_share_expenditure_incurred_inkind = textValidation($val['fundtransfer-details']['additional_state_share_expenditure_incurred_inkind']);
			} else {
				$additional_state_share_expenditure_incurred_inkind = '';
			}
			if(!is_array($val['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind'])){
				$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = textValidation($val['fundtransfer-details']['state_share_expenditure_incurred_to_additional_beneficiaries_inkind']);
			} else {
				$state_share_expenditure_incurred_to_additional_beneficiaries_inkind = '';
			}
			if(!is_array($val['fundtransfer-details']['total_expenditure_incurred_inkind'])){
				$total_expenditure_incurred_inkind = textValidation($val['fundtransfer-details']['total_expenditure_incurred_inkind']);
			} else {
				$total_expenditure_incurred_inkind = '';
			}

	//	transaction-details
			if(!is_array($val['transaction-details']['total_no_transactions_electronic_modes_cash'])){
				$total_no_transactions_electronic_modes_cash = textValidation($val['transaction-details']['total_no_transactions_electronic_modes_cash']);
			} else {
				$total_no_transactions_electronic_modes_cash = '';
			}
			if(!is_array($val['transaction-details']['payment_electronic_modes_cash'])){
				$payment_electronic_modes_cash = textValidation($val['transaction-details']['payment_electronic_modes_cash']);
			} else {
				$payment_electronic_modes_cash = '';
			}
			
			if(!is_array($val['transaction-details']['total_no_transactions_other_modes_cash'])){
				$total_no_transactions_other_modes_cash = textValidation($val['transaction-details']['total_no_transactions_other_modes_cash']);
			} else {
				$total_no_transactions_other_modes_cash = '';
			}
			if(!is_array($val['transaction-details']['payment_other_modes_cash'])){
				$payment_other_modes_cash = textValidation($val['transaction-details']['payment_other_modes_cash']);
			} else {
				$payment_other_modes_cash = '';
			}
			if(isset($val['transaction-details']['unit_of_measurement_inkind'])) {
				if(!is_array($val['transaction-details']['unit_of_measurement_inkind']) && $dbt_eligibility_type != 4){
					$unit_of_measurement_inkind = textValidation($val['transaction-details']['unit_of_measurement_inkind']);
				} else {
					$unit_of_measurement_inkind = '';
				}
			} else {
				$unit_of_measurement_inkind = '';
			}
			if($unit_of_measurement_inkind!='' && !in_array($unit_of_measurement_inkind, $unit_of_measurement_inkind_array)){
				array_push($unit_of_measurement_inkind_array, $unit_of_measurement_inkind);
			}
			
			if(isset($val['transaction-details']['quantity_transferred_inkind'])) {
				if(!is_array($val['transaction-details']['quantity_transferred_inkind']) && $dbt_eligibility_type != 4){
					$quantity_transferred_inkind = textValidation($val['transaction-details']['quantity_transferred_inkind']);
				} else {
					$quantity_transferred_inkind = '';
				}
			} else {
				$quantity_transferred_inkind = '';
			}
			if(!is_array($val['transaction-details']['no_of_authenticated_transactions_inkind'])){
				$no_of_authenticated_transactions_inkind = textValidation($val['transaction-details']['no_of_authenticated_transactions_inkind']);
			} else {
				$no_of_authenticated_transactions_inkind = '';
			}
			if(!is_array($val['transaction-details']['dbt_expenditure_incurred_inkind']) && $dbt_eligibility_type == 4){
				$dbt_expenditure_incurred_inkind = $total_expenditure_incurred_inkind;
			} else if(!is_array($val['transaction-details']['dbt_expenditure_incurred_inkind'])){
				$dbt_expenditure_incurred_inkind = textValidation($val['transaction-details']['dbt_expenditure_incurred_inkind']);
			} else {
				$dbt_expenditure_incurred_inkind = '';
			}										
		$txt .= "#".$k."\n";
		$txt .= "Year : ".$year."\n";
		$txt .= "Month : ".$month."\n";
		if($day<>"")	$txt .= "Day : ".$day."\n";
		if($district_code<>"")	$txt .= "District code : ".$district_code."\n";
		if($district_name<>"")	$txt .= "District name : ".$district_name."\n";
		$txt .= "State Code : ".$state_code."\n";
		$txt .= "State Name : ".$state_name."\n\n";
	
		if($dbt_eligibility_type == 4){
			$txt .= "Beneficiary Type : ".$beneficiary_type."\n";
			$txt .= "No of Groups SHG : ".$number_of_groups_shg."\n";
		}
		$txt .= "No of Beneficiaries Normative Central and State Share : ".$no_of_beneficiaries_normative_central_and_state_share."\n";
		$txt .= "No of Additional Beneficiaries Supported by State : ".$no_of_additional_beneficiaries_supported_by_state."\n";
		$txt .= "Total No of Beneficiaries : ".$total_no_of_beneficiaries."\n";
		$txt .= "No of Beneficiaries Record Digitized : ".$no_of_beneficiaries_record_digitized."\n";
		$txt .= "No of Authenticated Seeded Beneficiaries Record : ".$no_of_authenticated_seeded_beneficiaries."\n";
		$txt .= "No of Beneficiaries Whom Mobile no. Captured : ".$no_of_beneficiaries_whom_mobile_no_captured."\n\n";
			
		if(in_array(1, $benefit_type)){
			$txt .= "Central Share Fund Transferred cash: ".$central_share_fund_transferred_cash."\n";
			$txt .= "Normative State Share Fund Transferred cash : ".$normative_state_share_fund_transferred_cash."\n";
			$txt .= "Additional State Share Fund Transferred cash : ".$additional_state_share_fund_transferred_cash."\n";
			$txt .= "Total Fund Transferred cash : ".$total_fund_transferred_cash."\n";
			$txt .= "State Share Fund Transferred To Additional Beneficiaries cash : ".$state_share_fund_transferred_to_additional_beneficiaries_cash."\n\n";
			$txt .= "Total No. Transactions Electronic Modes cash : ".$total_no_transactions_electronic_modes_cash."\n";
			$txt .= "Payment Electronic Modes cash : ".$payment_electronic_modes_cash."\n";
			$txt .= "Total No. Transactions Other Modes cash: ".$total_no_transactions_other_modes_cash."\n";
			$txt .= "Payment Other Modes cash : ".$payment_other_modes_cash."\n\n";
		}
		if(in_array(2, $benefit_type)){
			$txt .= "Central share expenditure incurred inkind : ".$central_share_expenditure_incurred_inkind."\n";
			$txt .= "Normative state share expenditure incurred inkind : ".$normative_state_share_expenditure_incurred_inkind."\n";
			$txt .= "Additional state share expenditure incurred inkind: ".$additional_state_share_expenditure_incurred_inkind."\n";
			$txt .= "State share expenditure incurred to additional beneficiaries inkind: ".$state_share_expenditure_incurred_to_additional_beneficiaries_inkind."\n";
			$txt .= "Total expenditure incurred inkind: ".$total_expenditure_incurred_inkind."\n\n";
			$txt .= "Unit of Measurement inkind : ".$unit_of_measurement_inkind."\n";
			$txt .= "Quantity Transferred inkind : ".$quantity_transferred_inkind."\n";
			$txt .= "Total No. Authenticated Transactions : ".$no_of_authenticated_transactions_inkind."\n";
			$txt .= "Dbt expenditure incurred inkind : ".$dbt_expenditure_incurred_inkind."\n";
		}
			
	//	additional parameter start
		$additional_parameter1 = '';
		$additional_parameter2 = '';
		$additional_parameter3 = '';
		if(isset($val['additional-parameters']['additional_parameter1']) && !is_array($val['additional-parameters']['additional_parameter1'])) {
			$additional_parameter1 = textValidation($val['additional-parameters']['additional_parameter1']);
			$txt .= "Additional Parameter1 : ".$additional_parameter1."\n";
		} 
		if(isset($val['additional-parameters']['additional_parameter2']) && !is_array($val['additional-parameters']['additional_parameter2'])) {
			$additional_parameter2 = textValidation($val['additional-parameters']['additional_parameter2']);
			$txt .= "Additional Parameter2 : ".$additional_parameter2."\n";
		}
		if(isset($val['additional-parameters']['additional_parameter3']) && !is_array($val['additional-parameters']['additional_parameter3'])) {
			$additional_parameter3 = textValidation($val['additional-parameters']['additional_parameter3']);
			$txt .= "Additional Parameter3 : ".$additional_parameter3."\n";
		}
	//	additional parameter end
			
		$txt .= "------------------\n\n";

		if(in_array(1, $benefit_type)){
			$sum_fund_transferred_cash = $central_share_fund_transferred_cash + $normative_state_share_fund_transferred_cash +$additional_state_share_fund_transferred_cash +$state_share_fund_transferred_to_additional_beneficiaries_cash;

			$sum_payment_cash = $payment_electronic_modes_cash + $payment_other_modes_cash;
		}
		if(in_array(2, $benefit_type)){
			$sum_expenditure_incurred_inkind = $central_share_expenditure_incurred_inkind + $normative_state_share_expenditure_incurred_inkind + $additional_state_share_expenditure_incurred_inkind + $state_share_expenditure_incurred_to_additional_beneficiaries_inkind;	
		}

		$sum_beneficiaries = $no_of_beneficiaries_normative_central_and_state_share + $no_of_additional_beneficiaries_supported_by_state;
		
		$error_report_array[$state_code]=array(
			'state_code'=>$state_code,
			'state_name'=>$state_name,
			'district_code'=>$district_code,
			'district_name'=>$district_name
		);
		$error_report_array[$state_code]['error']=array();	
		$numeric_error_message = " should not be negative(-ve) or blank. Also should not contain any alphabet or special character only positive(+ve) numeric value is allowed";
	
		$c=0;
		if($webservice_data_type==1)
		{
			$checkUnit=$state_code;
			$labeltext='State Code : ';
		}
		elseif($webservice_data_type==2)
		{
			$checkUnit=$district_code;
			$labeltext='District Code : ';
		}
		foreach($previousMonthDataArray AS $key => $value)
		{
			if($key==$checkUnit)
			{
				$c=1;
				$scheme_transaction_from_date = $value;
				if($day<>"")
				{
					$reporting_date=date("Y-m-d", strtotime("$scheme_transaction_from_date +1 days"));
					if($reporting_month<>$execution_date)
					{
						$error_report_array[$checkUnit]['error'][$i++]=array(
							'label'=>$labeltext.$checkUnit,
							'values'=>'Previous Day ('.$scheme_transaction_from_date.") data is not available.",
						);
						$err_flag=1;
					}
				}
				else
				{
					$reporting_month=date("m", strtotime("$scheme_transaction_from_date +1 months"));
					if($reporting_month<>$month)
					{
						$error_report_array[$checkUnit]['error'][$i++]=array(
							'label'=>$labeltext.$checkUnit,
							'values'=>'Previous Month ('.($month-1).") data is not available.",
						);
						$err_flag=1;
					}
				}
				break;
			}
		}
		if($c==0)
		{
			if($webservice_data_type==3)
			{
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>$labeltext.$checkUnit,
					'values'=>'Previous Month ('.($month-1).") data is not available.",
				);
			}
			else
			{
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>$labeltext.$checkUnit,
					'values'=>'Previous Month ('.($month-1).") data is not available.",
				);
				//	If there is any new state in response data and that state is not available in previous month data then we will not insert that state code
			}
			$err_flag=1;
		}
	//	vali-1(beneficiary-details)------------------------------------------------------------
		if(trim($val['general-information']['schemecode']) != trim($scheme_code) && $err_flag==0) 
		{
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'Scheme Code : '.(empty($val['general-information']['schemecode']) ? '': $val['general-information']['schemecode']),
				'values'=>'Scheme code not matched with requested scheme code.',
			);
			$err_flag=1;
		}
		elseif(checkPositiveNumber($total_no_of_beneficiaries)==1) 
		{
			$error_report_array[$state_code]['error'][$i++]= array(
				'label' => 'Total no of beneficiaries : '.$total_no_of_beneficiaries,
				'values' => "Total no of beneficiaries ".$numeric_error_message,
			);
			$err_flag=1;
		}
		elseif(checkPositiveNumber($no_of_beneficiaries_record_digitized)==1) 
		{
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'No. of beneficiaries record digitized : '.$no_of_beneficiaries_record_digitized,
				'values'=>'No. of beneficiaries record digitized '.$numeric_error_message,
			);
			$err_flag=1;
		}
		elseif(checkPositiveNumber($no_of_authenticated_seeded_beneficiaries)==1) 
		{
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'No. of authenticated seeded beneficiaries : '.$no_of_authenticated_seeded_beneficiaries,
				'values'=>'No. of authenticated seeded beneficiaries '.$numeric_error_message,
			);
			$err_flag=1;
		}
		elseif(checkPositiveNumber($no_of_beneficiaries_whom_mobile_no_captured)==1) 
		{
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'No. of beneficiaries whom mobile no captured : '.$no_of_beneficiaries_whom_mobile_no_captured,
				'values'=>'No. of beneficiaries whom mobile no captured '.$numeric_error_message,
			);
			$err_flag=1;
		}
		elseif($no_of_beneficiaries_record_digitized > $total_no_of_beneficiaries)
		{		
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'Total no of beneficiaries : '.$total_no_of_beneficiaries .' >= '.$no_of_beneficiaries_record_digitized,
				'values'=>'Total number of beneficiaries should be greater than or equal to number of beneficiary records digitized',
			);	
			$err_flag=1;					
		}		
		elseif($no_of_authenticated_seeded_beneficiaries > $no_of_beneficiaries_record_digitized)
		{											
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'No. of beneficiaries record digitized : '.$no_of_beneficiaries_record_digitized.' >= '.$no_of_authenticated_seeded_beneficiaries,
				'values'=>'Number of beneficiary records digitized should be greater than or equal to number of authenticated seeded beneficiaries',
			);	
			$err_flag=1;											
		}
		elseif($no_of_beneficiaries_whom_mobile_no_captured > $total_no_of_beneficiaries)
		{											
			$error_report_array[$state_code]['error'][$i++]=array(
				'label'=>'Total number of beneficiaries : '.$total_no_of_beneficiaries.' >= '.$no_of_beneficiaries_whom_mobile_no_captured,
				'values'=>'Total number of beneficiaries should be greater than or equal to number of beneficiaries of whom mobile number has been captured',
			);
			$err_flag=1;						
		}
		 
	//vali-1(fundtransfer-details for cs)---------------------------------------------------
	
		if((count($benefit_type) == 1 && in_array(1, $benefit_type)) || (count($benefit_type) > 1 && in_array(1, $benefit_type) && in_array(2, $benefit_type) && $err_flag==0))
		{
			if(checkPositiveNumber($total_fund_transferred_cash)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Total fund transferred  : = '.$total_fund_transferred_cash,
					'values'=>'Total fund transferred '.$numeric_error_message,
				);	
				$err_flag=1;		
			}
			elseif(checkPositiveNumber($central_share_fund_transferred_cash)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Central share fund transferred : = '.$central_share_fund_transferred_cash,
					'values'=>'Central share fund transferred '.$numeric_error_message,
				);	
				$err_flag=1;		
			}
			elseif(checkPositiveNumber($total_no_transactions_electronic_modes_cash)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Total no transactions electronic modes  : = '.$total_no_transactions_electronic_modes_cash,
					'values'=>'Total no transactions electronic modes '.$numeric_error_message,
				);
				$err_flag=1;			
			}
			elseif(checkPositiveNumber($payment_electronic_modes_cash)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Payment electronic modes  : = '.$payment_electronic_modes_cash,
					'values'=>'Payment electronic modes '.$numeric_error_message,
				);			
				$err_flag=1;
			}
			elseif(checkPositiveNumber($total_no_transactions_other_modes_cash)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Total no transactions other modes  : = '.$total_no_transactions_other_modes_cash,
					'values'=>'Total no transactions other modes '.$numeric_error_message,
				);
				$err_flag=1;			
			}
			elseif(checkPositiveNumber($payment_other_modes_cash)==1)
			{
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Payment other modes  : = '.$payment_other_modes_cash,
					'values'=>'Payment other modes '.$numeric_error_message,
				);
				$err_flag=1;			
			}
			elseif($total_fund_transferred_cash!=$sum_payment_cash)
			{		
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Total fund transferred  : '.$total_fund_transferred_cash.' &#8800; '. $payment_electronic_modes_cash.' + '.$payment_other_modes_cash,
					'values'=>'Total fund transferred should be equal to the sum of payments through Electronic modes + Non-electronic modes',
				);
				$err_flag=1;				
			}
			if($scheme_type==2)
			{
				if(checkPositiveNumber($normative_state_share_fund_transferred_cash)==1)
				{											
					$error_report_array[$state_code]['error'][$i++]=array(
						'label'=>'Normative state share fund transferred = '.$normative_state_share_fund_transferred_cash,
						'values'=>'Normative state share fund transferred '.$numeric_error_message,
					);
					$err_flag=1;			
				}
				elseif(checkPositiveNumber($additional_state_share_fund_transferred_cash)==1)
				{
					$error_report_array[$state_code]['error'][$i++]=array(
						'label'=>'Additional state share fund transferred = '.$additional_state_share_fund_transferred_cash,
						'values'=>'Additional state share fund transferred '.$numeric_error_message,
					);
					$err_flag=1;			
				}
				elseif(checkPositiveNumber($state_share_fund_transferred_to_additional_beneficiaries_cash)==1)
				{
					$error_report_array[$state_code]['error'][$i++]=array(
						'label'=>'State share fund transferred to additional beneficiaries = '.$state_share_fund_transferred_to_additional_beneficiaries_cash,
						'values'=>'State share fund transferred to additional beneficiaries '.$numeric_error_message,
					);
					$err_flag=1;			
				}
				elseif(round($total_fund_transferred_cash,2) != round($sum_fund_transferred_cash,2))
				{															
					$error_report_array[$state_code]['error'][$i++]=array(
						'label'=>'Total fund transferred : '.$total_fund_transferred_cash.' &#8800; '.$central_share_fund_transferred_cash.'+'.$normative_state_share_fund_transferred_cash.'+'.$additional_state_share_fund_transferred_cash.'+'.$state_share_fund_transferred_to_additional_beneficiaries_cash,
						'values'=>'Total fund transferred should be equal to the sum of fund transferred through Central share + Normative State share + Additional State share + State share of fund transferred to additional beneficiaries',
					);
					$err_flag=1;
				}
			}
		}
		if((count($benefit_type) == 1 && in_array(2, $benefit_type) && $err_flag==0) || (count($benefit_type) > 1 && in_array(1, $benefit_type) && in_array(2, $benefit_type) && $err_flag==0))	
		{ 
			if(checkPositiveNumber($total_expenditure_incurred_inkind)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Total expenditure incurred  : = '.$total_expenditure_incurred_inkind,
					'values'=>'Total expenditure incurred '.$numeric_error_message,
				);
				$err_flag=1; 				
			}
			elseif(checkPositiveNumber($central_share_expenditure_incurred_inkind)==1)
			{											
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Central share expenditure incurred : = '.$central_share_expenditure_incurred_inkind,
					'values'=>'Central share expenditure incurred '.$numeric_error_message,
				); 
				$err_flag=1;				
			}
			elseif(checkPositiveNumber($dbt_expenditure_incurred_inkind)==1)
			{		
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'DBT expenditure incurred : = '.$dbt_expenditure_incurred_inkind,
					'values'=>'DBT expenditure incurred '.$numeric_error_message,
				);
				$err_flag=1;	 				
			}
			elseif($dbt_expenditure_incurred_inkind > $total_expenditure_incurred_inkind)
			{			   				   
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'DBT expenditure incurred : '.$dbt_expenditure_incurred_inkind.' <= '.$total_expenditure_incurred_inkind,
					'values'=>'DBT expenditure incurred should be less than or equal to total expenditure incurred ',
				);
				$err_flag=1;				   				 				
			}
			elseif(checkPositiveNumber($normative_state_share_expenditure_incurred_inkind)==1)
			{					
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Normative state share expenditure incurred = '.$normative_state_share_expenditure_incurred_inkind,
					'values'=>'Normative state share expenditure incurred '.$numeric_error_message,
				);
				$err_flag=1;			 				
			}
			elseif(checkPositiveNumber($additional_state_share_expenditure_incurred_inkind)==1)
			{					
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Additional state share expenditure incurred = '.$additional_state_share_expenditure_incurred_inkind,
					'values'=>'Additional state share expenditure incurred '.$numeric_error_message,
				);
				$err_flag=1;			 				
			}
			elseif(checkPositiveNumber($state_share_expenditure_incurred_to_additional_beneficiaries_inkind)==1)
			{					
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'State share expenditure incurred to additional beneficiaries = '.$state_share_expenditure_incurred_to_additional_beneficiaries_inkind,
					'values'=>'State share expenditure incurred to additional beneficiaries '.$numeric_error_message,
				);
				$err_flag=1;			 				
			}
			elseif(round($total_expenditure_incurred_inkind,2) != round($sum_expenditure_incurred_inkind,2))
			{
				$error_report_array[$state_code]['error'][$i++]=array(
			   'label'=>'Total expenditure incurred : '.$total_expenditure_incurred_inkind.' &#8800; '.$central_share_expenditure_incurred_inkind.' + '.$normative_state_share_expenditure_incurred_inkind.' + '.$additional_state_share_expenditure_incurred_inkind.' + '.$state_share_expenditure_incurred_to_additional_beneficiaries_inkind,
			   'values'=>'Total expenditure incurred should be equal to the sum of expenditure incurred through Central share + Normative state share + Additional State share + State share of expenditure incurred to additional beneficiaries ',
				 );	
				$err_flag=1;					
			}
		}
		if($scheme_type==2 && $err_flag==0)	
		{	
			if(checkPositiveNumber($no_of_beneficiaries_normative_central_and_state_share)==1) 
			{
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'No. of beneficiaries normative central and_state share : '.$no_of_beneficiaries_normative_central_and_state_share,
					'values'=>'Number of beneficiaries normative central and_state share '.$numeric_error_message,
				);
				$err_flag=1;
			}
			elseif(checkPositiveNumber($no_of_additional_beneficiaries_supported_by_state)==1) 
			{
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'No. of additional beneficiaries supported by state : '.$no_of_additional_beneficiaries_supported_by_state,
					'values'=>'Number of additional beneficiaries supported by state '.$numeric_error_message,
				);
				$err_flag=1;
			}
			elseif($total_no_of_beneficiaries!=$sum_beneficiaries ) 
			{
				$error_report_array[$state_code]['error'][$i++]=array(
					'label'=>'Total no of beneficiaries : '.$total_no_of_beneficiaries.' &#8800; '.$no_of_beneficiaries_normative_central_and_state_share.' + '.$no_of_additional_beneficiaries_supported_by_state,
					'values'=>'Total number of beneficiaries should be equal to the sum of beneficiaries through normative Central and State share + Additional beneficiaries supported by State',
				);		
				$err_flag=1;
			}
			
		}
	// beneficiary comulative check
	// condition expect from these scheme ids 624-pahal,674-mgnrega, 1026-pds.
		$scheme_ids_arr = array(624,674,1026);
		if(!in_array($schemeid, $scheme_ids_arr) && $err_flag==0)
		{
			$data_month = intval($datamonth);
			
			if($data_month != 4){
				global $con;
				$fy_year = $start.'_'.$end;
				$sql="SELECT no_beneficiaries_normative as total_no_beneficiaries_normative, no_beneficiaries_digitised as total_no_beneficiaries_digitised, no_beneficiaries_aadhaar as total_no_beneficiaries_aadhaar, no_beneficiaries_mobile as total_no_beneficiaries_mobile, reporting_month FROM dbt_scheme_beneficiary_data where scheme_id = $schemeid ";
				$forLocation="";	// identify the state name and/or district name in error message
				if($webservice_data_type==1)
				{
					$sql.=" and state_code = $state_code ";
					$forLocation.=" for $state_name.";
				}
				elseif($webservice_data_type==2)
				{
					$sql.=" and state_code = $state_code and district_code = $district_code ";
					$forLocation.=" for $state_name ($district_name).";
				}
				$sql.=" and financial_year = '$fy_year'";
				$result = mysqli_query($con, $sql);		
				
				
				$check_data_arr = mysqli_fetch_array($result);
				if($check_data_arr){
					if($check_data_arr['total_no_beneficiaries_normative']>$total_no_of_beneficiaries)
					{		
				      		 						
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Beneficiary = '.$total_no_of_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_normative'],
							'values'=>"Total number of beneficiary should be greater than or equal to last reported month total beneficiary for this financial year $forLocation",
						);
						$err_flag=1;		
					}
					elseif($check_data_arr['total_no_beneficiaries_digitised']>$no_of_beneficiaries_record_digitized)
					{											
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Digitised Beneficiary = '.$no_of_beneficiaries_record_digitized.' < '.$check_data_arr['total_no_beneficiaries_digitised'],
							'values'=>"Total number of digitised beneficiary should be greater than or equal to last reported month total digitised beneficiary for this financial year $forLocation",
						);
						$err_flag=1;	
					}
					elseif($check_data_arr['total_no_beneficiaries_aadhaar']>$no_of_authenticated_seeded_beneficiaries)
					{											
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Aadhaar Beneficiary = '.$no_of_authenticated_seeded_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_aadhaar'],
							'values'=>"Total number of aadhaar beneficiary should be greater than or equal to last reported month total aadhaar beneficiary for this financial year $forLocation",
						);
						$err_flag=1;		
					}
					elseif($check_data_arr['total_no_beneficiaries_mobile']>$no_of_beneficiaries_whom_mobile_no_captured)
					{											
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Beneficiary Whome Mobile No Captured = '.$no_of_beneficiaries_whom_mobile_no_captured.' < '.$check_data_arr['total_no_beneficiaries_mobile'],
							'values'=>"Total number of beneficiary whome mobile no captured should be greater than or equal to last reported month total beneficiary whome mobile no captured for this financial year $forLocation",
						);
						$err_flag=1;		
					}
				} // end prev month data check
			} // end data month check
		}
		$i++;
		if($err_flag==1)
		{
		//	break;
			$ind=$i-2;
			$code=($district_code>0) ? $district_code : $state_code;
			$invalidResponse.=$code.":".$error_report_array[$state_code]['error'][$ind]["values"].",";
			$err_flag=0;
		}
		else
		{
			$validatedResponse[]=$val;
		}
	}
	$error = 0;
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
	$returnvalue['log'] = $txt;
	if($error > 0) {
		$returnvalue['error'] = $error_report_array;
		$returnvalue['validation_message'] = 'Web-service validation failed';
		$returnvalue['err_msg'] = $err_msg;
	} 
	else {
		$returnvalue['error'] = '';
	}
	$returnvalue['validatedResponse'] = $validatedResponse;
	$returnvalue['invalidResponse'] = $invalidResponse;
	return $returnvalue;
} // generalValidation end



//Send validation error email
function validation_error_email ($error_report_array,$schemeid,$datamonth,$benefit_type,$start,$end,$nodalp_email,$cc_email){
	
	global $con;	
	$msg_body = '';	
	
	$datainfo = mysqli_query($con,"SELECT sm.scheme_type_id as scheme_type, sm.scheme_code, sd.scheme_name, m.ministry_name FROM dbt_scheme_master as sm JOIN dbt_scheme_details as sd on sm.scheme_id = sd.scheme_id JOIN dbt_ministry_details as m on sm.ministry_id = m.ministry_id where sm.scheme_id = '".$schemeid."'");
	
	$datainfocount = mysqli_fetch_object($datainfo);
	$nodalp_email = implode(',', $nodalp_email); 
	$cc_email = implode(',', $cc_email); 
	$scheme_type = $datainfocount->scheme_type; 		
	$scheme_name=$datainfocount->scheme_name;
	$ministry_name=$datainfocount->ministry_name;
	$scheme_codification=$datainfocount->scheme_code;
	$data_updated = date('d/m/Y h:i:s a');

	$month_num = $datamonth;
	$month_name = date("M", mktime(0, 0, 0, $month_num, 10));
	$year_name = date("Y");
	if(intval($month_num)<=3){$f_year= $end;} else{$f_year= $start;}
	
	if (count($benefit_type) == 1 && in_array(1, $benefit_type)) {
		$benefit_type_name = "CASH";
	} else if (count($benefit_type) == 1 && in_array(2, $benefit_type)) {
		$benefit_type_name = "In Kind";
	} else if (count($benefit_type) > 1 && in_array(1, $benefit_type) && in_array(2, $benefit_type)) {
		$benefit_type_name = "Cash and In Kind";
	} else {
		$benefit_type_name = "";
	}
	
	$subject = "DBT Web-service rejection alert: ".$ministry_name." (".$scheme_codification."), " . $month_name."-".$f_year; 

	$message = '<span style="font-size:14px">Dear User,
	<br>Web-service response for below mentioned scheme and month has been rejected by DBT Bharat Portal. Please find the web-service response validation alert details updated on '.$data_updated.'.</span><br><br>';
	
	$message .= '<table style="font-family:Trebuchet MS, Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;">';
	$message .= '<tr><td width="52%" style="border: 1px solid #ddd;padding: 8px;"><b>Ministry Name</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$ministry_name.'</td></tr>';
	$message .= '<tr><td style="border: 1px solid #ddd;padding: 8px;"><b>Scheme Name</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$scheme_name.'</td></tr>';
	$message .= '<tr><td style="border: 1px solid #ddd;padding: 8px;"><b>Scheme Code</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$scheme_codification.'</td></tr>';
	$message .= '<tr><td style="border: 1px solid #ddd;padding: 8px;"><b>Benefit Type</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$benefit_type_name.'</td></tr>';
	$message .= '<tr><td style="border: 1px solid #ddd;padding: 8px;"><b>Month, Financial Year</b></td><td style="border: 1px solid #ddd;padding: 8px;">'.$month_name.', '.$start.'-'.substr($end,2,7).'</td></tr>';	
	$message .='</table><br>';
	$msg_body .= "<table style='border-collapse: collapse;width: 100%;'><tr style='border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;color: white;background-color:#999999'><th style='border: 1px solid #ddd; font-size:14px'>STATE/UT(LGD CODE)</th><th style='border: 1px solid #ddd; font-size:14px'> WEB-SERVICE RESPONSE VALIDATION DETAILS </th></tr>";
		
	foreach($error_report_array as $key_error =>$val_error) {
		if(count($val_error['error']) > 0){
			 $response = true; 
			$msg_body .='<tr><td style="text-align:center;border: 1px solid #c3c2c2;padding: 8px;background-color:#ddd"><b>'.$val_error['state_name'].'('.$val_error['state_code'].')';
			if($val_error['district_code']<>"")	$msg_body .=' <b>['.$val_error['district_name'].'('.$val_error['district_code'].')] ';
			$msg_body .='</b></td><td  style="border: 1px solid #c3c2c2;padding: 8px; background-color: #ddd;" ><b>COMMENTS FOR CORRECTION</b></td></tr>'; 
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
function webserviceauditlogfunction($scheme_id, $data_day, $data_month, $financial_year, $is_updated, $is_success, $remarks, $webservice_execution_mode, $webservice_executed_for_date, $request_ip, $error_code, $rowCount=null, $webservice_execution_log_id, $error_message) {
	global $con;
	$created = date('Y-m-d H:i:s');
	// triger name trig_center_webservice_execution_log to creat log of this record before update
	if($rowCount==0 || $rowCount==null || $webservice_execution_log_id>0)
	{
		// triger name trig_center_webservice_execution_log to creat log of thi record before update
		$sql = "UPDATE dbt_center_webservice_execution_log SET data_day='$data_day', data_month='$data_month', financial_year='$financial_year', is_updated='$is_updated', is_success='$is_success', error_code='$error_code', remarks='$remarks', webservice_execution_mode='$webservice_execution_mode', webservice_executed_for_date='$webservice_executed_for_date', ip_address='$request_ip', error_msg = '$error_message', updated=NOW() WHERE center_webservice_execution_log_id=$webservice_execution_log_id";
		mysqli_query($con, $sql);
		$lastinsertedid = $webservice_execution_log_id;
		$is_resolved = ($error_code == 0) ? 'Y' : 'N';
		if($rowCount==null)
		{
			if($error_code > 0){
				$sql_query = "INSERT INTO dbt_failed_center_webservice_details (webservice_execution_log_id, is_resolved, ip_address, created) VALUES ($lastinsertedid, 'N', '$request_ip', '$created')";
				mysqli_query($con, $sql_query);
			}
		}
		else
		{
			$sql_query = "update dbt_failed_center_webservice_details set is_resolved = '$is_resolved' where webservice_execution_log_id = $lastinsertedid";
			mysqli_query($con, $sql_query);
		}
	}
	else
	{
		$sql = "INSERT INTO dbt_center_webservice_execution_log (scheme_id, data_day, data_month, financial_year, is_updated, is_success, error_code, remarks, webservice_execution_mode, webservice_executed_for_date, ip_address, created, error_msg) VALUES ('$scheme_id', '$data_day', '$data_month', '$financial_year', '$is_updated', '$is_success', '$error_code', '$remarks', '$webservice_execution_mode', '$webservice_executed_for_date', '$request_ip', '$created', '$error_message')";
		mysqli_query($con, $sql);
		$lastinsertedid = mysqli_insert_id($con);
		if($error_code > 0){
			$sql_query = "INSERT INTO dbt_failed_center_webservice_details (webservice_execution_log_id, is_resolved, ip_address, created) VALUES ($lastinsertedid, 'N', '$request_ip', '$created')";
			mysqli_query($con, $sql_query);
		}
	}
	return $lastinsertedid;
}
function createLogEntryBeforeAutoExecution($scheme_id, $data_day, $data_month, $financial_year, $webservice_execution_mode, $request_date, $request_ip)
{
	global $con;
	$error_code=null;
	$is_updated=$is_success="no";
	$sql = "INSERT INTO dbt_center_webservice_execution_log (scheme_id, data_day, data_month, financial_year, is_updated, is_success, error_code, webservice_execution_mode, request_date, webservice_executed_for_date, ip_address, created) VALUES ('$scheme_id', '$data_day', '$data_month', '$financial_year', '$is_updated', '$is_success', '$error_code', '$webservice_execution_mode', '$request_date', '', '$request_ip', NOW())";
	mysqli_query($con, $sql);
	$lastinsertedid = mysqli_insert_id($con);
	return $lastinsertedid;
}
// update webservice status if service failed
function inactive_webservice_status($webservice_id){
	global $con;
	if($webservice_id>0){
		$sql = "UPDATE dbt_webservice_details SET webservice_status = 0 WHERE webservice_id = $webservice_id";
		mysqli_query($con, $sql);
	}
}

// insert_data_in_temp_table
function insert_data_in_temp_table($responsedata,$schemeid,$userid,$ip_address,$log_id){
	global $con;
	foreach($responsedata as $data_val){
		$state_code = !is_array($data_val["location"]["state_code"]) ? $data_val["location"]["state_code"] : "";
		$state_name = !is_array($data_val["location"]["state_name"]) ? $data_val["location"]["state_name"] : "";
		$district_code = isset($data_val["location"]["district_code"]) ? $data_val["location"]["district_code"] : "";
		$district_name = isset($data_val["location"]["district_name"]) ? $data_val["location"]["district_name"] : "";

		$schemecode = !is_array($data_val["general-information"]["schemecode"]) ? $data_val["general-information"]["schemecode"] : "";

		$year = !is_array($data_val["scheme-progress"]["year"]) ? $data_val["scheme-progress"]["year"] : "";
		$month = !is_array($data_val["scheme-progress"]["month"]) ? $data_val["scheme-progress"]["month"] : "";
		$day = isset($data_val["scheme-progress"]["day"]) ? $data_val["scheme-progress"]["day"] : "";
		$requestid = isset($data_val["scheme-progress"]["requestid"]) ? $data_val["scheme-progress"]["requestid"] : "";

		$beneficiary_type = isset($data_val["beneficiary-details"]["beneficiary_type"]) ? $data_val["beneficiary-details"]["beneficiary_type"] : "";
		$number_of_groups_shg = isset($data_val["beneficiary-details"]["number_of_groups_shg"]) ? $data_val["beneficiary-details"]["number_of_groups_shg"] : "";
		$no_of_beneficiaries_normative_central_and_state_share = !is_array($data_val["beneficiary-details"]["no_of_beneficiaries_normative_central_and_state_share"]) ? $data_val["beneficiary-details"]["no_of_beneficiaries_normative_central_and_state_share"] : "";
		$no_of_additional_beneficiaries_supported_by_state = !is_array($data_val["beneficiary-details"]["no_of_additional_beneficiaries_supported_by_state"]) ? $data_val["beneficiary-details"]["no_of_additional_beneficiaries_supported_by_state"] : "";
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
		$created = date('Y-m-d H:i:s');
		
		$query = mysqli_query($con,"INSERT INTO dbt_center_webservice_temp_data SET
			scheme_id = $schemeid,
			center_webservice_execution_log_id = $log_id,
			field_1 = '$state_code',
			field_2 = '$state_name',
			field_3 = '$district_code',
			field_4 = '$district_name',
			field_5 = '$schemecode',
			field_6 = '$year',
			field_7 = '$month',
			field_8 = '$day',
			field_9 = '$requestid',
			field_10 = '$beneficiary_type',
			field_11 = '$number_of_groups_shg',
			field_12 = '$no_of_beneficiaries_normative_central_and_state_share',
			field_13 = '$no_of_additional_beneficiaries_supported_by_state',
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
			updated_by = '$userid',
			created = '$created',
			ip_address = '$ip_address'
		");
	}
}