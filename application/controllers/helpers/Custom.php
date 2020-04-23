<?php

/**
 * Action Helper for finding days in a month
 */
class Zend_Controller_Action_Helper_Custom extends Zend_Controller_Action_Helper_Abstract {
    /*
     * This function get current financial year
     */

    public function getCurrentFinancialYear() {
        $curre_year = strtotime(date("d-m-Y"));
        $fixedyear = strtotime(date("d-m-Y", strtotime(FY_START_DATE)));
        if ($curre_year > $fixedyear) {
            $start = date("Y");
        } else if ($curre_year <= $fixedyear) {
            $dataa = date("Y") - 1;
            $start = $dataa;
        }
        $end_year = $start + 1;
        $dateadded = $start . "_" . $end_year;
        return $dateadded;
    }

    /*
     * This function get current financial year IN FORMAT 2017-18
     */

    public function getCurrentFinancialYearNw() {

        /* $financialyearfrom = date('Y');
          $financialyearto = date('y') + 1;
          $currentmonth = date('m');
          if ($currentmonth <= 3) {
          $financialyearfrom = date('Y') - 1;
          $financialyearto = date('y');
          }
          $financialyear = $financialyearfrom . '-' . $financialyearto;

          return $financialyear; */

        $curre_year = strtotime(date("d-m-Y"));
        $fixedyear = strtotime(date("d-m-Y", strtotime(FY_START_DATE)));
        if ($curre_year > $fixedyear) {
            $financialyearfrom = date('Y');
            $financialyearto = date('y') + 1;
        } else if ($curre_year <= $fixedyear) {
            $financialyearfrom = date('Y') - 1;
            $financialyearto = date('y');
        }
        $financialyear = $financialyearfrom . '-' . $financialyearto;
        return $financialyear;
    }

    /**
     * First entry is for January
     */
    protected $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;

    /**
     * Constructor: initialize plugin loader
     * 
     * @return void
     */
    public function __construct() {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }

