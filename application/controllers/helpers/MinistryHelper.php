<?php

class Zend_Controller_Action_Helper_MinistryHelper extends Zend_Controller_Action_Helper_Abstract {

    /**
     * getMinistryData() is get the data with condition from Ministry 
     * language = 1 means hindi, language = 2 means english
     * Where is used this function?:
     * used in ministryviewAction in MinistryController.php
     * Author: Chakshu Gulati
     * Created on 21 may 2018
     */
    public function getMinistryData($language = null,$search = null,$ministry_name=null,$ministry_id = null,$start = null,$limit = null,$status =null) {  // get the ministry data from the dbt_ministry table 
	//echo $search; die;
//	$language=1;
		$select_table = new Zend_Db_Table('dbt_ministry_master');
        $select = $select_table->select();
        $select->setIntegrityCheck(false);
        $select->from(array('ministry' => 'dbt_ministry_master'), array('ministry_id', 'ministry_status','created', 'updated','ministry_status'));
		$select->join(array('ministrydetails' => 'dbt_ministry_details'), 'ministry.ministry_id = ministrydetails.ministry_id', array('ministry_details_id','ministry_name','language_id'));
		$select->where('ministrydetails.language_id  = ?',trim(intval($language)));
		if($ministry_name!=null)
		{
        $select->where('ministrydetails.ministry_name  = ?',trim($ministry_name));
		}
		if($ministry_id!=null)
		{
		$select->where('ministry.ministry_id  = ?',trim(intval($ministry_id)));
		}
		if($status!=null)
		{
		$select->where('ministry.ministry_status  = ?',trim(intval($status)));
		}
		if($search!=null)
		{
		$select->where('ministrydetails.ministry_name LIKE ?', '%' . $search . '%');
		}
		$select->order('ministry.ministry_id DESC');
		$select->limit($limit, $start);	
//echo $select; die;
        $get_min_data = $select_table->fetchAll($select);
		return  $get_min_data;
    }
	
	 public function getMinistryDataById($id){  // get the ministry data from the dbt_ministry table based upon the id 
		$select_table = new Zend_Db_Table('dbt_ministry_details');
		$rowselect = $select_table->fetchRow($select_table->select()->where('ministry_Id = ?',trim(intval($id))));
		return $rowselect;  
    }
	public function getMinistryDataInHindi($id)
	{
		$select_table = new Zend_Db_Table('dbt_ministry_details');
		$select = $select_table->select();
		$select->where('ministry_Id = ?',trim(intval($id)));
		$rowselect = $select_table->fetchAll($select);
		return $rowselect;  
	}

}

?>