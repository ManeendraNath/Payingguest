 <?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Image extends Zend_Db_Table_Abstract 
{
 
 
 
 public function insertpgDetail($dataform)
			{
				
				 $users_table = new Zend_Db_Table('image');

       
         $datainsert = "";
         $datainsert = array(
		 
		  'image_name' => $dataform['name'],
            
			 );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $users_table->insert($datainsert);
		 
        return $insertdata;								
														
			}
			 public function side()
			{ 
			    $select_table = new Zend_Db_Table('image');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('image'), array('*'));
                 //$select->where('pg_id=17');
				//$select_feedback = $select_table->fetchAll($select);
				
			   // return $select_feedback->toArray(); 
				$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 


			}
}