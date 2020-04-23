<?php
error_reporting(E_ALL);
//ini_set('display_errors', 1);
/*---(Data Validation  )----------------------------------|
  |	 * Purpose:-Prevent data discrepancy                         |
  |--------------------------------------------------------------|
*/
function generalValidation_replace($getValue,$schemeid,$datamonth,$benefit_type,$start,$end,$txt,$month_index)
{	
	// echo "<pre>";
	// print_r($getValue);
	// die;
	global $con;
	$returnvalue=array();
	
	$datainfo = mysqli_query($con,"SELECT sm.scheme_type_id as scheme_type, sm.scheme_code, sd.scheme_name, m.ministry_name, e.scheme_eligibility_type_id as dbt_eligibility_type FROM dbt_scheme_master as sm JOIN dbt_scheme_details as sd on sm.scheme_id = sd.scheme_id JOIN dbt_ministry_details as m on sm.ministry_id = m.ministry_id JOIN dbt_scheme_eligibility_type_relation as e on sm.scheme_id = e.scheme_id where sm.scheme_id = '".$schemeid."'");
	$datainfocount = mysqli_fetch_object($datainfo);
	$scheme_type = $datainfocount->scheme_type; 		
	$dbt_eligibility_type = $datainfocount->dbt_eligibility_type; 
	$ministry_name=$datainfocount->ministry_name;
	$scheme_code=$datainfocount->scheme_code;
	

	$month_num = $datamonth;
	$month_name = date("M", mktime(0, 0, 0, $month_num, 10));
	
	if(intval($month_num)<=3)	$f_year= $end;
	else						$f_year= $start;
	
	
	$unit_of_measurement_inkind_array = array();
	$subject = "DBT Web-service rejection alert: ".$ministry_name." (".$scheme_code."), " . $month_name."-".$f_year; 
	$i=1;
	$cont=1;
	$error_report_array=array();
	//if($getValue['0']['general-information']['schemecode'] != '') {
		$schemecode = $getValue['0']['general-information']['schemecode'];
	//} else {
	//	$schemecode = '';
	//}
	
	$txt .= "Scheme Code : ".$schemecode."\n\n";
	$k = 0;
	$err_flag=0;
	
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
					'label'=>'Central share fund transferred  : = '.$central_share_fund_transferred_cash,
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
	if($err_flag==0)
		{
			$data_month = intval($datamonth);
			
			if($data_month != 4 && $loopcount>1){
				global $con;
				$fy_year = $start.'_'.$end;
				if($data_month==1)
				{
					$reporting_month=12;
				}
				else
				{
					$reporting_month=$data_month-1;
				}
				$sql="SELECT no_beneficiaries_normative as total_no_beneficiaries_normative, no_beneficiaries_digitised as total_no_beneficiaries_digitised, no_beneficiaries_aadhaar as total_no_beneficiaries_aadhaar, no_beneficiaries_mobile as total_no_beneficiaries_mobile, reporting_month FROM dbt_temp_beneficiary_data where scheme_id = $schemeid and reporting_month=$reporting_month ";
				if($state_code != "")
				{
					$sql.=" and state_code = $state_code ";
				}
				$sql.=" and financial_year = '$fy_year'";
				$result = mysqli_query($con, $sql);		
				
				
				$check_data_arr = mysqli_fetch_array($result);
				if($check_data_arr){
					if($check_data_arr['total_no_beneficiaries_normative']>$total_no_of_beneficiaries)
					{		
				      		 						
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Beneficiary = '.$total_no_of_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_normative'],
							'values'=>"Total number of beneficiary should be greater than or equal to last reported month total beneficiary for this financial year ",
						);
						$err_flag=1;		
					}
					elseif($check_data_arr['total_no_beneficiaries_digitised']>$no_of_beneficiaries_record_digitized)
					{											
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Digitised Beneficiary = '.$no_of_beneficiaries_record_digitized.' < '.$check_data_arr['total_no_beneficiaries_digitised'],
							'values'=>"Total number of digitised beneficiary should be greater than or equal to last reported month total digitised beneficiary for this financial year ",
						);
						$err_flag=1;	
					}
					elseif($check_data_arr['total_no_beneficiaries_aadhaar']>$no_of_authenticated_seeded_beneficiaries)
					{											
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Aadhaar Beneficiary = '.$no_of_authenticated_seeded_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_aadhaar'],
							'values'=>"Total number of aadhaar beneficiary should be greater than or equal to last reported month total aadhaar beneficiary for this financial year ",
						);
						$err_flag=1;		
					}
					elseif($check_data_arr['total_no_beneficiaries_mobile']>$no_of_beneficiaries_whom_mobile_no_captured)
					{											
						$error_report_array[$state_code]['error'][$i++]=array(
							'label'=>'Total No Of Beneficiary Whome Mobile No Captured = '.$no_of_beneficiaries_whom_mobile_no_captured.' < '.$check_data_arr['total_no_beneficiaries_mobile'],
							'values'=>"Total number of beneficiary whome mobile no captured should be greater than or equal to last reported month total beneficiary whome mobile no captured for this financial year ",
						);
						$err_flag=1;		
					}
				} // end prev month data check
			} // end data month check
		}
		$i++;
		if($err_flag==1)
		{
			break;
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
	
	return $returnvalue;
} // generalValidation end
function insert_temp_replace_data($responsedata, $schemeid, $benefit_type)
{
	global $con;
	// variable initialization
	$success=0;
	foreach($responsedata as $dataval) {
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
		$temp_query = "INSERT INTO dbt_temp_beneficiary_data SET  no_beneficiaries_normative = '$total_no_of_beneficiaries', no_beneficiaries_additional_state = '$no_of_additional_beneficiaries_supported_by_state', no_beneficiaries_digitised = '$no_of_beneficiaries_record_digitized', no_beneficiaries_aadhaar = '$no_of_authenticated_seeded_beneficiaries', no_beneficiaries_mobile = '$no_of_beneficiaries_whom_mobile_no_captured', no_group = '$no_of_shg_group', data_mode_id = '2', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', beneficiary_data_status = '1', ip_address = '$ip_address', reporting_month = '$month' ,created = '$created', scheme_id = '$schemeid', state_code = $state_code, district_code = $district_code, financial_year = '$financial_year' ";
	
		mysqli_query($con, $temp_query);
		// update transaction data
			
		
		if (in_array(1, $benefit_type)){
			
			$temp_transaction_query = "INSERT INTO dbt_temp_beneficiary_transaction_data SET  total_fund_transferred_normative = '$total_fund_transferred_normative', fund_transferred_state_normative = '$fund_transferred_state_normative', fund_transferred_center_normative = '$fund_transferred_center_normative', total_fund_transfered_State_additional_x = '$total_fund_transfered_State_additional_x', total_fund_transfered_state_additional_y = '$total_fund_transfered_state_additional_y', total_fund_electronic_authenticated = '$total_fund_electronic_authenticated', total_fund_non_electronic_authenticated = '$total_fund_non_electronic_authenticated', no_transaction_electronic_authenticated = '$no_transaction_electronic_authenticated', no_transaction_non_electronic_authenticated = '$no_transaction_non_electronic_authenticated', unit_of_measurement = '', no_quantity = '', additional_parameter1 = '$additional_parameter1', additional_parameter2 = '$additional_parameter2', additional_parameter3 = '$additional_parameter3', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', b_typewise_data_status = '1', ip_address = '$ip_address' , created = '$created', scheme_id = '$schemeid', scheme_benefit_type_id = '1', state_code = $state_code, district_code = $district_code, reporting_month = '$month', financial_year = '$financial_year'";
			$sql=mysqli_query($con, $temp_transaction_query);
			if(mysqli_insert_id($con)>0)	$success++;
		}
		if (in_array(2, $benefit_type)){
			
			$temp_data_query_kind = "INSERT INTO dbt_temp_beneficiary_transaction_data SET  total_fund_transferred_normative = '$total_expenditure_incurred_inkind', fund_transferred_state_normative = '$normative_state_share_expenditure_incurred_inkind', fund_transferred_center_normative = '$central_share_expenditure_incurred_inkind', total_fund_transfered_State_additional_x = '$additional_state_share_expenditure_incurred_inkind', total_fund_transfered_state_additional_y = '$state_share_expenditure_incurred_to_additional_beneficiaries_inkind', total_fund_electronic_authenticated = '$dbt_expenditure_incurred_inkind', total_fund_non_electronic_authenticated = '', no_transaction_electronic_authenticated = '$no_of_authenticated_transactions_inkind', no_transaction_non_electronic_authenticated = '', unit_of_measurement = '$unit_of_measurement_inkind', no_quantity = '$quantity_transferred_inkind', additional_parameter1 = '$additional_parameter1', additional_parameter2 = '$additional_parameter2', additional_parameter3 = '$additional_parameter3', scheme_transaction_from_date = '$scheme_transaction_from_date', scheme_transaction_to_date = '$scheme_transaction_to_date', b_typewise_data_status = '1', ip_address = '$ip_address' , created = '$created', scheme_id = '$schemeid',  state_code = $state_code, district_code = $district_code, reporting_month = '$month', financial_year = '$financial_year'";
			$sql=mysqli_query($con, $temp_data_query_kind);
			if(mysqli_insert_id($con)>0)	$success++;
		}
	} 
	return	$success;
} 
function DataValidation_with_existing_data($schemeid,$from_month,$to_month,$state_code,$fy_year)
{	
	global $con;
	$err_flag=0;
	$from_month = intval($from_month);
	$to_month = intval($to_month);		
	$latest_data_month=getlatestdatamonth($schemeid,$state_code,$fy_year);
				
	if($from_month != 4 && $err_flag==0){
		if($from_month==1)
		{
			$reporting_month=12;
		}
		else
		{
			$reporting_month=$from_month-1;
		}
		$sql="SELECT no_beneficiaries_normative as total_no_beneficiaries_normative, no_beneficiaries_digitised as total_no_beneficiaries_digitised, no_beneficiaries_aadhaar as total_no_beneficiaries_aadhaar, no_beneficiaries_mobile as total_no_beneficiaries_mobile, reporting_month, state_code FROM dbt_scheme_beneficiary_data_log1 where scheme_id = $schemeid and reporting_month=$reporting_month ";
		if($state_code != "")
		{
			$sql.=" and state_code = $state_code ";
		}
		$sql.=" and financial_year = '$fy_year'";
		$result = mysqli_query($con, $sql);
		while($dat=mysqli_fetch_array($result))
		{
			$db_data_arr[] = $dat;
		}
		//Get temp data from month to check cumulative
		$temp_sql="SELECT no_beneficiaries_normative as total_no_beneficiaries_normative, no_beneficiaries_digitised as total_no_beneficiaries_digitised, no_beneficiaries_aadhaar as total_no_beneficiaries_aadhaar, no_beneficiaries_mobile as total_no_beneficiaries_mobile, reporting_month, state_code FROM dbt_temp_beneficiary_data where scheme_id = $schemeid and reporting_month=$from_month ";
		if($state_code != "")
		{
			$temp_sql.=" and state_code = $state_code ";
		}
		$temp_sql.=" and financial_year = '$fy_year'";
		$tempresult = mysqli_query($con, $temp_sql);
		while($dat=mysqli_fetch_array($tempresult))
		{
			$temp_data_arr[] = $dat;
		}
		if(count($db_data_arr) <> count($temp_data_arr))
		{
			$err_flag=1;
		}
		else
		{
			foreach($db_data_arr AS $key => $check_data_arr)
			{
				$total_no_of_beneficiaries=$temp_data_arr[$key]['total_no_beneficiaries_normative'];
				$no_of_beneficiaries_record_digitized=$temp_data_arr[$key]['total_no_beneficiaries_digitised'];
				$no_of_authenticated_seeded_beneficiaries=$temp_data_arr[$key]['total_no_beneficiaries_aadhaar'];
				$no_of_beneficiaries_whom_mobile_no_captured=$temp_data_arr[$key]['total_no_beneficiaries_mobile'];

				if($check_data_arr['total_no_beneficiaries_normative']>$total_no_of_beneficiaries)
				{
					$error_report_array[$state_code]['error']=array(
						'label'=>'Total No Of Beneficiary = '.$total_no_of_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_normative'],
						'values'=>"Total number of beneficiary should be greater than or equal to last reported month total beneficiary for this financial year ",
					);
					$err_flag=1;		
				}
				elseif($check_data_arr['total_no_beneficiaries_digitised']>$no_of_beneficiaries_record_digitized)
				{											
					$error_report_array[$state_code]['error']=array(
						'label'=>'Total No Of Digitised Beneficiary = '.$no_of_beneficiaries_record_digitized.' < '.$check_data_arr['total_no_beneficiaries_digitised'],
						'values'=>"Total number of digitised beneficiary should be greater than or equal to last reported month total digitised beneficiary for this financial year ",
					);
					$err_flag=1;	
				}
				elseif($check_data_arr['total_no_beneficiaries_aadhaar']>$no_of_authenticated_seeded_beneficiaries)
				{											
					$error_report_array[$state_code]['error']=array(
						'label'=>'Total No Of Aadhaar Beneficiary = '.$no_of_authenticated_seeded_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_aadhaar'],
						'values'=>"Total number of aadhaar beneficiary should be greater than or equal to last reported month total aadhaar beneficiary for this financial year ",
					);
					$err_flag=1;		
				}
				elseif($check_data_arr['total_no_beneficiaries_mobile']>$no_of_beneficiaries_whom_mobile_no_captured)
				{											
					$error_report_array[$state_code]['error']=array(
						'label'=>'Total No Of Beneficiary Whome Mobile No Captured = '.$no_of_beneficiaries_whom_mobile_no_captured.' < '.$check_data_arr['total_no_beneficiaries_mobile'],
						'values'=>"Total number of beneficiary whome mobile no captured should be greater than or equal to last reported month total beneficiary whome mobile no captured for this financial year ",
					);
					$err_flag=1;		
				}
			}
		}
	}
	if($to_month != 3 && $latest_data_month!=4 && $latest_data_month!=$to_month && $err_flag==0){
		if($to_month==12)
		{
			$reporting_month=1;
		}
		else
		{
			$reporting_month=$to_month+1;
		}
		$ben_table_name="";
		if($latest_data_month==$reporting_month)
		{
			$ben_table_name="dbt_scheme_beneficiary_data";
		}
		else
		{
			$ben_table_name="dbt_scheme_beneficiary_data_log1";
		}
		$sql="SELECT no_beneficiaries_normative as total_no_beneficiaries_normative, no_beneficiaries_digitised as total_no_beneficiaries_digitised, no_beneficiaries_aadhaar as total_no_beneficiaries_aadhaar, no_beneficiaries_mobile as total_no_beneficiaries_mobile, reporting_month FROM $ben_table_name where scheme_id = $schemeid and reporting_month=$reporting_month ";
		if($state_code != "")
		{
			$sql.=" and state_code = $state_code ";
		}
		$sql.=" and financial_year = '$fy_year'";
		$result = mysqli_query($con, $sql);		
		$check_data_arr = mysqli_fetch_array($result);
		
		//Get temp data from month to check cumulative
		$temp_sql="SELECT no_beneficiaries_normative as total_no_beneficiaries_normative, no_beneficiaries_digitised as total_no_beneficiaries_digitised, no_beneficiaries_aadhaar as total_no_beneficiaries_aadhaar, no_beneficiaries_mobile as total_no_beneficiaries_mobile, reporting_month FROM dbt_temp_beneficiary_data where scheme_id = $schemeid and reporting_month=$to_month ";
		if($state_code != "")
		{
			$temp_sql.=" and state_code = $state_code ";
		}
		$temp_sql.=" and financial_year = '$fy_year'";
		$tempresult = mysqli_query($con, $temp_sql);		
		$temp_data_arr = mysqli_fetch_array($tempresult);
		if($temp_data_arr){
			$total_no_of_beneficiaries=$temp_data_arr['total_no_beneficiaries_normative'];
			$no_of_beneficiaries_record_digitized=$temp_data_arr['total_no_beneficiaries_digitised'];
			$no_of_authenticated_seeded_beneficiaries=$temp_data_arr['total_no_beneficiaries_aadhaar'];
			$no_of_beneficiaries_whom_mobile_no_captured=$temp_data_arr['total_no_beneficiaries_mobile'];
		}
		
		if($check_data_arr){
			if($check_data_arr['total_no_beneficiaries_normative']<$total_no_of_beneficiaries)
			{		
											
				$error_report_array[$state_code]['error']=array(
					'label'=>'Total No Of Beneficiary = '.$total_no_of_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_normative'],
					'values'=>"Total number of beneficiary should be greater than or equal to last reported month total beneficiary for this financial year ",
				);
				$err_flag=1;		
			}
			elseif($check_data_arr['total_no_beneficiaries_digitised']<$no_of_beneficiaries_record_digitized)
			{											
				$error_report_array[$state_code]['error']=array(
					'label'=>'Total No Of Digitised Beneficiary = '.$no_of_beneficiaries_record_digitized.' < '.$check_data_arr['total_no_beneficiaries_digitised'],
					'values'=>"Total number of digitised beneficiary should be greater than or equal to last reported month total digitised beneficiary for this financial year ",
				);
				$err_flag=1;	
			}
			elseif($check_data_arr['total_no_beneficiaries_aadhaar']<$no_of_authenticated_seeded_beneficiaries)
			{											
				$error_report_array[$state_code]['error']=array(
					'label'=>'Total No Of Aadhaar Beneficiary = '.$no_of_authenticated_seeded_beneficiaries.' < '.$check_data_arr['total_no_beneficiaries_aadhaar'],
					'values'=>"Total number of aadhaar beneficiary should be greater than or equal to last reported month total aadhaar beneficiary for this financial year ",
				);
				$err_flag=1;		
			}
			elseif($check_data_arr['total_no_beneficiaries_mobile']<$no_of_beneficiaries_whom_mobile_no_captured)
			{											
				$error_report_array[$state_code]['error']=array(
					'label'=>'Total No Of Beneficiary Whome Mobile No Captured = '.$no_of_beneficiaries_whom_mobile_no_captured.' < '.$check_data_arr['total_no_beneficiaries_mobile'],
					'values'=>"Total number of beneficiary whome mobile no captured should be greater than or equal to last reported month total beneficiary whome mobile no captured for this financial year ",
				);
				$err_flag=1;		
			}
		} // end prev month data check
	}
	if($err_flag==1)
	{
		$data[0] = 1;
		$data[1] = $error_report_array[$state_code]['error']['values'];
	}
	else
	{
		$data[0] = 0;
		$data[1] = "";
	}
	
	return $data;
}
// function gettempbeneficiarydata($schemeid,$from_month,$to_month,$fy_year,$state_code)
// {
	// global $con;
	// $sql="SELECT no_beneficiaries_normative, no_beneficiaries_additional_state , no_beneficiaries_digitised, no_beneficiaries_aadhaar, no_beneficiaries_mobile, no_group, data_mode_id, scheme_transaction_from_date, scheme_transaction_to_date, beneficiary_data_status, ip_address, reporting_month, created, scheme_id, state_code,district_code, financial_year FROM dbt_temp_beneficiary_data where scheme_id = $schemeid and reporting_month between $from_month and $to_month and financial_year = '$fy_year'";
	// if($state_code != "")
	// {
		// $sql.=" and state_code = $state_code ";
	// }
	// $sql.=" order by reporting_month,state_code";
	// $result = mysqli_query($con, $sql);
	// if(mysqli_num_rows($result)>0)
	// {
		// while($res=mysqli_fetch_array($result))	$data_arr[] = $res;
	// }
	// else	$data_arr=0;
	// return $data_arr;
