<?php

require_once 'Zend/Db/Table/Abstract.php';

class Application_Model_Authnew extends Zend_Db_Table_Abstract {

    public function authenticateUser($user_name, $user_password, $user_token = null) {

        $select_table = new Zend_Db_Table("dbt_user_master");
        $select = $select_table->select();
        $select->from(array("u" => "dbt_user_master"), array("count(user_name) as user_record"));
        $select->where("user_name = ?", trim($user_name));
        //$select->where("user_password = ?", trim($user_password));
        if ($user_token) {
            $select->where("reset_login_token = ?", trim($user_token));
        }//echo $select;die;
        $select->where("user_status = ?", 1);
        $rowcount = $select_table->fetchRow($select);

        if ($rowcount) {
            $rowcount = $rowcount->toArray();
            $rowcount = $rowcount['user_record'];
        }
        return $rowcount;
    }

    public function updateUserLoginAttempt($user_name = null) {

        $columns = [];

        $select_table = new Zend_Db_Table("dbt_user_master");
        $select = $select_table->select();
        $select->from(array("u" => "dbt_user_master"), array("login_attempt"));
        $select->where("user_name = ?", trim($user_name)); //echo $select;die;
        $rowcount = $select_table->fetchRow($select);

        if ($rowcount) {
            $rowcount = $rowcount->toArray();
            $login_attempt = $rowcount['login_attempt'];
        }
         $login_attempt++;
        if ($login_attempt >= LOCK_COUNT) {
            $columns = array('user_status' => 2,'login_attempt' => $login_attempt);
        } else {
           $columns = array('user_status' => 1,'login_attempt' => $login_attempt);
        }
        $where = array('user_name = ?' => trim($user_name));
        $update_values = $select_table->update($columns, $where);
        return $update_values;
    }

    public function updateUserStatus($user_id, $columns) {

        if (!array_key_exists('user_status', $columns))
            unset($columns['user_status']);

        if (!array_key_exists('sys_gen_pwd_status', $columns))
            unset($columns['sys_gen_pwd_status']);

        if (!array_key_exists('login_attempt', $columns))
            unset($columns['login_attempt']);

        if (!array_key_exists('reset_login_token', $columns))
            unset($columns['reset_login_token']);

        if (count($columns) > 0) {
            $user_table = new Zend_Db_Table('dbt_user_master');
            $where = array('user_id = ?' => $user_id);
            $update_values = $user_table->update($columns, $where);
        }
    }

    public function changeUserPassword($new_password, $user_id) {

        //Update new user password 
        $select_table = new Zend_Db_Table('dbt_user_master');
        //$columns = array('user_password' => hash_hmac('sha256', $new_password, ''));
        $columns = array('user_password' => $new_password);
        $condition = array("user_id = ?" => $user_id);
        $update_values = $select_table->update($columns, $condition);
        return 1;
    }

    public function updatesessionstatus($sesid) {
        $status = 0;
        $lotime = time();
        $user_table = new Zend_Db_Table('dbt_sessions');
        $data = array(
            'status' => $status,
            'logout_time' => $lotime
        );
        $where = array('session_id = ?' => $sesid);
        $update_values = $user_table->update($data, $where);
    }

    
    //Taken from helper/functions.php
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
    
    public function countSchemesByBenefitType() {
        $data = array();
        $select_table = new Zend_Db_Table('dbt_scheme_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('scheme_master' => 'dbt_scheme_master'), array('scheme_master.scheme_id', 'scheme_master.scheme_code'));
        $select->join(array('benefit_type_relation' => 'dbt_scheme_benefit_type_relation'), 'scheme_master.scheme_id=benefit_type_relation.scheme_id', array("IF(group_concat(benefit_type_relation.scheme_benefit_type_id)='1', 1, 0) AS cash_scheme", "IF(group_concat(benefit_type_relation.scheme_benefit_type_id)='2', 1, 0) AS inkind_scheme", "if(group_concat(benefit_type_relation.scheme_benefit_type_id ORDER BY benefit_type_relation.scheme_benefit_type_id) = '1,2', 1, 0) AS cash_and_inkind_scheme"));
        $select->where('scheme_master.scheme_onboarding_status = ?', "Y");
        $select->where('scheme_master.state_code is null');
        $select->where('scheme_master.scheme_status = ?', "1");
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
    
    public function getBeneficiaries($financial_year = null) {
        $select_table = new Zend_Db_Table('dbt_scheme_beneficiary_data');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('beneficiary_data' => 'dbt_scheme_beneficiary_data'), array("sum(beneficiary_data.no_beneficiaries_normative) as no_beneficiaries_normative", 
		"sum(beneficiary_data.no_beneficiaries_additional_state) as no_beneficiaries_additional_state", 
		'sum(beneficiary_data.no_beneficiaries_aadhaar) as no_beneficiaries_aadhaar'));
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
    
    public function round_number($number, $precision = 2) {
        if (0 == $number) {
            return $number;
        }
        $negative = $number / abs($number);
        $number = abs($number);
        $precision = pow(10, $precision);
        return floor($number * $precision) / $precision * $negative;
    }
}