    /**
     * Returns the number of days in a given month + year
     * 
     * @param int $month
     * @param int $year
     * @return int
     * @throws Exception
     */
    public function getDaysInMonth($month, $year) {
        if ($month < 1 || $month > 12) {
            throw new Exception('Invalid month ' . $month);
        }

        $d = $this->daysInMonth[$month - 1];

        if ($month == 2) {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...

            if (($year % 4) == 0) {
                if (($year % 100) == 0) {
                    if (($year % 400) == 0) {
                        $d = 29;
                    }
                } else {
                    $d = 29;
                }
            }
        }

        return $d;
    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @param  int $month 
     * @param  int $year 
     * @return int
     */
    public function direct($month, $year) {
        return $this->getDaysInMonth($month, $year);
    }

    public function getstatename($statecode = null) {
        if ($statecode == '35') {
            $statename = 'andaman_nicobar';
        } else if ($statecode == '7') {
            $statename = 'delhi';
        } else if ($statecode == '26') {
            $statename = 'dadar_nagar_haveli';
        } else if ($statecode == '25') {
            $statename = 'daman_diu';
        } else if ($statecode == '4') {
            $statename = 'chandigarh';
        } else if ($statecode == '31') {
            $statename = 'lakshadweep';
        } else if ($statecode == '34') {
            $statename = 'puducherry';
        } else if ($statecode == '28') {
            $statename = 'andhra_pradesh';
        } else if ($statecode == '12') {
            $statename = 'arunachal_pradesh';
        } else if ($statecode == '18') {
            $statename = 'assam';
        } else if ($statecode == '10') {
            $statename = 'bihar';
        } else if ($statecode == '22') {
            $statename = 'chhattisgarh';
        } else if ($statecode == '30') {
            $statename = 'goa';
        } else if ($statecode == '24') {
            $statename = 'gujarat';
        } else if ($statecode == '6') {
            $statename = 'haryana';
        } else if ($statecode == '2') {
            $statename = 'himachel_pradesh';
        } else if ($statecode == '1') {
            $statename = 'jammu_kashmir';
        } else if ($statecode == '20') {
            $statename = 'jharkhand';
        } else if ($statecode == '29') {
            $statename = 'karnatka';
        } else if ($statecode == '32') {
            $statename = 'kerala';
        } else if ($statecode == '23') {
            $statename = 'madhya_pradesh';
        } else if ($statecode == '27') {
            $statename = 'maharashtra';
        } else if ($statecode == '14') {
            $statename = 'manipur';
        } else if ($statecode == '17') {
            $statename = 'meghalaya';
        } else if ($statecode == '15') {
            $statename = 'mizoram';
        } else if ($statecode == '13') {
            $statename = 'nagaland';
        } else if ($statecode == '21') {
            $statename = 'odisha';
        } else if ($statecode == '3') {
            $statename = 'punjab';
        } else if ($statecode == '8') {
            $statename = 'rajasthan';
        } else if ($statecode == '11') {
            $statename = 'sikkim';
        } else if ($statecode == '33') {
            $statename = 'tamil_nadu';
        } else if ($statecode == '36') {
            $statename = 'telangana';
        } else if ($statecode == '16') {
            $statename = 'tripura';
        } else if ($statecode == '5') {
            $statename = 'uttarakhand';
        } else if ($statecode == '9') {
            $statename = 'uttar_pradesh';
        } else if ($statecode == '19') {
            $statename = 'west_bengal';
        }
        return $statename;
    }

    /*     * ****************** validation: File Upload ************************ */

    function fileUploadValidation($files, $fileFormat, $allow_extension, $file_size_allowed = null) {
        $filename = $files['name'];
        $fieltempval = 0;

        if ((count(explode('.', $filename)) > 2) || (preg_match("/[\/|~|`|;|:|]/", $filename))) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%0A\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%0D\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%22\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%27\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%3C\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%3E\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%00\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%3b\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%3d\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%29\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%28\b/i", $filename)) {
            $fieltempval = 1;
        } elseif (preg_match("/\b%20\b/i", $filename)) {
            $fieltempval = 1;
        }

        if (in_array(end(explode('.', $filename)), $fileFormat) && $fieltempval == 0) {

            $data = file_get_contents($files['tmp_name']);//print'<pre>';print_r($files);echo $data;die('df');
            $dataCheck = substr($data, 0, 2);
            if ($dataCheck == "MZ" || $dataCheck == "NE" || $dataCheck == "PE" || $dataCheck == "LX" || $dataCheck == "LE" || $dataCheck == "W3" || $dataCheck == "W4" || $dataCheck == "DL" || $dataCheck == "MP" || $dataCheck == "P2" || $dataCheck == "P3" || $dataCheck == "Ta" || $data == "") {
                //$this->_redirect('/ministryowner/ministryschemeadd?actmsg=fileformaterror&'.$qstring);
                $fieltempval = 1;//die($fieltempval);
            } else { //echo $files['size'].' dd '.$file_size_allowed;die;
                if ($files['size'] > $file_size_allowed)
                    $fieltempval = 2;
                else
                    $fieltempval = 0;
            }
        }else {
            $fieltempval = 1;
        }
        return $fieltempval; // 0-PASS, 1-INVALID FILE, 2- LARGE FILE
    }

    /*     * ****************** validation: File Upload ************************ */

    public function getallFinancialYear() {
        $year_ini = '';
        $financial_year_arr = array();
        $curre_year = strtotime(date("d-m-Y"));
        $fixedyear = strtotime(date("d-m-Y", strtotime(FY_START_DATE)));
        if ($curre_year > $fixedyear) {
            $start = date("Y");
        } else if ($curre_year <= $fixedyear) {
            $dataa = date("Y") - 1;
            $start = $dataa;
        }
        $end_year = $start + 1;
        $dateadded = $start . "_" . $end_year;

        for ($year_ini = 2016; $year_ini < $end_year; $year_ini++) {
            $next_yr = $year_ini + 1;
            $financial_year_arr[] = $year_ini . "_" . $next_yr;
        }

        return $financial_year_arr;
    }

