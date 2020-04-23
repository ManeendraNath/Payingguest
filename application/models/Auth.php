 <?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Auth extends Zend_Db_Table_Abstract 
{ 
 public function insertRegisterDetail($form_data)
			{
				
				 $user_table = new Zend_Db_Table('dbt_admissionform');

       
        $datainsert = "";
        $datainsert = array(
		   
            'Name' => $form_data['name'],
            'Email' => $form_data['emailaddress'],
            'MobileNo' => $form_data['mobile'],
			'Password' => $form_data['password'],
			
			

			
        );
     //  echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}