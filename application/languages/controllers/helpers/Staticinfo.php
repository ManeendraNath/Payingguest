<?php

class Zend_Controller_Action_Helper_Staticinfo extends Zend_Controller_Action_Helper_Abstract {

    public function servicesCode() {
        return array('pan' => 'pan', 'passport' => 'passport', 'fsba' => 'fsba', 'fsp' => 'fsp', 'mtc' => 'mtc', 'dl' => 'dl', 'teachers' => 'teachers', 'lr' => 'lr', 'prisoners' => 'prisoners');
    }

    public function OptionSection7of57() {
        return array('3' => 'Already Notified', '2' => 'Notification under process', '1' => 'Yet to be Notified');
    }

    public function OptionSchemeSpecificMISPreparation() {
        return array('3' => 'Already Prepared', '2' => 'Under Preparation', '1' => 'Yet to Start');
    }

    public function OptionSchemeSpecificMISIntegration() {
        return array('3' => 'Already Done', '2' => 'Under Process', '1' => 'Yet to be done');
    }
	
	public function OptionBenefiType(){
        return array('1' => 'Cash', '2' => 'In Kind', '3' => 'Others','5' => 'Cash and In Kind','6' => 'Services','7' => 'Service Enabler');
    }
	public function OptionTypeOfScheme(){
        return array('1' => 'Central Sector', '2' => 'Centrally Sponsored');
    }
    public function OptionSchemeGroup(){
        return array('1' => 'PAHAL', '2' => 'MGNREGS','3' => 'NSAP','4' => 'SCHOLARSHIP SCHEME','5' => 'OTHERS','6' => 'PMAYG','7' => 'PDS');
    }
	public function OptionSchemePfms(){
        return array('yes' => 'Yes', 'no' => 'No');
    }

	public function OptionSchemeMIS(){
        return array('1' => 'Online system / MIS at Conceptual Stage', '2' => 'Online system / MIS under development', '3' => 'Online system / MIS implemented at field level (Roll out) and data reported manually', '4' => 'Online system / MIS integrated with DBT Bharat portal but data reported manually', '5' => 'Online system / MIS integrated with DBT Bharat portal and report submitted through web-services');
    }	
	
	/***when we create or edit email sent mail to officers 11-02-2019************/
	public function OfficerEmailIds(){
         return array('0' => 'feedback@dbtbharat.gov.in', '1' => 'cpms.viswanath@gmail.com','2'=>'samsher.ali@gov.in');
    }
	
	public function drilldown_mailId(){
        return array('sharma.anupam@pwc.com','rohit.k.sharma@pwc.com','sanjeev.dogra@in.pwc.com','shashvat.dixit@pwc.com');
    }
    public function newOptionSchemeMIS(){
        return array('1' => 'MIS not initiated', '2' => 'MIS under development', '3' => 'MIS developed', '4' => 'MIS under integration', '5' => 'MIS integrated but data reported manually', '6' => 'MIS integrated and data reported through web-services');
    }
	/***get the  cumulative saving till march 2019 based upon the scheme code 30-05-2019***********/
	public function get_cumulative_saving($scheme_code=null)
	{
			if($scheme_code == 'AP34D'){
			$cumulative_saving = 595990000000;
			}else if($scheme_code == 'A894A'){
			$cumulative_saving = 476330000000;
			}else if($scheme_code == 'BXASD'){
			$cumulative_saving = 207904500000;
			}else if($scheme_code == 'AFT78'){
			$cumulative_saving = 450036000;
			}else if($scheme_code == 'AG5FG'){
			$cumulative_saving = 519040500;
			}else if($scheme_code == 'AYUTB'){
			$cumulative_saving = 622500000;
			}else if($scheme_code == 'BPASX'){
			$cumulative_saving = 3198615399;
			}else if($scheme_code == 'BPAMX'){
			$cumulative_saving = 29466180;
			}else if($scheme_code == 'BEAMP'){
			$cumulative_saving = 91710000;
			}else if($scheme_code == 'B0EU2'){
			$cumulative_saving = 146000;
			}else if($scheme_code == 'BGUNM'){
			$cumulative_saving = 7400000;
			}else if($scheme_code == 'BXRSM'){
			$cumulative_saving = 26900000;
			}else if($scheme_code == 'AG4EQ'){
			$cumulative_saving = 940000;
			}else if($scheme_code == 'B1IS1'){
			$cumulative_saving = 15237500000;
			}else if($scheme_code == 'A1R0K'){
			$cumulative_saving = 100000000000;
			}else{
			 $cumulative_saving = 0;
			}
			return $cumulative_saving;
					
		
	}
	/**********end**********************************************************************/
}
