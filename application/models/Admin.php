<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Admin extends Zend_Db_Table_Abstract 
{ 
   public function frontviewid()
			{
			
				 $select_table = new Zend_Db_Table('pg_owner');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
				$select->from(array('pg_owner'), array('Owner_id','Owner Name','Contact Details','Email','Address'));

				//$select_feedback = $select_table->fetchAll($select);
				
			    //return $select_feedback->toArray(); 
				$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			public function ownerdetails($id)
			{
				
               $select_table = new Zend_Db_Table('pg_owner');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
				$select->from(array('pg_owner'), array('Owner_id','Owner Name','Contact Details','Email','Address'));
               
				if($id)
				{										 					
				  $select->where('Owner_id = ?', $id);
				}				
				$select_feedback = $select_table->fetchAll($select);
				return $select_feedback->toArray(); 

	
				
			}
			public function updateownerdetails($editdata , $id)
			{
					$update_table = new Zend_Db_Table('pg_owner');
					$data="";
					$where="";
					$data = array(
					'Owner Name' => $editdata['name'],
					'Contact Details' => $editdata['contactno'],
					'Email' => $editdata['emailaddress'],
					'Password' => md5($editdata['password']),
					'Address'=>$editdata['address']
					
					);
					$where = array('Owner_id = ?' => $id);
					
					$update_data = $update_table->update($data, $where);
				   return $update_data ;			
				
			}
			public function delownerinfo($id) {
        $delete_user = new Zend_Db_Table('pg_booking');
        $where = "";
        $where = array('userid = ?' => $id);
        $delete_values = $delete_user->delete($where);
    }
	
	 public function frontid()
			{
			
				 $select_table = new Zend_Db_Table('pg_booking');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
				$select->from(array('pg_booking'), array('userid','PG-Name','username','gender','contactno','address','email'));

				//$select_feedback = $select_table->fetchAll($select);
				
			    //return $select_feedback->toArray(); 
				$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
	public function customerviewdetails($id)
			{
				
               $select_table = new Zend_Db_Table('pg_booking');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
				$select->from(array('pg_booking'), array('userid','PG-Name','username','gender','contactno','address','email'));

               
				if($id)
				{										 					
				  $select->where('userid = ?', $id);
				}				
				$select_feedback = $select_table->fetchAll($select);
				
				return $select_feedback->toArray(); 

	
				
			}
			public function updatebookingdetails($editdata , $id)
			{
					$update_table = new Zend_Db_Table('pg_booking');
					$data="";
					$where="";
					$data = array(
					'PG-Name' => $editdata['pg'],
					'username' => $editdata['name'],
					'gender' => $editdata['gender'],
					'contactno' => $editdata['contactno'],
					'address'=>$editdata['address'],
					'email'=>$editdata['email'],
					
					);
					$where = array('userid= ?' => $id);
					
					$update_data = $update_table->update($data, $where);
				   return $update_data ;			
				
			}
			public function ownerinfo($id) {
        $delete_user = new Zend_Db_Table('pg_owner');
        $where = "";
        $where = array('Owner_id = ?' => $id);
        $delete_values = $delete_user->delete($where);
    }
	public function sideviewidea()
			{ 
			    $select_table = new Zend_Db_Table('pg_divyahomedetails');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_divyahomedetails'), array('pg_id','name','location','rent','rooms','address','balcony','electricity','carparking','security','fireexit','ac'));

				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			public function showdetailsid()
			{
				$select_table = new Zend_Db_Table('pg_nikhilhomedetails');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
		$select->from(array('pg_nikhilhomedetails'),array('home_id','pghome_name','location','rent','numberofrooms','address','balcony','electricity','parking','security','fireexit','AC'));
		$select_info = $select_table->fetchAll($select);
		return $select_info->toArray();
		return $select_info;
			}
			public function getdetailsidea()
			{
				$select_table = new Zend_Db_Table('pg_kaushikahomedetails');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
		$select->from(array('pg_kaushikahomedetails'),array('form_id','pg_name','pg_location','pg_rent','pg_rooms','pg_address','balcony','electricity','parking_area','security','fireexit','Airconditioned'));
		$select_information = $select_table->fetchAll($select);
		return $select_information->toArray();
		return $select_information;
			}
			public function getviewidea()
			{ 
			    $select_table = new Zend_Db_Table('pg_shivahomedetails');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_shivahomedetails'), array('location_id','name','home_location','rentpermonth','rooms','address','balcony_facility','electricity_facility','parking_facility','security','fireexit','fullyac'));

				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			public function viewidea()
			{ 
			    $select_table = new Zend_Db_Table('pg_dakshhomedetails');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_dakshhomedetails'), array('details_id','name','location','permonthrent','rooms','pghome_address','balcony_facility','electricity_facility','parking_facility','security','fireexit','ac_facility'));

				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			}