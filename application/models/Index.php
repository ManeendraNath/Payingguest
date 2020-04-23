<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Index extends Zend_Db_Table_Abstract 
{ 
         
		  public function insertRegistrationDetail($dataform)
			{
				
				 $user_table = new Zend_Db_Table('register');

       
        $datainsert = "";
        $datainsert = array(
		      
            'Name' => $dataform['name'],
            'Email' => $dataform['emailaddress'],
            'MobileNo' => $dataform['mobilenumber'],
			'Password'=>$dataform['password']
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
}