    public function getallFinancialYearFrom($yearfrom) {
        // $year_ini = '';
        $financial_year_arr = array();
        $curre_year = strtotime(date("d-m-Y"));
        $fixedyear = strtotime(date("d-m-Y", strtotime(FY_START_DATE)));
        if ($curre_year > $fixedyear) {
            $start = date("Y");
        } else if ($curre_year <= $fixedyear) {
            $dataa = date("Y") - 1;
            $start = $dataa;
        }
        $end_year = $start + 1;
        $dateadded = $start . "_" . $end_year;

        for ($year_ini = $yearfrom; $year_ini < $end_year; $year_ini++) {
            $next_yr = $year_ini + 1;
            $financial_year_arr[] = $year_ini . "_" . $next_yr;
        }

        return $financial_year_arr;
    }

    /**
     * Generate the financial year based upon the day month and the year
     * @author chakshu
     * @date: 6 Sep 2017
     */
    public function generateFinancialYear($lastday, $month, $year) {
        $existing_date = strtotime($lastday . "-" . $month . "-" . $year);
        $last_day_of_fy = strtotime(date("d-m-Y", strtotime(FY_START_DAY_MONTH . $year)));

        if ($existing_date > $last_day_of_fy) {
            $start_year = $year;
        } else if ($existing_date <= $last_day_of_fy) {
            $start_year = $year - 1;
        }
        $financial_year = $start_year . "_" . ($start_year + 1);
        return $financial_year;
    }

    /**
     * Get the scheme detail based upon the scheme id 
     * @author chakshu
     * @date: 6 Sep 2017
     */
    public function getschemename($scheme_id) {

        $select_table = new Zend_Db_Table('dbt_scheme');
        $rowselect = $select_table->fetchAll($select_table->select()->where('id = ?', trim($scheme_id)));
        $rowselectarr = $rowselect->toArray();
        return $rowselectarr;
    }

    /*     * *state format code changes 23-10-2017** */

    /**
     * Returns the benefit type of the scheme
     * @return array
     * @author Dilip
     * @date 1 Sep 2017
     * @List of pages used this function:
     */
    public function onboardedBenefitType() {
        $array = array(
            '1' => 'Cash',
            '2' => 'In Kind',
            '3' => 'Others',
            '5' => 'Cash and In Kind',
            '6' => 'Service Enabler',
        );
        return $array;
    }

    /**
     * Returns the benefit type of the scheme
     * @return array
     * @author Dilip
     * @date 1 Sep 2017
     * @List of pages used this function:
     */
    public function schemeGroup() {
        $array = array(
            '1' => 'PAHAL',
            '2' => 'MGNREGS',
            '3' => 'NSAP',
            '4' => 'SCHOLARSHIP SCHEME',
            '5' => 'OTHERS',
        );
        return $array;
    }

    public function get_scheme_type($scheme_code = null) {
        /* if ($scheme_code == 'A') {
          $scheme_type = 'Central Sector Scheme';
          } else if ($scheme_code == 'B') {
          $scheme_type = 'Centrally Sponsored Scheme';
          } else if ($scheme_code == 'C') {
          $scheme_type = 'State/UTs Scheme';
          } else if ($scheme_code == 'D') {
          $scheme_type = 'District Scheme';
          } else {
          $scheme_type = 'N/A';
          } */

        $scheme_code_first_char = strtolower(substr($scheme_code, 0, 1));
        //echo $scheme_code_first_char;die;
        switch ($scheme_code_first_char) {
            case 'a':
                $scheme_type = 'Central Sector';
                break;
            case 'b':
                $scheme_type = 'Centrally Sponsored Scheme';
                break;
            case 'c':
                $scheme_type = 'State/UTs Scheme';
                break;
            case 'd':
                $scheme_type = 'District Scheme';
                break;
            case 'e':
                $scheme_type = 'Centrally Sponsored Scheme';
                break;
            case 'g':
                $scheme_type = 'State/UTs Scheme';
                break;
            case 'f':
                $scheme_type = 'Central Sector';
                break;
            default:
                $scheme_type = 'N/A';
        }
        return $scheme_type;
    }

