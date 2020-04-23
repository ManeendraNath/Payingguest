<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Contactus extends Zend_Db_Table_Abstract 
{ 
         
		  public function insertContactDetail($dataform)
			{
				
				 $user_table = new Zend_Db_Table('pg_contactinfo');

       
        $datainsert = "";
        $datainsert = array(
		      
            'Name' => $dataform['name'],
            'ContactNo' => $dataform['contactno'],
            'Emailaddress' => $dataform['emailaddress'],
			'Address'=>$dataform['address']
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
			public function frontview()
			{
			
				 $select_table = new Zend_Db_Table('pg_contactinfo');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
				$select->from(array('pg_contactinfo'), array('Contactid','Name','ContactNo','Emailaddress','Address'));

				//$select_feedback = $select_table->fetchAll($select);
				
			    //return $select_feedback->toArray(); 
				$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
}
			