public function getowner($owner_id = null, $emailaddress = null) {

        if ($owner_id == '' && $emailaddress == '') {
            return NULL;
        }
        $user = new Zend_Db_Table("pg_owner");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pg_owner'), array('Owner_id','Owner Name','Contact Details','Email','Password','Address','reset_signtoken'));
       
        if ($owner_id) {
            $select->where("Owner_id = ?", $owner_id);
        }
        if ($$emailaddress) {
            $select->where("Email = ?", $emailaddress);
        }
         
        $result_arr = $user->fetchRow($select);
        if ($result_arr) {
            $result_arr = $result_arr->toArray();
        }
        return $result_arr;
    }
public function updateownerstate($owner_id , $columns) {

         if (!array_key_exists('reset_signtoken', $columns))
            unset($columns['reset_signtoken']);

        if (count($columns) > 0) {
            $user_table = new Zend_Db_Table('pg_owner');
            $where = array('Owner_id = ?' => $owner_id);
            $update_values = $user_table->update($columns, $where);
        }
		}
		 public function changeownerPassword($newpassword, $owner_id) {

        //Update new user password 
        $select_table = new Zend_Db_Table('pg_owner');
       
        $columns = array('Password' => $newpassword);
        $match = array("Owner_id = ?" => $owner_id);
        $update_values = $select_table->update($columns, $match);
        return 1;
    }
	public function getActiveUser($owner_id = null, $emailaddress = null) {
      
        if ($owner_id == '' && $emailaddress == '') {
            return NULL;
        }
		
        $user = new Zend_Db_Table("pg_owner");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pg_owner'), array('Owner_id','Owner Name','Contact Details','Email','Password','Address','reset_signtoken'));
       
        if ($owner_id) {
            $select->where("Owner_id = ?", $owner_id);
        }
        if ($emailaddress) {
            $select->where("Email = ?", $emailaddress);
        }
         
        $result_arr = $user->fetchRow($select);
        if ($result_arr) {
            $result_arr = $result_arr->toArray();
        }
        return $result_arr;
    }
    }
