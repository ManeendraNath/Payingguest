<?php

/**
 * Action Helper for common function in project
 */
class Zend_Controller_Action_Helper_Common extends Zend_Controller_Action_Helper_Abstract {
    
    /*
     * This function is used rediret with message.
     */
     public function redirect($msg) {
      

        if($msg=='success'){

            return array('type' => 'success_message','msg' => SUSSC_MSG);
         
        }else if($msg=='error'){
           
            return array('type' => 'error_message','msg' => 'Something went wrong. Please try again!');  

        }else if($msg=='denied'){
             
             return array('type' => 'error_message','msg' => ACCESS_DENIED);  

        }else if($msg=='reject'){
            
            return array('type' => 'success_message','msg' => REJECT_MSG);  

        }else if($msg == 'exist')
        {
            return array('type' => 'error_message','msg' => ALLREADY_EXIST);  
            
        }
        else if($msg == 'insert')
        {
            return array('type' => 'success_message','msg' => RECORD_INSERTED);  
            
        }
        else if($msg == 'update')
        {
            return array('type' => 'success_message','msg' => RECORD_UPDATED);  
            
        }
        else if($msg == 'delete')
        {
            return array('type' => 'success_message','msg' => RECORD_DELETED);  
            
        }
        else if($msg == 'activate')
        {
            return array('type' => 'success_message','msg' => RECORD_ACTIVATED);  
            
        }

        else if($msg == 'request')
        {
            return array('type' => 'success_message','msg' => 'Request submitted successfully, approval awaited');  
            
        }
         else if($msg == 'approve')
        {
            return array('type' => 'success_message','msg' => 'Request approved successfully for nation to state wise.');  
            
        }

       
    }


     public function getallFinancialYear() {
        $year_ini = '';
        $financial_year_arr = array();
        $curre_year = strtotime(date("d-m-Y"));
        $fixedyear = strtotime(date("d-m-Y", strtotime(FY_START_DATE)));
        if ($curre_year > $fixedyear) {
            $start = date("Y");
        } else if ($curre_year <= $fixedyear) {
            $dataa = date("Y") - 1;
            $start = $dataa;
        }
        $end_year = $start + 1;
        $dateadded = $start . "_" . $end_year;

        for ($year_ini = 2019; $year_ini < $end_year; $year_ini++) {
            $next_yr = $year_ini + 1;
            $financial_year_arr[] = $year_ini . "_" . $next_yr;
        }

        return $financial_year_arr;
    }   

}
