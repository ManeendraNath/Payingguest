<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Register extends Zend_Db_Table_Abstract 
{  
         

   
		  public function insertRegistrationDetail($dataform)
			{
				
				 $user_table = new Zend_Db_Table('register');

       
        $datainsert = "";
        $datainsert = array(
		      
            'Name' => $dataform['name'],
            'Email' => $dataform['emailaddress'],
            'MobileNo' => $dataform['mobilenumber'],
			'Password'=> md5($dataform['password']),
			'Address' =>$dataform['address']
           
			
        );
		
		
		
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
		
	public function checkUser($emailaddress = null) {
        $select_table = new Zend_Db_Table('register');
        $rowselect = $select_table->fetchAll($select_table->select()->where('Email = ?', trim(($emailaddress))));
		//print_r( $rowselect); die;
        return count($rowselect);
    }
	
	
	
}


