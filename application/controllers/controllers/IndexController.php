<?php

require_once 'Zend/Controller/Action.php';


class IndexController extends Zend_Controller_Action {


    public function init() {
		
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_IndexForm();
	}
	
	public function indexAction(){
	       
		
        $this->view->assign('registration_details' );
    
	
	}
	 public function registerAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }

       $form = new Application_Form_IndexForm();
        $form->addform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				
				$cm_list = new Application_Model_Index;
                $user_record = $cm_list->insertRegistrationDetail($dataform);
				//echo "<pre>";print_r( $user_record); die;
				
                if ($user_record) {
                    $this->_redirect('Index');
                } else {
                    $this->view->assign('errormessage', 'Error to insert data.');
                }
            //}
        }
    }
}
	  
