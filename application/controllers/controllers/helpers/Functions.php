<?php

/**
 * Action Helper for finding days in a month
 */
class Zend_Controller_Action_Helper_Functions extends Zend_Controller_Action_Helper_Abstract {

    public function getNoOfMinistryFromSchemeMaster($state_code = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('distinct(scheme_master.ministry_id)'));
        $select->joinleft(array('ministry_master' => 'dbt_ministry_master'), 'scheme_master.ministry_id = ministry_master.ministry_id', array('ministry_master.ministry_status as ministry_status'));
        $select->where('scheme_master.scheme_status=1');
        if (empty($state_code)) {
            $select->where('scheme_master.state_code is null');
            $select->where('scheme_master.scheme_onboarding_status=?', 'Y');
        } else {
            $select->where('scheme_master.state_code=?', $state_code);
        }
        $select->where('ministry_master.ministry_status=1');
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }
    
    
    public function getNoOfMinistryForStateFromSchemeMaster($state_code) {
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('distinct(scheme_master.ministry_id)'));
        $select->joinleft(array('state_dept_master' => 'dbt_state_dept_master'), 'scheme_master.state_dept_id = state_dept_master.state_dept_id', array('state_dept_master.state_dept_name as state_ministry_name'));
        $select->where('scheme_master.scheme_status=1');
        $select->where('scheme_master.state_code=?', $state_code);
        $select->where('state_dept_master.state_dept_status=1');
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }
    
    

    public function getNoOfSchemesFromSchemeMaster($state_code = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('distinct(scheme_master.scheme_id)'));
        $select->where('scheme_master.scheme_status=1');
        if (empty($state_code)) {
            $select->where('scheme_master.state_code is null');
            $select->where('scheme_master.scheme_onboarding_status="Y"');
        } else {
            $select->where('scheme_master.state_code=?', $state_code);
        }
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    //fetch banner images
    public function getGalleryData($gallery_type = null, $language_id = null, $state_code = null) {

        $select_table = new Zend_Db_Table('dbt_photogallery');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('photogallery' => 'dbt_photogallery'), array('id', 'title', 'type', 'filename', 'filepath', 'status'));
        $select->where('photogallery.type = ?', $gallery_type);
        $select->where('photogallery.status = ?', "1");
        $select->where('photogallery.language = ?', $language_id);
        if ($state_code) {
            $select->where('photogallery.state_code = ?', $state_code);
        } else {
            $select->where('photogallery.state_code is null');
        }
        //echo $select;die;
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    public function getSchemeDetail($language_id = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('scheme_master.scheme_id', 'scheme_master.ministry_id'));
        $select->join(array('scheme_details' => 'dbt_scheme_details'), 'scheme_master.scheme_id = scheme_details.scheme_id', array('scheme_details.scheme_name as scheme_name'));
        $select->join(array('ministry_details' => 'dbt_ministry_details'), 'scheme_master.ministry_id = ministry_details.ministry_id', array('ministry_details.ministry_name'));
        $select->where('scheme_master.scheme_status=1');
        $select->where('scheme_master.scheme_onboarding_status="Y"');
        $select->where('scheme_master.state_code is null');
        if ($language_id) {
            $select->where('scheme_details.language_id= ?', $language_id);
        }
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    public function getTransactionsData($language_id = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('scheme_master.scheme_id', 'scheme_master.ministry_id'));
        $select->join(array('scheme_details' => 'dbt_scheme_details'), 'scheme_master.scheme_id = scheme_details.scheme_id', array('scheme_details.scheme_name as scheme_name'));
        $select->join(array('ministry_details' => 'dbt_ministry_details'), 'scheme_master.ministry_id = ministry_details.ministry_id', array('ministry_details.ministry_name'));

        $select->join(array('beneficiary_data' => 'dbt_scheme_beneficiary_data'), 'scheme_master.scheme_id = beneficiary_data.scheme_id', array('beneficiary_data.reporting_month', 'beneficiary_data.financial_year', 'no_beneficiaries_normative', 'no_beneficiaries_additional_state', 'state_code', 'district_code'));
        //$select->join(array('beneficiary_typewise_data' => 'dbt_scheme_beneficiary_b_typewise_data'), 'scheme_master.ministry_id = ministry_details.ministry_id', array('ministry_details.ministry_name'));

        $select->where('scheme_master.scheme_status=1');
        $select->where('scheme_master.scheme_onboarding_status="Y"');
        $select->where('scheme_master.state_code is null');
        if ($language_id) {
            $select->where('scheme_details.language_id= ?', $language_id);
        }
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    public function getTransactionalDetails($financial_year = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_beneficiary_b_typewise_data');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('beneficiary_b_typewise_data' => 'dbt_scheme_beneficiary_b_typewise_data'), array('scheme_id', 'state_code', 'district_code', 'reporting_month', 'financial_year', 'scheme_benefit_type_id', 'sum(beneficiary_b_typewise_data.total_fund_transferred_normative) as total_fund_transferred_normative', 'sum(beneficiary_b_typewise_data.fund_transferred_center_normative) as fund_transferred_center_normative', 'sum(beneficiary_b_typewise_data.total_fund_electronic_authenticated) as total_fund_electronic_authenticated', 'sum(beneficiary_b_typewise_data.total_fund_non_electronic_authenticated) as total_fund_non_electronic_authenticated', 'sum(beneficiary_b_typewise_data.no_transaction_electronic_authenticated) as no_transaction_electronic_authenticated', 'sum(beneficiary_b_typewise_data.no_transaction_non_electronic_authenticated) as no_transaction_non_electronic_authenticated'));
        $select->join(array('scheme_master' => 'dbt_scheme_master'), 'scheme_master.scheme_id = beneficiary_b_typewise_data.scheme_id', array('scheme_mis_status_id', 'scheme_onboarding_status'));
        $select->where('scheme_master.scheme_onboarding_status="Y"');
        $select->where('scheme_master.state_code is null');
        if ($financial_year) {
            $select->where('beneficiary_b_typewise_data.financial_year= ?', $financial_year);
        }
        $select->group('beneficiary_b_typewise_data.state_code');
        //echo $select;
        $results = $select_table->fetchAll($select);
        $state_wise_data = $this->getTransactionalDetailsStateWise($results->toArray());
        return $state_wise_data;
    }

    public function getTransactionalDetailsStateWise($data = array()) {
        $array = array();
        foreach ($data as $key => $val) {
            $state_code = ($val['state_code']) ? $val['state_code'] : '0';
            //$array[$state_code]['total_transfer'] = $val['total_fund_transferred_normative'];
            $array[$state_code]['total_transfer'] = $val['total_fund_electronic_authenticated'];
            //$array[$state_code]['total_transactions'] = $val['no_transaction_electronic_authenticated'] + $val['no_transaction_non_electronic_authenticated'];
            $array[$state_code]['total_transactions'] = $val['no_transaction_electronic_authenticated'];
        }
        return $array;
    }

    public function getTransactionalDetailsBySchemeGroup($financial_year = null, $fund_absolute_val = null) {

        $total_dbt_transfer_in_current_fy = $total_no_of_transactions_in_current_fy = '';

        for ($i = 1; $i <= 2; $i++) {

            $select_table = new Zend_Db_Table('dbt_scheme_master');
            $select = $select_table->select();
            $select->setIntegrityCheck(false);
            $select->from(array('scheme_master' => 'dbt_scheme_master'), array('scheme_id', 'scheme_onboarding_status'));
            $select->joinLeft(array('scheme_group' => 'dbt_scheme_group'), 'scheme_group.scheme_group_id = scheme_master.scheme_group_id', array('scheme_group_id', 'scheme_group_name'));
            //$select->joinLeft(array('benefit_type_relation' => 'dbt_scheme_benefit_type_relation'), 'benefit_type_relation.scheme_id = scheme_master.scheme_id', array('scheme_benefit_type_id'));
            $select->joinLeft(array('beneficiary_b_typewise_data' => 'dbt_scheme_beneficiary_b_typewise_data'), 'scheme_master.scheme_id = beneficiary_b_typewise_data.scheme_id', array('scheme_benefit_type_id', 'sum(beneficiary_b_typewise_data.total_fund_transferred_normative) as total_fund_transferred_normative', 'sum(beneficiary_b_typewise_data.fund_transferred_center_normative) as fund_transferred_center_normative', 'sum(beneficiary_b_typewise_data.total_fund_electronic_authenticated) as total_fund_electronic_authenticated', 'sum(beneficiary_b_typewise_data.total_fund_non_electronic_authenticated) as total_fund_non_electronic_authenticated', 'sum(beneficiary_b_typewise_data.no_transaction_electronic_authenticated) as no_transaction_electronic_authenticated', 'sum(beneficiary_b_typewise_data.no_transaction_non_electronic_authenticated) as no_transaction_non_electronic_authenticated'));
            $select->where('scheme_master.scheme_onboarding_status="Y"');
            $select->where('scheme_master.state_code is null');
			$select->where('financial_year = "2019_2020"');
            $select->where('beneficiary_b_typewise_data.scheme_benefit_type_id = ?', $i);
            //$select->group('beneficiary_b_typewise_data.scheme_benefit_type_id');
            $select->group('scheme_master.scheme_group_id'); //echo $select;die;
            //$select->group('scheme_master.scheme_id');

            $scheme_results = $select_table->fetchAll($select);

            if ($scheme_results) {
                $scheme_results = $scheme_results->toArray();
                foreach ($scheme_results as $skey => $sval) {

                    if ($fund_absolute_val == 'n') {
                        $transactional_data_arr[$financial_year][$sval['scheme_group_id']][$sval['scheme_benefit_type_id']] = $this->round_number(($sval['total_fund_electronic_authenticated']) / 10000000, 2);
                        //Get data for current financial year
                        $total_dbt_transfer_in_current_fy +=$sval['total_fund_electronic_authenticated'];
                        $total_no_of_transactions_in_current_fy +=$sval['no_transaction_electronic_authenticated'];
                    }
                    if ($fund_absolute_val == 'y') {
                        $transactional_data_arr[$sval['scheme_group_id']]['fund_data'][$sval['scheme_benefit_type_id']] = $sval['total_fund_electronic_authenticated'];
                        $transactional_data_arr[$sval['scheme_group_id']]['transactions'][$sval['scheme_benefit_type_id']] = $sval['no_transaction_electronic_authenticated'];
                    }
                }
            }
        }
        return array('transactional_data_arr' => $transactional_data_arr, 'total_dbt_transfer_in_current_fy' => $total_dbt_transfer_in_current_fy, 'total_no_of_transactions_in_current_fy' => $total_no_of_transactions_in_current_fy);
    }

    public function getBeneficiaries($financial_year = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_beneficiary_data');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('beneficiary_data' => 'dbt_scheme_beneficiary_data'), array("sum(beneficiary_data.no_beneficiaries_normative) as no_beneficiaries_normative", 'sum(beneficiary_data.no_beneficiaries_aadhaar) as no_beneficiaries_aadhaar'));
        $select->join(array('scheme_master' => 'dbt_scheme_master'), 'scheme_master.scheme_id = beneficiary_data.scheme_id', array('scheme_onboarding_status', 'scheme_id'));
        $select->joinLeft(array('schd' => 'dbt_scheme_details'), 'scheme_master.scheme_id = schd.scheme_id', array('scheme_name'));
        $select->join(array('scheme_group' => 'dbt_scheme_group'), 'scheme_group.scheme_group_id = scheme_master.scheme_group_id', array('scheme_group_id', 'scheme_group_name'));
        //$select->join(array('benefit_type_relation' => 'dbt_scheme_benefit_type_relation'), 'benefit_type_relation.scheme_id = scheme_master.scheme_id', array('scheme_benefit_type_id'));
        $select->where('scheme_master.scheme_onboarding_status="Y"');
        $select->where('scheme_master.state_code is null');
        if ($financial_year) {
            $select->where('beneficiary_data.financial_year= ?', $financial_year);
        }
        //$select->group('benefit_type_relation.scheme_benefit_type_id');
        //$select->group('scheme_master.scheme_group_id');
        $select->group('scheme_master.scheme_id');

        $results = $select_table->fetchAll($select);

        if ($results) {
            $ben_array = $results->toArray();
            return $ben_array;
        }
    }

    // Get all scheme's group name
    public function getSchemeGroup() {
        $data = array();
        $select_table = new Zend_Db_Table('dbt_scheme_group');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_group' => 'dbt_scheme_group'), array('scheme_group_id', 'scheme_group_name'));
        $select->where('scheme_group.scheme_group_status = ?', "1");

        $results = $select_table->fetchAll($select);
        foreach ($results->toArray() as $key => $val) {
            $data[$val['scheme_group_id']] = $val['scheme_group_name'];
        }
        return $data;
    }

    public function countSchemesByBenefitType($scheme_status = null) {
        $data = array();
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('scheme_master.scheme_id', 'scheme_master.scheme_code'));
        $select->join(array('benefit_type_relation' => 'dbt_scheme_benefit_type_relation'), 'scheme_master.scheme_id=benefit_type_relation.scheme_id', array("IF(group_concat(benefit_type_relation.scheme_benefit_type_id)='1', 1, 0) AS cash_scheme", "IF(group_concat(benefit_type_relation.scheme_benefit_type_id)='2', 1, 0) AS inkind_scheme", "if(group_concat(benefit_type_relation.scheme_benefit_type_id ORDER BY benefit_type_relation.scheme_benefit_type_id) = '1,2', 1, 0) AS cash_and_inkind_scheme"));
        $select->where('scheme_master.scheme_onboarding_status = ?', "Y");
        $select->where('scheme_master.state_code is null');
        
		if(is_array($scheme_status)){
			$select->where('scheme_master.scheme_status IN(?)', $scheme_status);
		}else{
			$select->where('scheme_master.scheme_status = ?', "1");
		}
        $select->group('benefit_type_relation.scheme_id');

        $results = $select_table->fetchAll($select);

        foreach ($results->toArray() as $key => $val) {
            $data['cash_scheme'] += ($val['cash_scheme']) ? $val['cash_scheme'] : 0;
            $data['inkind_scheme'] += ($val['inkind_scheme']) ? $val['inkind_scheme'] : 0;
            $data['cash_and_inkind_scheme'] += ($val['cash_and_inkind_scheme']) ? $val['cash_and_inkind_scheme'] : 0;

            //Benefit type of each scheme
            $benefit_type_by_scheme[$val['scheme_id']] = ($val['cash_scheme']) ? 1 : (($val['inkind_scheme']) ? 2 : (($val['cash_and_inkind_scheme']) ? 3 : 0));
        }
        return array('scheme_count_benefit_type' => $data, 'benefit_type_by_scheme' => $benefit_type_by_scheme);
    }

    public function getHomeGraph() {
        $select_table = new Zend_Db_Table('dbt_graph');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('graph' => 'dbt_graph'), array('benefit_type_id', 'scheme_group_id', 'financial_year', 'total_fund_transfer', 'no_of_beneficiaries', 'no_of_scheme'));
        $select->where('graph.graph_status=1');
        //$select->where('ministry_master.ministry_status=1');
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    public function round_number($number, $precision = 2) {
        if (0 == $number) {
            return $number;
        }
        $negative = $number / abs($number);
        $number = abs($number);
        $precision = pow(10, $precision);
        return floor($number * $precision) / $precision * $negative;
    }

    
    /******************************financialYearFormat: Starts *************************************/
    /*
     *  parameter $format_type must be either 'short','medium','long'
            * short => xx-xx
            * medium => xxxx-xx
            * long => xxxx-xxxx

     * parameter $financial_year must be in xxxx_xxxx format
     */

    public function financialYearFormat($financial_year = null, $format_type = null) {

        $financial_year_array = explode('_', $financial_year);
        //print_r($financial_year_array);die;
        switch (strtolower($format_type)) {
            case "short":
                $year_from = substr($financial_year_array[0], -2);
                $year_to = substr($financial_year_array[1], -2);
                $financial_year_new_format = $year_from . "-" . $year_to;
                break;
            case "medium":
                $year_from = $financial_year_array[0];
                $year_to = substr($financial_year_array[1], -2);
                $financial_year_new_format = $year_from . "-" . $year_to;
                break;
            case "long":
                $year_from = $financial_year_array[0];
                $year_to = $financial_year_array[1];
                $financial_year_new_format = $year_from . "-" . $year_to;
                break;
            default:
                $financial_year_new_format = $financial_year;
        }
        return $financial_year_new_format;
    }
    /******************************financialYearFormat: Ends *************************************/

    //Get Transactional data for State/UT
    public function getTransactionalDetailsForState($state_code=null, $financial_year = null) {
        $select_table = new Zend_Db_Table('dbt_state_scheme_beneficiary_b_typewise_data');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('beneficiary_b_typewise_data' => 'dbt_state_scheme_beneficiary_b_typewise_data'), array('scheme_id', 'state_code', 'district_code', 'reporting_month', 'financial_year', 'scheme_benefit_type_id', 'sum(beneficiary_b_typewise_data.total_fund_transferred_normative) as total_fund_transferred_normative', 'sum(beneficiary_b_typewise_data.fund_transferred_center_normative) as fund_transferred_center_normative', 'sum(beneficiary_b_typewise_data.total_fund_electronic_authenticated) as total_fund_electronic_authenticated', 'sum(beneficiary_b_typewise_data.total_fund_non_electronic_authenticated) as total_fund_non_electronic_authenticated', 'sum(beneficiary_b_typewise_data.no_transaction_electronic_authenticated) as no_transaction_electronic_authenticated', 'sum(beneficiary_b_typewise_data.no_transaction_non_electronic_authenticated) as no_transaction_non_electronic_authenticated'));
        $select->join(array('scheme_master' => 'dbt_scheme_master'), 'scheme_master.scheme_id = beneficiary_b_typewise_data.scheme_id', array('scheme_mis_status_id', 'scheme_onboarding_status'));
        //$select->where('scheme_master.scheme_onboarding_status="Y"');
        $select->where('scheme_master.state_code=?', $state_code);
        $select->where('beneficiary_b_typewise_data.district_code is not null');
        $select->where('scheme_master.state_code is null');
        if ($financial_year) {
            $select->where('beneficiary_b_typewise_data.financial_year= ?', $financial_year);
        }
        $select->group('beneficiary_b_typewise_data.district_code');
        //echo $select;
        $results = $select_table->fetchAll($select);
        $state_wise_data = $this->getTransactionalDetailsStateWise($results->toArray());