    public function get_applicable_scheme_basic_info($scheme_id = null) {

        $manage_schemedata_model = new Application_Model_Manageschemedatafy1718;
        $onboard_model = new Application_Model_OnboardingMonitoringfy1718;

        $get_scheme_data = $manage_schemedata_model->get_applicable_scheme_data($scheme_id);

        $benefit_type_phase_I = $get_scheme_data[0]['benefit_type'];
        $get_kpi_arr = $manage_schemedata_model->get_scheme_kpi($scheme_id, $benefit_type_phase_I, $onboard_model);

        if ($get_kpi_arr['type_of_scheme'] != '') { //Check if type of scheme kpi exists in scorecard
            $get_scheme_data[0]['type_of_scheme'] = $get_kpi_arr['type_of_scheme'];
        } elseif ($get_scheme_data[0]['type_of_scheme'] == 1) {

            $get_scheme_data[0]['type_of_scheme'] = 'Central Sector';
        } elseif ($get_scheme_data[0]['type_of_scheme'] == 2) {

            $get_scheme_data[0]['type_of_scheme'] = 'Centrally Sponsered';
        } else {
            $get_scheme_data[0]['type_of_scheme'] = '';
        }
        return $get_scheme_data;
        //print"<pre>";print_r($get_scheme_data);die;
    }

    public function pagination($nume, $start, $limit, $pagename) {
        //die("jdf");
        if ($nume > $limit) {
            $page_name = $pagename . '?search=' . $_GET['search'];
            $this1 = $start + $limit;
            $back = $start - $limit;
            $next = $start + $limit;

            $paginate = "";
            $paginate.='<ul class="pagination">';

            if ($back >= 0) {
                $paginate.='<li><a href="' . $page_name . '&start=' . $back . '" class="head2">&lt; PREV</a></li>';
            }
            $i = 0;
            $l = 1;
            for ($i = 0; $i < $nume; $i = $i + $limit) {
                if ($i <> $start) {
                    $paginate.='<li><a href="' . $page_name . '&start=' . $i . '" class="text">' . $l . '</a></li>';
                } else {
                    $paginate.='<li><a href="#" class="text active">' . $l . '</a></li>';
                }
                $l = $l + 1;
            }

            if ($this1 < $nume) {
                $paginate.='<li><a href="' . $page_name . '&start=' . $next . '" class="head2">NEXT &gt;</a></li>';
            }
            $paginate.='</ul>';
            return $paginate;
            //echo $paginate;die;
        }
    }

    public function monthName() {
        $month_names = array(
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December",
            "1" => "January",
            "2" => "February",
            "3" => "March",
        );

        return $month_names;
    }

    public function check_scheme_type($scheme_code = null) {

        $scheme_code_first_char = strtolower(substr($scheme_code, 0, 1));

        switch ($scheme_code_first_char) {
            case 'a':
                $scheme_type = 1;
                break;
            case 'b':
                $scheme_type = 2;
                break;
            case 'c':
                $scheme_type = 3;
                break;
            case 'd':
                $scheme_type = 4;
                break;
            case 'e':
                $scheme_type = 5;
                break;
            case 'f':
                $scheme_type = 1;
                break;
            case 'g':
                $scheme_type = 3;
                break;
            default:
                $scheme_type = 'N/A';
        }
        return $scheme_type;
    }

    public function getstateSchemeDetail($statename, $scheme_id) {
        $selecttable = new Zend_Db_Table('dbt_' . $statename . '_scheme');
        $select = $selecttable->select();
        $select->setIntegrityCheck(false);
        $select->from(array('sm' => 'dbt_' . $statename . '_scheme'), array('*'));
        //$select->join(array('ministry' => 'dbt_ministry'),'sm.ministry_id = ministry.id', array('ministry_name'));
        $select->joinLeft(array('ministry' => 'dbt_ministry'), 'sm.ministry_id = ministry.id', array('ministry.ministry_name as ministryname'));
        //$select->join(array('beneficary' =>'dbt_beneficaryscheme_'.$statename),'sm.id = beneficary.scheme_id', array('beneficary.totalnoofbeneficiaries as totalnoofbeneficiaries'));
        $select->where('sm.language  = ?', 2);
        $select->where('sm.id = ? ', $scheme_id);
        $select->where('sm.status = ? ', 1);
        //$select->where('ministry.status = ? ',1);
        //echo $select;die;
        $tablelist = $selecttable->fetchAll($select);
        $stateschemedata = $tablelist->toArray();
        return $stateschemedata;
    }

