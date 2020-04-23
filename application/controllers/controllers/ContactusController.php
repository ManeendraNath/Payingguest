<?php

//require_once 'Zend/Session/Namespace.php';
//require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
//require_once 'Zend/Validate.php';

class ContactusController extends Zend_Controller_Action {

    //protected $rolearray = array('1');
  //  protected $user = '';
   // protected $user_ip = '';
   // protected $sessionid = '';
     protected $form = '';
	 public function init() {
		 
  
      $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_ContactusForm();
        //$layout = $this->_helper->layout();
        // if ($this->_helper->Functions->validateResponseMethod() == FALSE) {
			
            // $this->_redirect('');
        // }
   }
	public function indexAction() {
		
       // if ($this->user->user_name == '') {
          //  $this->_redirect('');
       // }
        //if (!in_array($this->user->user_role, $this->rolearray)) {
            //$this->_redirect('');
       // }
            $cm_list = new Application_Model_Contactus;
                $user_record = $cm_list->frontview();
				
			//	echo "<pre>jkhjhk";print_r( $user_record); die;
		
        $this->view->assign('contact_details', $user_record );
    
	}
	 public function addContactusAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }

       $form = new Application_Form_ContactusForm();
        $form->addform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				
				$cm_list = new Application_Model_Contactus;
                $user_record = $cm_list->insertContactDetail($dataform);
				//echo "<pre>";print_r( $user_record); die;
				
                if ($user_record) {
                    $this->_redirect('Contactus');
                } else {
                    $this->view->assign('errormessage', 'Error to insert data.');
                }
            //}
        }
    }
}