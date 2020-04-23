<?php

require_once 'Zend/Controller/Action.php';


class OwnerController extends Zend_Controller_Action {


    public function init() {
		

		 $this->_helper->layout->setLayout('layout');
       $this->owner_model = new Application_Model_Owner;
	  //$this->sendgrid_mail = $this->_helper->Custom->sendGridConfig();
       $this->form = new Application_Form_OwnerForm();
	}
	
	public function indexAction(){
	     $cm_list = new Application_Model_Owner;
         $user_record = $cm_list->frontviewid();
		$this->view->assign('owner_registration_details',$user_record );
	}
	 public function registrationAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }

       $form = new Application_Form_OwnerForm();
        $form->registerform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $data_form = $request->getPost();
				 if($form->isValid($_POST)){
                $dataform = $form->getValues();
				
				//$cm_list = new Application_Model_Owner;
               // $user_record = $cm_list->insertOwnerDetail($data_form);
				//echo "<pre>";print_r( $user_record); die;
				 if($data_form['password'] != $data_form['repeatpassword']){
                   $this->view->errorMessage = "Password and repeat password don't match.";
                    return false;
                }
				 }
                unset($data_form['repeatpassword']);
				$count_records = $this->owner_model->checkUser($data_form['emailaddress']);
		
		 //echo"<pre>"; print_r($count_records);die;
		if (($count_records == 0)) {
			$cm_list = new Application_Model_Owner;
                $user_record = $cm_list->insertOwnerDetail($data_form);

                    $this->_redirect('Owner/divya-pghome');

	   }
	   else {
             
                        $this->view->assign('errorMessage', 'Email already exists in the Database.');
                        return false;
                    }
                //$cm_list = new Application_Model_Owner;
                //$user_record = $cm_list->insertOwnerDetail($data_form);
               
            //}
        }
	 }
	public function editOwnerdetailsAction() { //edit role by id
       
	    $form = new Application_Form_OwnerForm();
        $form->editprofileform();
        $this->view->form = $form;
        $request = $this->getRequest();
         
        $id = base64_decode($request->getParam('id'));
		
        
		$reg_list = new Application_Model_Owner;
        $regshow_list = $reg_list->ownerdetails($id);
		$details = array(
            'name' => $regshow_list[0]['Owner Name'],
            'contactno' => $regshow_list[0]['Contact Details'],
            'emailaddress' => $regshow_list[0]['Email'],
			//'password' => $regshow_list[0]['Password'],
			'address' => $regshow_list[0]['Address']
			
        );
		 
        $form->populate($details);
        if ($this->getRequest()->isPost()) {
           $request = $this->getRequest();
       // if ($this->form->isValidPartial($request->getPost())) {
                $editdata = $request->getPost();
				 
				 //$id = base64_decode($request->getParam('id'));
				 $datalist = $reg_list->updateownerdetails($editdata , $id);
				 
                if ($datalist) {
                    $this->_redirect('Owner');
                } else {
                    $this->view->assign('msg', 'Something wrong');
                    return;
                }

	            }
                }
			
	public function deleteOwnerdetailsAction() {

       

        $request = $this->getRequest();
		$id = base64_decode($request->getParam('id'));
       
		$show_list = new Application_Model_Owner;
		$record = $show_list->delownerinfo($id);
		
		//echo "<pre>";print_r( $record); die;
       
        $this->_redirect('Owner');
    }
 public function loginAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }
     
       $form = new Application_Form_OwnerForm();
        $form->loginform();
       $this->view->form = $form;
        $request = $this->getRequest();
       
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				
				$this->_redirect('owner/owner-information');
				
        }
    }
	 public function forgetpasswordAction() { //add role
       // if (!in_array($this->user->user_role, $this->rolearray)) {
          //  $this->_redirect('');
      //  }
         //$this->view->assign('success_message', $success_message);
		 
       $form = new Application_Form_OwnerForm();
        $form->forgetpasswordform();
       $this->view->form = $form;
        $request = $this->getRequest();
           
        if ($this->getRequest()->isPost()) {
           
            //if ($this->form->isValidPartial($request->getPost())) {
                $dataform = $request->getPost();
				//$this->_redirect('Owner');
				//if ($dataform['emailaddress'] != $Email) {
                    //$msg = "Please enter a correct email!";
                    //$this->view->assign('errorMessage', "Please enter a correct email!");
                    //return false;
					
						  
		 //echo"<pre>"; print_r($count_records);die;
		
				
                }
		
                //return false;
            }
		//$get_user_details = $this->owner_model->getActiveUser(null,$dataform['emailaddress']);
				
				//if ($get_user_details) {
               // $user_token = $this->_helper->Custom->generateUserToken();
				//$columns = array( 'reset_signtoken' => $user_token);
               // $update_token = $this->owner_model->updateownerstate($get_user_details['owner_id'], $columns);
				
				//$url =  $this->baseUrl('owner/resetpassword' . base64_encode($get_user_details['owner_id']) . "&token=" . base64_encode($user_token));
				
		
			
            
			
				//else {
                //$this->view->assign('success_message', "Password has been sent to registered email ID (if correct username was entered).");
                //return false;
            //}	
				
		//}
		
	public function resetpasswordAction()
	{
	$request = $this->getRequest();
		//$user_token = trim(base64_decode($request->getParam('token')));
        //$owner_id =   intval(base64_decode($request->getParam('owner')));
        $form = new Application_Form_OwnerForm();
        $form->resetpasswordform();

        $this->view->form = $form;
		if ($request->isPost()) {
        $dataform = $request->getPost();
	   
	
	}
	

		}
	   public function divyaPghomeAction() { 
        
       $form = new Application_Form_OwnerForm();
        $form->divyapghomeform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
			$dataform = $request->getPost();
		$cm_showlist = new Application_Model_Owner;
                $user_record = $cm_showlist->insertpgDetail($dataform);
                  if ($user_record) {
                    $this->_redirect('Owner/nikhil-pghome');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
	 }
		 }
		 public function nikhilPghomeAction() { 
          $form = new Application_Form_OwnerForm();
        $form->nikhilpghomeform();
        $this->view->form = $form;
        $request = $this->getRequest();
        if ($request->isPost()) {
			$dataform = $request->getPost();
		$cm_showlist = new Application_Model_Owner;
                $user_record = $cm_showlist->insertDetail($dataform);
                 if ($user_record) {
                    $this->_redirect('Owner');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
	 }
		 }
		
		
     
		 public function kaushikaPghomeAction() { 
		 
        $form = new Application_Form_OwnerForm();
        $form->Kaushikapghomeform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
			$dataform = $request->getPost();
		$cm_showlist = new Application_Model_Owner;
                $user_record = $cm_showlist->insertpgdata($dataform);
                  if ($user_record) {
                    $this->_redirect('Owner');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
	 }
		 }
		 public function shivaPghomeAction() {
			 $form= new Application_Form_OwnerForm();
			 $form->shivapghomeform();
             $this->view->form = $form;
              $request = $this->getRequest();
			 if ($request->isPost()) {
			$dataform = $request->getPost();
		      $cm_showlist = new Application_Model_Owner;
                $user = $cm_showlist->pginformation($dataform);
                  if ($user) {
                    $this->_redirect('Owner');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
	 }
		 } 
		 public function dakshPghomeAction() {
		 $form= new Application_Form_OwnerForm();
		 $form-> dakshpghomeform();
		 $this->view->form = $form;
		 $request = $this->getrequest();
		 if($request->isPost()) {
			 $data_form = $request->getPost();
			 $show_list=new Application_Model_Owner;
			 $show_record = $show_list->pggetdetails($data_form);
			 if($show_record){
				 $this->redirect('Owner');
			 }
			 else {
				 $this->view->assign('errorMessage', 'Error to insert data in the database.');
			 }
		 }
		 }
		 public function ownerInformationAction(){
	       
		
       // $this->view->assign('registration_details' );
    
	   //$this->view->assign('login_details' );
	}
	  public function pgOwnerAction() { 
        
       $form = new Application_Form_OwnerForm();
        $form->ownerpghomeform();
		
        $this->view->form = $form;
        $request = $this->getRequest();
       
        if ($request->isPost()) {
			$dataform = $request->getPost();
			

			 
		$cm_list = new Application_Model_Owner;
		
                $user_record = $cm_list->ownerDetail($dataform);
			
				
                  if ($user_record) {
                    $this->_redirect('image/image');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
				
	 }
		 }
		
		  
		  public function totalBookingAction(){
	     $cm_list = new Application_Model_Owner;
         $user_record = $cm_list->frontid();
		// print_r($user_record);
		 //die;
		$this->view->assign('customer_registrated_details',$user_record );
		
	}
	public function logout(){
		 $this->_redirect('Payingguest');
		}
		}
		
			   