    public function getstateBeneficiaryDetail($statename, $scheme_id, $financial_year_from, $financial_year_to, $month) {
        $selecttable = new Zend_Db_Table('dbt_beneficaryscheme_' . $statename);
        $select = $selecttable->select();
        $select->setIntegrityCheck(false);
        $select->from(array('sm' => 'dbt_beneficaryscheme_' . $statename), array('id', 'totalnoofbeneficiaries', 'total_no_of_beneficiaries_centre', 'total_no_of_beneficiaries_state', 'totalnoofbeneficiarieswithaadhaar', 'total_no_of_beneficiaries_mobile_no', 'total_no_of_beneficiaries_digitized', 'total_ghost_beneficiaries', 'total_deduplicate_beneficiaries'));
        $select->where('sm.scheme_id = ? ', $scheme_id);
        $select->where('sm.financial_year_from = ? ', $financial_year_from);
        $select->where('sm.financial_year_to = ? ', $financial_year_to);
        $select->where('sm.month = ? ', $month);
        $select->where('sm.status = ? ', 1);
        $stateschemedata = $selecttable->fetchRow($select);
        //$stateschemedata  = $tablelist->toArray();
        return $stateschemedata;
    }

    public function getstateSchemeManualDetail($statename, $scheme_id, $financial_year_from, $financial_year_to, $month) {
        $selecttable = new Zend_Db_Table('dbt_scheme_manual_data_' . $statename);
        $select = $selecttable->select();
        $select->setIntegrityCheck(false);
        $select->from(array('sm' => 'dbt_scheme_manual_data_' . $statename), array('id', 'total_fund_transfer', 'fund_transfer_centre_share', 'fund_transfer_state_normative_share', 'fund_transfer_additional_state_contribution', 'total_fund_transfer_centre_and_state_and_additional_share', 'fund_transfer_state', 'grand_total_fund_transfer', 'no_of_beneficiries_in_scheme', 'without_aadhar_bridge_payment', 'total_transaction_other_mode', 'total_fund_transfer_other_mode', 'saving', 'remarks', 'other_saving', 'quantity', 'unit_of_measurement', 'no_of_beneficiries_with_aadhar', 'total_no_of_transaction_inkind', 'total_transaction_electronic', 'centre_share_expenditure', 'normative_state_share_expenditure', 'additional_state_contribution_expenditure', 'total_centre_state_expenditure', 'state_contribution_expenditure', 'total_expenditure'));
        $select->where('sm.scheme_id = ? ', $scheme_id);
        $select->where('sm.financial_year_from = ? ', $financial_year_from);
        $select->where('sm.financial_year_to = ? ', $financial_year_to);
        $select->where('sm.month = ? ', $month);
        $select->where('sm.status = ? ', 1);
        /* echo $select;
          die; */
        $stateschemedata = $selecttable->fetchRow($select);
        //$stateschemedata  = $tablelist->toArray();
        return $stateschemedata;
    }

    public function scheme_specific_MIS_options($scheme_mis_status = null) {

        if ($scheme_mis_status) {
            switch ($scheme_mis_status) {
                case 0:
                    $scheme_mis_status_data = '--Select Status of Scheme Specific MIS--';
                    break;
                case 1:
                    $scheme_mis_status_data = 'Online system / MIS at Conceptual Stage';
                    break;
                case 2:
                    $scheme_mis_status_data = 'Online system / MIS under development';
                    break;
                case 3:
                    $scheme_mis_status_data = 'Online system / MIS implemented at field level (Roll out) and data reported manually';
                    break;
                case 4:
                    $scheme_mis_status_data = 'Online system / MIS integrated with DBT Bharat portal but data reported manually';
                    break;
                case 5:
                    $scheme_mis_status_data = 'Online system / MIS integrated with DBT Bharat portal and report submitted through web-services';
                    break;
                default:
                    $scheme_mis_status_data = 'N/A';
            }
        } else {

            $scheme_mis_status_data = array(
                0 => '--Select Status of Scheme Specific MIS--',
                1 => 'Online system / MIS at Conceptual Stage',
                2 => 'Online system / MIS under development',
                3 => 'Online system / MIS implemented at field level (Roll out) and data reported manually',
                4 => 'Online system / MIS integrated with DBT Bharat portal but data reported manually',
                5 => 'Online system / MIS integrated with DBT Bharat portal and report submitted through web-services'
            );
        }
        return $scheme_mis_status_data;
    }

