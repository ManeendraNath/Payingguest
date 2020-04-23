<?php

require_once 'Zend/Controller/Action.php';


class RegisterController extends Zend_Controller_Action {
protected $auth_model = '';

    public function init() {
		 $this->register_model = new Application_Model_Register;
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_RegisterForm();
	   
	}
	
	public function indexAction(){
	       
		
       // $this->view->assign('registration_details' );
    
	   //$this->view->assign('login_details' );
	}
	 public function registerAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }
	  
		
       $form = new Application_Form_RegisterForm();
        $form->addform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				 if($form->isValid($_POST)){
                $dataform = $form->getValues();
			 
                if($dataform['password'] != $dataform['repeatpassword']){
                   $this->view->errorMessage = "Password and repeat password don't match.";
                    return false;
                }
				 }
                unset($dataform['repeatpassword']);
                
          
                
			
            //}
			  	  
		$count_records = $this->register_model->checkUser($dataform['emailaddress']);
		
		 //echo"<pre>"; print_r($count_records);die;
		if (($count_records == 0)) {
			$cm_list = new Application_Model_Register;
                $user_record = $cm_list->insertRegistrationDetail($dataform);

                    $this->_redirect('Register/register');

	   }
	   else {
             
                        $this->view->assign('errorMessage', 'Email already exists in the Database.');
                        return false;
                    }
               
	   
    }
	 }

	public function signinAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }

       $form = new Application_Form_RegisterForm();
        $form->loginform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				$this->_redirect('/Customer/fulldetails');
				
        }
    }
	public function forgetpasswordAction() {
		
		$form = new Application_Form_RegisterForm();
        $form->forgetpasswordform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				$this->_redirect('Register');
				
	
	
}
	}
	
	}