//        echo "<pre>";
//        print_r($state_wise_data);
//        echo "</pre>";die;
        return $state_wise_data;
    }
   
    public function validateResponseMethod() {

		$request = $this->getRequest();
		$host_url = (parse_url($request->getHeader('referer'),PHP_URL_HOST));
        
		$allow_methods = array('GET', 'POST');
        
        if (in_array($request->getMethod(Request), $allow_methods)) {
			if($host_url == 'localhost' || $host_url == 'dbtbharat.gov.in' || $host_url == ''){
				return TRUE;
			}else{
				return FALSE;
			}
            
        }
        return FALSE;
        
    }
   
   public function validateScheme($condition = array()) {
	   
		$user_model = new Application_Model_User;
		$schemereport_model = new Application_Model_Schemereportnew;
		if ($condition['role_id'] == 1 || $condition['role_id'] == 3) {
			return 'y';
		} else {
			if ($condition['role_id'] == 6) {
				
				$scheme_id = $scheme_code = NULL; 
				$user_details = $user_model->getUserDetails($condition['user_id']);
				$user_ministry_id = $ministry_id_param = $user_details['ministry_id'];

				if($condition['scheme_id']) $scheme_id = $condition['scheme_id'];
				if($condition['scheme_code']) $scheme_code = $condition['scheme_code'];

				//Search scheme ID and ministry ID for central schemes
				$scheme_list = $schemereport_model->getSchemeList($scheme_id, $user_ministry_id, null, null, null, null, null, null, $scheme_code);
				if(empty($scheme_list)){
					return 'n';
				}
			} elseif ($condition['role_id'] == 4) {

				$user_details = $schemereport_model->getAssignedSchemesForUser($condition['user_id']);
				$assigned_scheme_list = $user_details['assigned_schemes_array'];

				if($condition['scheme_code']){
					$user_scheme_id = $schemereport_model->getSchemeIdbyCode($condition['scheme_code']);

					if(!in_array($user_scheme_id['scheme_id'], $assigned_scheme_list)){
						return 'n';
					}
				}else{
					//Check if Scheme ID exists in assign scheme array
					if(!in_array($condition['scheme_id'], $assigned_scheme_list)){
						return 'n';
					}
				}
			}elseif ($condition['role_id'] == 2) {
			
				if($condition['state_code']){//State Page
					$user_details = $user_model->getUserDetails($condition['user_id']);
					$user_state_code = $user_details['state_code'];
					if($condition['state_code'] != $user_state_code){
						return 'n';
					}
				}
			}
		}
		
    }   
}