    /**
     * This function contains the array of States that created their State DBT Portal.
     * @return array i.e. array[state_code]=>State_DBT_Portal_URL
     * @author Dilip Kumar
     * @date 6 November 2017
     * @List of pages used this function:
     * StateutController.php
     */
    public function stateDbtPortalUrl() {
        $state_url_array = array(
            '10' => 'http://dbt.bih.nic.in',
            '22' => 'http://dbt.cgstate.gov.in',
            '23' => 'http://164.100.196.217/mpdbt',
            '21' => 'http://dbt.Odisha.gov.in',
            '9' => 'http://dbtup.upsdc.gov.in',
            '18' => 'http://103.8.248.140',
            '17' => 'http://megdbt.gov.in',
            '15' => 'http://dbt.Mizoram.gov.in',
            '13' => 'http://dbt.nagaland.gov.in',
            '11' => 'https://ten19nims.wixsite.com/dbtsikkim',
            '20' => 'http://jhr.nic.in/dbt',
            '27' => 'https://mahadbt.gov.in',
            '5' => 'http://dbt.uk.gov.in',
            '24' => 'http://www.dbtgujarat.guj.nic.in',
            '2' => 'http://dbtportal.hp.gov.in',
            '28' => 'http://dbt.ap.gov.in',
            '29' => 'http://dbtkarnataka.gov.in',
            '3' => 'http://dbt.Punjab.gov.in',
            '12' => 'http://dbt.arunachal.gov.in',
            '6' => 'http://dbtharyana.gov.in',
            '8' => 'http://bhamashah.rajasthan.gov.in',
            '16' => 'http://dbttripura.gov.in',
            '1' => 'http://dbtjk.gov.in',
            '32' => 'http://dbt.kerala.gov.in'
        );
        return $state_url_array;
    }

    /**
     * Returns the benefit type of the scheme
     * @return array
     * @author Dilip
     * @date 22 Nov 2017
     * @List of pages used this function:
     */
    public function applicableSchemeBenefitType() {
        $array = array(
            '1' => 'Cash',
            '2' => 'In Kind',
            '3' => 'Others',
            '5' => 'Cash and In Kind',
            '6' => 'Services',
            '7' => 'Service Enabler',
        );
        return $array;
    }

    public function get_scheme_type_array() {

        $scheme_type_array = array(
            '1' => 'Central Sector',
            '2' => 'Centrally Sponsored Scheme',
            '3' => 'State/UTs Scheme',
            '4' => 'District Scheme',
            '5' => 'Centrally Sponsered Scheme'
        );
        return $scheme_type_array;
    }

    /*     * ** Export to excel *** */

    public function exportExcelProperty($object) {
        // set default font
        $object->getDefaultStyle()->getFont()->setName('Calibri');
        // set default font size
        $object->getDefaultStyle()->getFont()->setSize(8);
        // create the writer
        $object->getProperties()->setCreator("Dilip Kumar");
        $object->getProperties()->setLastModifiedBy("Dilip Kumar");
        $object->getProperties()->setTitle("DBT Bharat Progress Report");
        $object->getProperties()->setSubject("DBT Bharat Progress Report");
        $object->getProperties()->setDescription("DBT Bharat Progress Report");

        return $object;
    }

