<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Customer extends Zend_Db_Table_Abstract 
{
	public function sideviewid()
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
			public function viewid()
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
			public function getviewid()
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
			
			public function showdetails()
			{
				$select_table = new Zend_Db_Table('pg_nikhilhomedetails');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
		$select->from(array('pg_nikhilhomedetails'),array('home_id','pghome_name','location','rent','numberofrooms','address','balcony','electricity','parking','security','fireexit','AC'));
		$select_info = $select_table->fetchAll($select);
		return $select_info->toArray();
		return $select_info;
			}
			public function getdetails()
			{
				$select_table = new Zend_Db_Table('pg_kaushikahomedetails');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
		$select->from(array('pg_kaushikahomedetails'),array('form_id','pg_name','pg_location','pg_rent','pg_rooms','pg_address','balcony','electricity','parking_area','security','fireexit','Airconditioned'));
		$select_information = $select_table->fetchAll($select);
		return $select_information->toArray();
		return $select_information;
			}
			
			
			
		 public function insertbookDetail($dataform)
			{
				
				 $users_table = new Zend_Db_Table('pg_booking');

       
         $datainsert = "";
         $datainsert = array(
		 
		    'PG-Name'  => $dataform['pg'],
            'username' => $dataform['name'],
			'gender' => $dataform['gender'],
			'contactno' => $dataform['contactno'],
            'address' => $dataform['address'],
            'email' => $dataform['email']
			 );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $users_table->insert($datainsert);
        return $insertdata;								
														
			}
			  public function sideviewidea()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','name','location','address'));
                  //$select->where('pg_id=17');
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			 public function sideviewidea1()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','ownername','email','name','location','rent','rooms','address','balcony','electricity','carparking','security','fireexit','ac'));
              
			    $select->where("location = ?", Delhi);
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
						// public function sideviewidea12()
			// { 
			    // $select_table = new Zend_Db_Table('pg_home');
				 // $select = $select_table->select();
				 // $select->setIntegrityCheck(false);
		   // $select->from(array('pg_home'), array('pg_id','name','address'));
                  // $select->where('pg_id=17');
				  
				// $select_feedback = $select_table->fetchAll($select);
				
			    // return $select_feedback->toArray(); 
				// $select_feedback = $select_table->fetchAll($select)->toArray();
				// echo"<pre>";print_r($select_feedback);
				// die;
				
			// return $select_feedback; 

			// }
			public function sideviewideagoa()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','email','name','location','rent','rooms','address','balcony','electricity','carparking','security','fireexit','ac'));
              
			   $select->where("location = ?", Goa);
				
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			public function sideviewideapune()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','email','name','location','rent','rooms','address','balcony','electricity','carparking','security','fireexit','ac'));
              
			   $select->where("location = ?", Pune);
				
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			public function sideviewideamumbai()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','email','name','location','rent','rooms','address','balcony','electricity','carparking','security','fireexit','ac'));
              
			   $select->where("location = ?", Mumbai);
				
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			public function sideviewideachennai()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','email','name','location','rent','rooms','address','balcony','electricity','carparking','security','fireexit','ac'));
              
			   $select->where("location = ?", Chennai);
				
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
			 public function getdata()
			{ 
			    $select_table = new Zend_Db_Table('pg_home');
				 $select = $select_table->select();
				 $select->setIntegrityCheck(false);
		   $select->from(array('pg_home'), array('pg_id','ownername','name','location','address'));
              
			   $select->order('location ASC');
				$select_feedback = $select_table->fetchAll($select);
				
			    return $select_feedback->toArray(); 
				//$select_feedback = $select_table->fetchAll($select)->toArray();
				//echo"<pre>";print_r($select_feedback);
				//die;
				
			return $select_feedback; 

			}
}
		    
	