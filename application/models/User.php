<?php

require_once 'Zend/Db/Table/Abstract.php';

class Application_Model_User extends Zend_Db_Table_Abstract {

    public function checkUser($username = null) {
        $select_table = new Zend_Db_Table('dbt_user_master');
        $rowselect = $select_table->fetchAll($select_table->select()->where('user_name = ?', trim(($username))));
        return count($rowselect);
    }

    public function createUser($dataform) {

        $user_table = new Zend_Db_Table('dbt_user_master');
        $created_date = date("Y-m-d H:i:s");
        $datainsert = "";
        $datainsert = array(
            'user_name' => $dataform['user_name'],
            'user_password' => hash_hmac('sha256', $dataform['user_password'], ''),
            'user_first_name' => $dataform['user_first_name'],
            'user_last_name' => $dataform['user_last_name'],
            'user_designation' => $dataform['user_designation'],
            'ministry_id' => ($dataform['ministry_name'] == '') ? NULL : $dataform['ministry_name'],
            'state_code' => ($dataform['state_name'] == '') ? NULL : $dataform['state_name'],
            'agency_code' => NULL,
            'user_role_id' => $dataform['user_role'],
            'user_mobile' => $dataform['user_mobile'],
            'user_email' => $dataform['user_email'],
            'user_address' => NULL,
            'user_status' => 1,
            'sys_gen_pwd_status' => 'y',
            'login_attempt' => 0,
            'reset_login_token' => $dataform['reset_login_token'],
            'updated_by' => $dataform['updated_by'],
            //'updated_by' => 1,
            'created' => $created_date,
            'ip_address' => $dataform['ip_address']
        ); //print'<pre>';print_r($datainsert);die;
        $insertdata = $user_table->insert($datainsert); //die('dd');
        return $insertdata;
    }

    public function createUserLog($dataform) {

        $user_table = new Zend_Db_Table('dbt_user_master');
        $created_date = date("Y-m-d H:i:s");
        $datainsert = "";
        $datainsert = array(
            'user_name' => $dataform['user_name'],
            'first_name' => $dataform['first_name'],
            'last_name' => $dataform['last_name'],
            'user_password' => hash_hmac('sha256', $dataform['password'], ''),
            'user_designation' => $dataform['user_designation'],
            'ministry_id' => $dataform['ministry_id'],
            'scheme_state_id' => '',
            'user_role_id' => $dataform['role'],
            'user_mobile' => $dataform['user_mobile'],
            'user_email' => $dataform['user_email'],
            'user_address' => NULL,
            'user_status' => 1,
            //'updated_by' => $dataform['updated_by'],
            'updated_by' => 1,
            'created' => $created_date,
            'ip_address' => $dataform['ip_address']
        );
        $insertdata = $user_table->insert($datainsert);
        return $insertdata;
    }

    public function editUser($form_data, $user_id = null) {

        $user_table = new Zend_Db_Table('dbt_user_master');
        $insertdata = '';
        $created_date = date("Y-m-d H:i:s");
        $data_array = array(
            'user_first_name' => $form_data['user_first_name'],
            'user_last_name' => $form_data['user_last_name'],
            'user_designation' => $form_data['user_designation'],
            'user_mobile' => $form_data['user_mobile'],
            'user_email' => $form_data['user_email'],
            'updated_by' => trim($form_data['updated_by']),
            'ip_address' => $form_data['ip_address']
        );
        if ($user_id) {
            $condition = array('user_id = ?' => $user_id);
            $insertdata = $user_table->update($data_array, $condition);
        }
        return $insertdata;
    }

    public function listUserRoles($start = null, $limit = null) {
        $select_table = new Zend_Db_Table('dbt_role_master');
        $row = $select_table->fetchAll($select_table->select()->where('role_master_status = 1')->order('role_name DESC')->limit($limit, $start));
        return $row;
    }

    public function getUserName($userId) {
        $select_table = new Zend_Db_Table('dbt_user_master');
        $select_org = $select_table->fetchRow($select_table->select()->where('user_id = ?', trim(intval($userId))));
        if ($select_org) {
            return $select_org->toArray();
        } else {
            return NULL;
        }
    }