    public function exportExcelTitle($data, $object) {

        $cols = 1;
        $rowss = 0;
        $temp = false;
        foreach (array_keys($data) as $field => $value) {
            $chr1 = chr(ord('A') + $rowss);

            $cell = $chr1 . $cols;
            if ($temp == false) {
                $startcell = $cell;
            }
            $temp = true;

            $endcell = $cell;
            $object->getActiveSheet()->setCellValue($cell, $value);
            $object->getActiveSheet()->getColumnDimension($chr1)->setAutoSize(true);
            $rowss++;
        }
        $cell_range = $startcell . ':' . $endcell;
        $object->getActiveSheet()->getStyle($cell_range)->getFont()->setBold(true)->setSize(12);
        $object->getActiveSheet()->getStyle($cell_range)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
        );
        return $object;
    }

    public function exportExcelBody($data, $object) {
        $rowcount = 2;
        //$colcount = 2;
        $colcount = count($data['heading_1']);
        foreach ($data['data_1'] as $key => $val) {
            //$colcount=1;
            $generate_cell_rowid = 0;
            $start_cell = ord('A');
            foreach ($val as $key1 => $val1) {
                $cell_col = $start_cell + $generate_cell_rowid;
                $chr1 = chr($cell_col);
                $cell1 = $chr1 . $colcount;
                $object->getActiveSheet()->setCellValue($cell1, $val1);
                $generate_cell_rowid++;
            }
            $colcount++;
        }
        return $object;
    }

    public function exportToExcel($data, $object) {
        $column_start = 0;
        $rows_start = 0;
        $count = 0;
        $temp = false;
        foreach ($data as $key_main => $value_main) {
            $temp = false;
            foreach ($value_main as $key_sec => $value_sec) {
                foreach ($value_sec as $key => $value) {
                    $count = 0;
                    $rows_start++;
                    $start_cell = ord('A');

                    foreach ($value as $key1 => $val1) {
                        $column_start++;
                        $column_name = $start_cell + $count;
                        $chr1 = chr($column_name);
                        $cell1 = $chr1 . $rows_start;
                        //echo "==".$cell1;
                        //echo "........".$key_sec;
                        if ($key_sec == 'heading') {
                            if ($temp == false) {
                                $startcell = $cell1;
                            }
                            $temp = true;

                            $endcell = $cell1;
                        }

                        $object->getActiveSheet()->setCellValue($cell1, $val1);
                        //$object->getActiveSheet()->getColumnDimension($chr1)->setAutoSize(true);
                        $object->getActiveSheet()->getStyle($cell1)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $object->getActiveSheet()->getStyle($cell1)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $object->getActiveSheet()->getStyle($cell1)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $object->getActiveSheet()->getStyle($cell1)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                        if (is_numeric($val1)) { //If numeric align it center,else left align
                            $object->getActiveSheet()->getStyle($cell1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        } else {
                            $object->getActiveSheet()->getStyle($cell1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                        $generate_cell_rowid++;
                        $count++;
                    }
                    if ($key_sec == 'heading') {
                        $cell_range = $startcell . ':' . $endcell;

                        // $object->getActiveSheet()->getStyle($cell_range)->getFont()->setBold(true)->setSize(12);
                        /* $object->getActiveSheet()->getStyle($cell_range)->applyFromArray(
                          array(
                          'fill' => array(
                          'type' => PHPExcel_Style_Fill::FILL_SOLID,
                          'color' => array('rgb' => 'A6A6A6')
                          ),
                          'borders' => array(
                          'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,
                          'color' => array('rgb' => PHPExcel_Style_Color::COLOR_BLACK)
                          )
                          )
                          )
                          ); */

                        //$object->getActiveSheet()->getStyle($cell_range)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
                    }
                    //$object->getActiveSheet()->getStyle($cell_range)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                }
            }
        }
        return $object;
    }

    /*     * ** Export to excel END *** */

    public function convert_no_to_word($num) {
        $ext = ""; //thousand,lac, crore
        if ($no == 0 || $no == '') {
            $no = 0;
            return $no;
        } else {

            $number_of_digits = strlen($num); //this is call :)
            if ($number_of_digits == 4 || $number_of_digits == 5) {
                $value = round($num / 1000, 2);
                $ext = "k";
            }
            if ($number_of_digits == 6 || $number_of_digits == 7) {
                $value = round($num / 100000, 2);
                $ext = "Lac";
            }
            if ($number_of_digits == 8 || $number_of_digits == 9) {
                $value = round($num / 10000000, 2);
                $ext = "Cr";
            }
            echo $value . " " . $ext;
        }
    }

    public function custom_number_format($num) {
        $locale = new Zend_Locale();
        $locale->setLocale('en_IN');
        //print $locale->toString();
        //print $locale->getRegion();
        $number = Zend_Locale_Format::toNumber($num, array('number_format' => '#,##,##0.00')
        );
        return $number;
    }

////    for dynamic web-service intrigation////////////////////// 
    public function stateTable($state_code = null, $id = null) {
        $select_table = new Zend_Db_Table('dbt_state');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('state' => 'dbt_state'), array('id', 'state_code', 'state_name', 'state_table_name', 'isstate', 'state_dbt_portal_url', 'state_dbt_portal_webservice_url', 'state_dbt_portal_status', 'webservice_integration_status', 'state_dbt_portal_live_date', 'webservice_integration_date'));
        $select->where('state.status=1');
        if ($state_code) {
            $select->where('state.state_code=?', $state_code);
        }
        if ($id) {
            $select->where('state.id=?', $id);
        }
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    public function stateSchemeTable($table_name) {
        $select_table = new Zend_Db_Table($table_name);
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('state_scheme' => $table_name), array('id', 'scheme_name'));
        $select->where('state_scheme.status=1');
        //echo $select;die;
        $results = $select_table->fetchAll($select);
        return $results->toArray();
    }

    public function getStateSchemes($state_code) {
        // $states=$this->stateTable($state_code);
        // $table_name="dbt_".$states[0]['state_table_name']."_scheme";
        // $scheme_results=$this->stateSchemeTable($table_name);
        // $array=array();
        // foreach($scheme_results as $key=>$val){
        // $array[$key]=$val['id'];
        // }
        $array = array(
            '35' => array(124),
            '1' => array(),
            '2' => array(),
            '10' => array(),
        );
        return $array[$state_code];
    }

    public function currencyData($rs = null) {
        setlocale(LC_MONETARY, 'en_IN');
        if ($rs<0) return "-".asDollars(-$rs);
      return number_format($rs, 2);
        //$amount = money_format('%!i', $rs);
        //$amount = explode('.', $amount); //Comment this if you want amount value to be 1,00,000.00
        //return $amount[0];
    }

	function currencyData1($rs = null) {
		setlocale(LC_MONETARY, 'en_IN');
        if ($rs<0) return "-".asDollars(-$rs);
      return number_format($rs, 2);
		//$amount = money_format('%!i', $rs);
		//$amount=explode('.',$amount); //Comment this if you want amount value to be 1,00,000.00
		//return $amount;
	}
	
    public function getKeySchemesCount($cur_financial_year = null) {
        //	echo $cur_financial_year; die;
        $newscm = new Zend_Db_Table("dbt_key_scheme");
        $select = $newscm->select();
        $select->from(array("key" => "dbt_key_scheme"), array('count(id) as total_key_schemes'));
        $select->where("key.status = 1"); //echo $select;die;
        $select->where("key.financial_year =?", $cur_financial_year);
        $scheme_record = $newscm->fetchRow($select);
        if ($scheme_record) {
            $res = $scheme_record->toArray();
            return $res['total_key_schemes'];
        } else {
            return null;
        }
    }

    public function sendGridConfig() {
        $cloud_ip = '104.211.97.98';
        $get_ip = $this->getRequest()->getServer('HTTP_HOST');
        if ($cloud_ip == $get_ip) {
            $smtpServer = 'smtp.sendgrid.net';
            $config = array('ssl' => 'tls',
                'port' => '587',
                'auth' => 'login',
                'username' => 'apikey',
                'password' => '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'
            );
            $transport = new Zend_Mail_Transport_Smtp($smtpServer, $config);
        } else {
            $transport = NULL;
        }
        return $transport;
    }
	
    public function generateUserToken(){
		$time = round(microtime(true) * 1000);
		$randno = bin2hex(openssl_random_pseudo_bytes(8));
		
		return base64_encode($time.$randno);
	}	
	
    public function findMd5Value($user_password) {
        $password = substr($user_password, 0, 12);
        $password.= substr($user_password, 22, 10);
        $password.= substr($user_password, 37);
        return $password;
    }	

}