// }
// function gettemptransactiondata($schemeid,$from_month,$to_month,$fy_year,$state_code)
// {
	// global $con;
	// $sql="SELECT total_fund_transferred_normative, fund_transferred_state_normative , fund_transferred_center_normative , total_fund_transfered_State_additional_x , total_fund_transfered_state_additional_y , total_fund_electronic_authenticated , total_fund_non_electronic_authenticated , no_transaction_electronic_authenticated , no_transaction_non_electronic_authenticated , unit_of_measurement, no_quantity , additional_parameter1 , additional_parameter2 , additional_parameter3 , scheme_transaction_from_date , scheme_transaction_to_date , b_typewise_data_status,ip_address  ,created, scheme_id, scheme_benefit_type_id, state_code, district_code, reporting_month, financial_year FROM dbt_temp_beneficiary_transaction_data where scheme_id = $schemeid and reporting_month between $from_month and $to_month  and financial_year = '$fy_year'";
	// if($state_code != "")
	// {
		// $sql.=" and state_code = $state_code ";
	// }
	// $sql.=" order by reporting_month,state_code";
	// $result = mysqli_query($con, $sql);
	// if(mysqli_num_rows($result)>0)
	// {
		// while($res=mysqli_fetch_array($result))	$data_arr[] = $res;
	// }
	// else	$data_arr=0;
	// return $data_arr;