    public function getUserDetails($user_id = null, $user_name = null) {

        if ($user_id == '' && $user_name == '') {
            return NULL;
        }
        $user = new Zend_Db_Table("dbt_user_master");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array("u" => "dbt_user_master"), array('*'));
        $select->join(array('r' => 'dbt_role_master'), 'u.user_role_id = r.role_id', array('role_name as user_role'));
        if ($user_id) {
            $select->where("user_id = ?", $user_id);
        }
        if ($user_name) {
            $select->where("user_name = ?", $user_name);
        }
         //$select->where("user_status = ?", 1);
        $result_arr = $user->fetchRow($select);
        if ($result_arr) {
            $result_arr = $result_arr->toArray();
        }
        return $result_arr;
    }
	
	public function getUserDetailsFromlog($user_id = null, $user_name = null) {

        if ($user_id == '' && $user_name == '') {
            return NULL;
        }
        $user = new Zend_Db_Table("dbt_user_master_log");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array("u" => "dbt_user_master_log"), array('distinct(u.user_password)'));     
        if ($user_id) {
            $select->where("user_id = ?", $user_id);
        }
        if ($user_name) {
            $select->where("user_name = ?", $user_name);
        }
         //$select->where("user_status = ?", 1);
		 $select->limit(2,0);
		 $select->order('u.updated DESC');
        $result_arr = $user->fetchAll($select);
        if ($result_arr) {
            $result_arr = $result_arr->toArray();
        }
        return $result_arr;
    }

    public function getUsersList($start, $limit = null, $user_role = null) {
        $select_table = new Zend_Db_Table('dbt_user_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('u' => 'dbt_user_master'), array('user_id', 'user_name', 'user_first_name', 'user_last_name', 'user_designation', 'user_mobile', 'user_email', 'user_role_id', 'user_status', 'state_code'));
        $select->join(array('r' => 'dbt_role_master'), 'u.user_role_id = r.role_id', array('role_name'));

        if ($user_role) {
            $select->where("user_role_id = ?", $user_role);
        }

        //$select->where('r.status = 1');
        $select->order('u.user_id DESC');
        if ($limit) {
            $select->limit($limit, $start);
        }

        $select_org = $select_table->fetchAll($select);
        if ($select_org) {
            return $select_org->toArray();
        } else {
            return NULL;
        }
    }

    public function getStatesList() {
        $select_table = new Zend_Db_Table('dbt_state_master');
        $select_org = $select_table->fetchAll($select_table->select()->order('state_name ASC'));
        if ($select_org) {
            return $select_org->toArray();
        } else {
            return NULL;
        }
    }

    public function getMinistryList($ministry_id_param = null) {
        $newtb = new Zend_Db_Table("dbt_ministry_master");
        $select = $newtb->select();
        $select->setIntegrityCheck(false);
        $select->from(array('ministry' => "dbt_ministry_master"), array('ministry_id'));
        $select->join(array('min' => 'dbt_ministry_details'), 'ministry.ministry_id = min.ministry_id', array('ministry_name'));
        $select->where("ministry.ministry_status = 1");
        if ($ministry_id_param) {
            $select->where("ministry.ministry_id = ? ", $ministry_id_param);
        }
        $select->order("min.ministry_name");
        $rows = $newtb->fetchAll($select);
        if ($rows) {
            return $rows->toArray();
        }
    }

    public function userModelCheckSesionStatus($userid) {
        $user = new Zend_Db_Table("dbt_sessions");
        $select = $user->select();
        $select->from(array("s" => "dbt_sessions"), array("status"));
        $select->where("status = ?", '1');
        $select->where("user_id = ?", $userid);

        $select->where("session_id = ?", session_id());
        //echo $select; die;
        $count_row = $user->fetchAll($select);
        $rowcount = count($count_row);
        return $rowcount;
    }

    public function usermodelchecksession($sesid) {
        $timeout = time();
        $count_table = new Zend_Db_Table('dbt_sessions');
        $count_row = $count_table->fetchAll($count_table->select()->where('session_id = ?', $sesid));
        $resultarr = $count_row->toArray();
        $result = $resultarr[0];
        return $timeout - $result["last_refresh_time"];
    }

    public function usermodellogininsertsession($sesid, $ip, $serverdetails, $userid) {

        $ltime = time();
        $date = date("Y-m-d H:i:s");
        $count_table = new Zend_Db_Table('dbt_sessions');
        $count_row = $count_table->fetchAll($count_table->select()->where('session_id = ?', $sesid));
        $rowcount = count($count_row);
        //echo $rowcount; die;
        //return $rowcount
        if ($rowcount > 0) {

            $data = array('last_refresh_time' => $ltime);
            $where = array('session_id = ?' => $sesid);
            $update_values = $count_table->update($data, $where);
            return $update_values;
        } else {
            $datainsert = array(
                'session_id' => $sesid,
                'user_id' => $userid,
                'login_time' => $ltime,
                'hostname' => $ip,
                'session' => $serverdetails,
                'status' => 1,
                'created' => $date,
            );
            //print_r($datainsert); die;
            $insertdata = $count_table->insert($datainsert);
            //print_r($insertdata); die;
            return $insertdata;
        }
    }

    public function usermodellogincounsession() {

        //$timeout=time();
        $staus = 1;
        $count_table = new Zend_Db_Table('dbt_sessions');
        $count_row = $count_table->fetchAll($count_table->select()->where('status = ?', $staus));
        $rowcount = count($count_row);
        return $rowcount;
    }

    public function getMinistryInfo($id) {
        $select_tablerole = new Zend_Db_Table('dbt_ministry');
        $rowselectrole = $select_tablerole->fetchRow($select_tablerole->select()->where('id = ?', $id));
        $role_name = $rowselectrole->toArray();
        return $role_name['ministry_name'];
    }

    public function updateSchemeOwner($form_data) {

        $created = date("Y-m-d H:i:s");

        $table = new Zend_Db_Table('dbt_scheme_assign');
        $count_row = $table->fetchAll($table->select()->where('user_id = ?', $form_data['scheme_owner_id']));
        $rowcount = count($count_row);

        if ($rowcount > 0) {
            foreach ($form_data['scheme_list'] as $scheme_id) {
                $data = array('scheme_id' => $scheme_id);
                $where = array('user_id = ?' => trim($form_data['scheme_owner_id']));
                $query = $table->delete($data, $where); //Delete previous records
            }
        }

        foreach ($form_data['scheme_list'] as $scheme_id) {
            $datainsert = array(
                'user_id' => $form_data['scheme_owner_id'],
                'scheme_id' => trim($scheme_id),
                'scheme_assign_status' => 1,
                'updated_by' => $form_data['updated_by'],
                'created' => $created,
                'ip_address' => $form_data['ip_address']
            );
            $query = $table->insert($datainsert); //Insert new records
        }
        return $query;
    }

    public function getSchemeOwnerDetails($user_id = null, $user_role = null) {

        $user = new Zend_Db_Table("dbt_scheme_assign");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array("um" => "dbt_user_master"), array('user_id', 'user_name', 'user_first_name', 'user_last_name', 'user_designation', 'user_mobile', 'user_email'));
        $select->joinLeft(array("rm" => "dbt_role_master"), 'um.user_role_id = rm.role_id', array('role_name'));
        $select->joinLeft(array('sa' => 'dbt_scheme_assign'), 'um.user_id = sa.user_id', array('scheme_id as scheme_list'));
        $select->joinLeft(array('sd' => 'dbt_scheme_details'), 'sa.scheme_id = sd.scheme_id', array('scheme_name'));
        $select->joinLeft(array('md' => 'dbt_ministry_details'), 'um.ministry_id = md.ministry_id', array('ministry_name', 'ministry_id'));
        $select->where("um.user_status = 1");

        if ($user_id) {
            $select->where("um.user_id = ?", $user_id);
        }
        if ($user_role) {
            $select->where("um.user_role_id = 4");
        }
        $result_arr = $user->fetchAll($select);
        if ($result_arr) {
            $result_arr = $result_arr->toArray();
        }
        return $result_arr;
    }

     public function updateUserStatus($data, $id) {

        if (count($data) > 0) {
            $user_master_table = new Zend_Db_Table('dbt_user_master');
            $where = array('user_id = ?' => intval($id));
            $update_values = $user_master_table->update($data, $where);
        }
    }
    
    public function getActiveUser($user_id = null, $user_name = null) {

        if ($user_id == '' && $user_name == '') {
            return NULL;
        }
        $user = new Zend_Db_Table("dbt_user_master");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array("u" => "dbt_user_master"), array('user_id','user_name','user_password','user_first_name','user_last_name','ministry_id', 'state_code','user_role_id','user_email','user_mobile','user_address','user_status','sys_gen_pwd_status','login_attempt','reset_login_token'));
        $select->join(array('r' => 'dbt_role_master'), 'u.user_role_id = r.role_id', array('role_name as user_role'));
        if ($user_id) {
            $select->where("user_id = ?", $user_id);
        }
        if ($user_name) {
            $select->where("user_name = ?", $user_name);
        }
         $select->where("user_status = ?", 1);
        $result_arr = $user->fetchRow($select);
        if ($result_arr) {
            $result_arr = $result_arr->toArray();
        }
        return $result_arr;
    }
    
    public function allActiveUser() {

        $results = array();
        $user = new Zend_Db_Table("dbt_user_master");
        $select = $user->select();
        $select->setIntegrityCheck(false);
        $select->from(array("user_master" => "dbt_user_master"), array('user_id','user_name','user_password','user_first_name','user_last_name','ministry_id', 'state_code','user_role_id','user_email','user_mobile','user_address','user_status','sys_gen_pwd_status','login_attempt','reset_login_token'));
        $select->join(array('role_master' => 'dbt_role_master'), 'user_master.user_role_id = role_master.role_id', array('role_name as user_role'));
         $select->where("user_master.user_status = ?", 1);
         $select->order("user_master.user_name asc");
        $result_arr = $user->fetchAll($select);
        if (count($result_arr->toArray())>0) {
            $results = $result_arr->toArray();
        }
        return $results;
    }
    
}
