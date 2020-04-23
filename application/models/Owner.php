<?php
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_Owner extends Zend_Db_Table_Abstract 
{ 
         
		  public function insertOwnerDetail($data_form)
			{
				
				 $user_table = new Zend_Db_Table('pg_owner');

       
        $datainsert = "";
        $datainsert = array(
		      
            'Owner Name' => $data_form['name'],
			'Contact Details' => $data_form['contactno'],
            'Email' => $data_form['emailaddress'],
            'Password'=> md5($data_form['password']),
			'Address' => $data_form['address']
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
			
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
        $delete_user = new Zend_Db_Table('pg_owner');
        $where = "";
        $where = array('Owner_id = ?' => $id);
        $delete_values = $delete_user->delete($where);
    }
		public function checkUser($emailaddress = null) {
        $select_table = new Zend_Db_Table('pg_owner');
        $rowselect = $select_table->fetchAll($select_table->select()->where('Email = ?', trim(($emailaddress))));
		//print_r( $rowselect); die;
        return count($rowselect);
    }
	public function insertpgDetail($dataform)
			{
				
		  $user_table = new Zend_Db_Table('pg_divyahomedetails');

		     $datainsert = "";
             $datainsert = array(
			 'name'  => $dataform['name'],
            'location' => $dataform['location'],
			'rent' => $dataform['rent'],
            'rooms' => $dataform['rooms'],
            'address' => $dataform['address'],
			'balcony' => $dataform['balcony'],
			'electricity' => $dataform['electricity'],
            'carparking' => $dataform['parking'],
			'security' => $dataform['security'],
			'fireexit' => $dataform['fireexit'],
			'ac' => $dataform['airconditioned'],
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
			public function insertDetail($dataform)
			{
				
		  $user_table = new Zend_Db_Table('pg_nikhilhomedetails');

		     $datainsert = "";
             $datainsert = array(
			'pghome_name'  => $dataform['name'],
            'location' => $dataform['location'],
			'rent' => $dataform['rent'],
            'numberofrooms' => $dataform['rooms'],
            'address' => $dataform['address'],
			'balcony' => $dataform['balcony'],
			'electricity' => $dataform['electricity'],
            'parking' => $dataform['parking'],
			'security' => $dataform['security'],
			'fireexit' =>$dataform['fireexit'],
			'AC' => $dataform['airconditioned'],
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
			public function insertpgdata($dataform)
			{
				
		  $user_table = new Zend_Db_Table('pg_kaushikahomedetails');

		     $datainsert = "";
             $datainsert = array(
			'pg_name'  => $dataform['name'],
            'pg_location' => $dataform['location'],
			'pg_rent' => $dataform['rent'],
            'pg_rooms' => $dataform['rooms'],
            'pg_address' => $dataform['address'],
			'balcony' => $dataform['balcony'],
			'electricity' => $dataform['electricity'],
            'parking_area' => $dataform['parking'],
			'security' => $dataform['security'],
			'fireexit' => $dataform['fireexit'],
			'Airconditioned' => $dataform['airconditioned'],
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
			public function pginformation($dataform)
			{
				
		  $user_table = new Zend_Db_Table('pg_shivahomedetails');

		     $datainsert = "";
             $datainsert = array(
			'name'  => $dataform['name'],
            'home_location' => $dataform['location'],
			'rentpermonth' => $dataform['rent'],
            'rooms' => $dataform['rooms'],
            'address' => $dataform['address'],
			'balcony_facility' => $dataform['balcony'],
			'electricity_facility' => $dataform['electricity'],
            'parking_facility' => $dataform['parking'],
			'security' => $dataform['security'],
			'fireexit' => $dataform['fireexit'],
			'fullyac' => $dataform['airconditioned'],
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
			public function pggetdetails($data_form)
			{
				
		  $user_table = new Zend_Db_Table('pg_dakshhomedetails');

		     $datainsert = "";
             $datainsert = array(
			'name'  => $data_form['name'],
            'location' => $data_form['location'],
			'permonthrent' => $data_form['rent'],
            'rooms' => $data_form['rooms'],
            'pghome_address' => $data_form['address'],
			'balcony_facility' => $data_form['balcony'],
			'electricity_facility' => $data_form['electricity'],
            'parking_facility' => $data_form['parking'],
			'security' => $data_form['security'],
			'fireexit' => $data_form['fireexit'],
			'ac_facility' => $data_form['airconditioned'],
			

			
        );
      //echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
        return $insertdata;								
														
			}
		public function customerdetails()
			{
				$select_table = new Zend_Db_Table('pg_booking');
				$select = $select_table->select();
				$select->setIntegrityCheck(false);
		$select->from(array('pg_booking'),array('userid','PG-Name','username','gender','contactno','address','email'));
		$select_info = $select_table->fetchAll($select);
		return $select_info->toArray();
		return $select_info;
			}
		public function ownerDetail($dataform)
			{
				
				
		  $user_table = new Zend_Db_Table('pg_home');
                
		     $datainsert = "";
             $datainsert = array(
			 'ownername'  => $dataform['ownername'],
			 'name'  => $dataform['name'],
            'location' => $dataform['location'],
			'email' => $dataform['email'],
            'rooms' => $dataform['rooms'],
			'rent' => $dataform['rent'],
            'address' => $dataform['address'],
			'balcony' => $dataform['balcony'],
			'electricity' => $dataform['electricity'],
            'carparking' => $dataform['parking'],
			'security' => $dataform['security'],
			'fireexit' => $dataform['fireexit'],
			'ac' => $dataform['airconditioned']
			

			
        );
    // echo "<pre>";print_r($datainsert); die;	

        $insertdata = $user_table->insert($datainsert);
		
        return $insertdata;								
														
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
			
}
			