// }
function getlatestdatamonth($schemeid,$state_code,$fy_year)
{
	global $con;
	$sql="select reporting_month FROM dbt_scheme_beneficiary_data where scheme_id = $schemeid ";
	if($state_code != "")
	{
		$sql.=" and state_code = $state_code ";
	}
	$sql.=" and financial_year = '$fy_year'";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result)==0)	$latest_data_month=0;
	else
	{
		$get_data_arr = mysqli_fetch_array($result);
		$latest_data_month=$get_data_arr['reporting_month'];
	}
	return $latest_data_month;
}
function deletetempdata($schemeid,$from_month,$to_month,$fy_year)
{
	global $con;
	$sql="delete FROM dbt_temp_beneficiary_data where scheme_id = $schemeid and reporting_month between $from_month and $to_month  and financial_year = '$fy_year'";
	if(mysqli_query($con, $sql))
	{
		$sql="delete FROM dbt_temp_beneficiary_transaction_data where scheme_id = $schemeid and reporting_month between $from_month and $to_month  and financial_year = '$fy_year'";
		if(mysqli_query($con, $sql))	$temp=1;
		else							$temp=0;
	}
	return $temp;
}
function update_replace_validation_status($data_replacement_request_id,$data_replaced)
{
	global $con;
	if($data_replacement_request_id>0){
		$sql = "UPDATE dbt_data_replacement_request SET data_replaced = '$data_replaced' WHERE data_replacement_request_id = $data_replacement_request_id";
	    $temp=mysqli_query($con, $sql);
		$data=mysqli_affected_rows($con);
		return $data;
	}
}
function createLogEntry($scheme_id, $data_day, $data_month, $financial_year, $webservice_execution_mode, $request_date, $request_ip, $request_type, $data_replacement_request_id,$error_code,$error_msg)
{
	global $con;
	$is_updated=$is_success="no";
	$sql = "INSERT INTO dbt_center_webservice_execution_log (scheme_id, data_day, data_month, financial_year, is_updated, is_success, error_code, webservice_execution_mode, request_date, webservice_executed_for_date, ip_address, created,request_type,data_replacement_request_id,error_msg) VALUES ('$scheme_id', '$data_day', '$data_month', '$financial_year', '$is_updated', '$is_success', '$error_code', '$webservice_execution_mode', '$request_date', '', '$request_ip', NOW(),'$request_type',$data_replacement_request_id,'$error_msg')";
	mysqli_query($con, $sql);
	$lastinsertedid = mysqli_insert_id($con);
	return $lastinsertedid;
}
function get_scheme_beneficiary_id ($schemeid,$state_code,$fy_year)
{
	global $con;
	$sql="select scheme_beneficiary_id FROM dbt_scheme_beneficiary_data where scheme_id = $schemeid ";
	if($state_code != "")
	{
		$sql.=" and state_code = $state_code ";
	}
	$sql.=" and financial_year = '$fy_year'";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result)==0)	$scheme_beneficiary_id=0;
	else
	{
		$get_data_arr = mysqli_fetch_array($result);
		$scheme_beneficiary_id=$get_data_arr['scheme_beneficiary_id'];
	}
	return $scheme_beneficiary_id;
}
function gettempbeneficiary_transaction_data($schemeid,$from_month,$to_month,$fy_year)
{
	global $con;
	$sql="SELECT no_beneficiaries_normative, no_beneficiaries_additional_state , no_beneficiaries_digitised, no_beneficiaries_aadhaar, no_beneficiaries_mobile, no_group, bendata.data_mode_id, bendata.scheme_transaction_from_date, bendata.scheme_transaction_to_date, beneficiary_data_status, bendata.ip_address, bendata.reporting_month, bendata.scheme_id, bendata.state_code,bendata.district_code, bendata.financial_year,total_fund_transferred_normative, fund_transferred_state_normative , fund_transferred_center_normative , total_fund_transfered_State_additional_x , total_fund_transfered_state_additional_y , total_fund_electronic_authenticated , total_fund_non_electronic_authenticated , no_transaction_electronic_authenticated , no_transaction_non_electronic_authenticated , unit_of_measurement, no_quantity , additional_parameter1 ,additional_parameter2 , additional_parameter3 , b_typewise_data_status, scheme_benefit_type_id FROM dbt_temp_beneficiary_data bendata inner join dbt_temp_beneficiary_transaction_data trandata on bendata.scheme_id = trandata.scheme_id and bendata.reporting_month = trandata.reporting_month and bendata.financial_year = trandata.financial_year and bendata.state_code = trandata.state_code where bendata.scheme_id = $schemeid and bendata.reporting_month between $from_month and $to_month and bendata.financial_year = '$fy_year' order by bendata.reporting_month, bendata.state_code";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result)>0)
	{
		while($res=mysqli_fetch_array($result))	$data_arr[] = $res;
	}
	else	$data_arr=0;
	return $data_arr;
}
function delete_beneficiary_data($schemeid,$reporting_month,$fy_year,$state_code,$data_type)
{
	global $con;
	$temp=0;
	if($data_type=="latest")
	{
		$bentablename="dbt_scheme_beneficiary_data";
	}
	else
	{
		$bentablename="dbt_scheme_beneficiary_data_log1";
	}
	$sql="delete FROM $bentablename where scheme_id = $schemeid and reporting_month =$reporting_month and financial_year = '$fy_year'";
	if($state_code != "")
	{
		$sql.=" and state_code = $state_code ";
	}
	if(mysqli_query($con, $sql))
	{
		$sql1="delete FROM dbt_scheme_beneficiary_b_typewise_data where scheme_id = $schemeid and reporting_month =$reporting_month and financial_year = '$fy_year'";
		if($state_code != "")
		{
			$sql1.=" and state_code = $state_code ";
		}
		if(mysqli_query($con, $sql1))	$temp=1;
		else							$temp=0;
	}
	//echo($sql);
	return $temp;
}
function insert_beneficiary_data($schemeid,$reporting_month,$fy_year,$state_code,$data_type)
{
	global $con;
	$lastinsertedid1=0;
	$created = date('Y-m-d H:i:s');
	if($data_type=="latest")
	{
		$bentablename="dbt_scheme_beneficiary_data";
		$beneficiary_data_status=" beneficiary_data_status ";
	}
	else
	{
		$bentablename="dbt_scheme_beneficiary_data_log1";
		$beneficiary_data_status=" beneficiary_data_log1_status ";
	}
	$sql= "insert into $bentablename (no_beneficiaries_normative, no_beneficiaries_additional_state, no_beneficiaries_digitised, no_beneficiaries_aadhaar, no_beneficiaries_mobile, no_group, data_mode_id,scheme_transaction_from_date ,scheme_transaction_to_date, $beneficiary_data_status, ip_address, reporting_month, created, scheme_id, state_code,district_code,financial_year) SELECT no_beneficiaries_normative, no_beneficiaries_additional_state, no_beneficiaries_digitised, no_beneficiaries_aadhaar, no_beneficiaries_mobile, no_group, data_mode_id,scheme_transaction_from_date ,scheme_transaction_to_date, beneficiary_data_status, ip_address, reporting_month, '$created', scheme_id, state_code,district_code,financial_year FROM dbt_temp_beneficiary_data where scheme_id = $schemeid and reporting_month =$reporting_month and financial_year = '$fy_year'";
	if($state_code != "")
	{
		$sql.=" and state_code = $state_code ";
	}
	
	mysqli_query($con, $sql);
	$lastinsertedid = mysqli_insert_id($con);
	if($lastinsertedid>0)
	{
		$sql1= "insert into dbt_scheme_beneficiary_b_typewise_data (total_fund_transferred_normative, fund_transferred_state_normative , fund_transferred_center_normative , total_fund_transfered_State_additional_x , total_fund_transfered_state_additional_y , total_fund_electronic_authenticated , total_fund_non_electronic_authenticated , no_transaction_electronic_authenticated , no_transaction_non_electronic_authenticated , unit_of_measurement , no_quantity , additional_parameter1 , additional_parameter2 , additional_parameter3 , scheme_transaction_from_date , scheme_transaction_to_date , b_typewise_data_status , ip_address, created, scheme_id, state_code, district_code, reporting_month, financial_year) SELECT total_fund_transferred_normative, fund_transferred_state_normative , fund_transferred_center_normative , total_fund_transfered_State_additional_x , total_fund_transfered_state_additional_y , total_fund_electronic_authenticated , total_fund_non_electronic_authenticated , no_transaction_electronic_authenticated , no_transaction_non_electronic_authenticated , unit_of_measurement , no_quantity , additional_parameter1 , additional_parameter2 , additional_parameter3 , scheme_transaction_from_date , scheme_transaction_to_date , b_typewise_data_status , ip_address, '$created', scheme_id, state_code, district_code, reporting_month, financial_year FROM dbt_temp_beneficiary_transaction_data where scheme_id = $schemeid and reporting_month =$reporting_month and financial_year = '$fy_year'";
		if($state_code != "")
		{
			$sql1.=" and state_code = $state_code ";
		}
		mysqli_query($con, $sql1);
		$lastinsertedid1 = mysqli_insert_id($con);
	}
	//echo($sql);
	return $lastinsertedid1;
}
function update_replace_validation_failed_status($data_replacement_request_id,$data_replaced,$reason_for_rejection)
{
	global $con;
	if($data_replacement_request_id>0){
		$sql = "UPDATE dbt_data_replacement_request SET data_replaced = '$data_replaced', reason_for_rejection='$reason_for_rejection' WHERE data_replacement_request_id = $data_replacement_request_id";
	    $temp=mysqli_query($con, $sql);
		$data=mysqli_affected_rows($con);
		return $data;
	}
}
