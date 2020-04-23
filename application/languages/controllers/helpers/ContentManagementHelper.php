<?php
class Zend_Controller_Action_Helper_ContentManagementHelper extends Zend_Controller_Action_Helper_Abstract{
	
	/**
	* language = 1 means hindi, language = 2 means english
	* Where is used this function?:
	* used in ministryviewAction in MinistryController.php
	* Author: Chakshu Gulati
	* Created on 24 may 2018
	*/
	public function getContentManagementData($title = null,$language = null,$search=null,$start=null,$limit=null)
	{
			$select_table = new Zend_Db_Table('dbt_content_management');
			$select = $select_table->select();
			$select->setIntegrityCheck(false);
			$select->from(array('content_management' => 'dbt_content_management'), array('title','menu_type','sort_order','translation_id','id','description','language','status','created','updated'));
			$select->joinLeft(array('langu' => 'dbt_language'), 'content_management.language = langu.id', array('langu.title as langname'));
			$select->where('content_management.language  = ?',trim(intval($language)));
			if($search!=""){         
			$select->where('content_management.menu_type =?',$search);
			}
			if($title!=""){         
			$select->where('content_management.title =?',$title);
			}
			$select->order('content_management.id DESC');
			$select->limit($limit,$start);
			$select_org = $select_table->fetchAll($select);
			return $select_org;
		
	}
	
	public function getContentManagementDataById($id=null,$translation_id=null)
	{
		$select_table = new Zend_Db_Table('dbt_content_management');
		if($id!=""){
		$rowselect = $select_table->fetchRow($select_table->select()->where('id = ?',trim(intval($id))));
		}
		if($translation_id!=""){
		$rowselect = $select_table->fetchRow($select_table->select()->where('translation_id = ?',trim(intval($translation_id))));
		}
		return $rowselect;    

		
	}
	
	
	
}
?>