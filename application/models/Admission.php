<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Admission extends Zend_Db_Table_Abstract 
{
 public function insertbookDetail($dataform)
			{
				
				 $users_table = new Zend_Db_Table('pg_admission');

       
         $datainsert = "";
         $datainsert = array(
		    'file_name'  => $dataform['image'],
           
			 );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $users_table->insert($datainsert);
        return $insertdata;								
														
			}
			  
		    }