<?php

require_once 'Zend/Controller/Action.php';


class AdminController extends Zend_Controller_Action {


    public function init() {
		// $this->register_model = new Application_Model_Admin;
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_AdminForm();
	   
	}
	public function indexAction(){
	       
		
       $this->view->assign('admin_details' );
    
	   
	}
	public function registeredOwnerAction(){
	     $cm_list = new Application_Model_Admin;
         $user_record = $cm_list->frontviewid();
		// print_r($user_record);
		 //die;
		$this->view->assign('owner_registrated_details',$user_record );
		
	}
	public function editPgownerdetailsAction() { //edit role by id
       
	    $form = new Application_Form_OwnerForm();
        $form->editprofileform();
        $this->view->form = $form;
        $request = $this->getRequest();
         
        $id = base64_decode($request->getParam('id'));
		
        
		$reg_list = new Application_Model_Admin;
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
                    $this->_redirect('admin');
                } else {
                    $this->view->assign('msg', 'Something wrong');
                    return;
                }

	            }
		}
		public function deletePgownerdetailsAction() {

        $request = $this->getRequest();
		$id = base64_decode($request->getParam('id'));
       
		$show_list = new Application_Model_Admin;
		$record = $show_list->ownerinfo($id);
		
		//echo "<pre>";print_r( $record); die;
       
        $this->_redirect('/admin/index');
    }
      public function registeredCustomerAction(){
	     $cm_list = new Application_Model_Admin;
         $user_record = $cm_list->frontid();
		// print_r($user_record);
		 //die;
		$this->view->assign('customer_registrated_details',$user_record );
		
	}
	public function editPgcustomerdetailsAction() { //edit role by id
       
	    $form = new Application_Form_CustomerForm();
        $form->editbookingform();
        $this->view->form = $form;
        $request = $this->getRequest();
         
        $id = base64_decode($request->getParam('id'));
		
        
		$reg_list = new Application_Model_Admin;
        $regshow_list = $reg_list->customerviewdetails($id);
		$details = array(
		   'pg' => $regshow_list[0]['PG-Name'],
            'name' => $regshow_list[0]['username'],
			//'pg' => $regshow_list[0]['PG-Name'],
			 'gender' => $regshow_list[0]['gender'],
            'contactno' => $regshow_list[0]['contactno'],
            'email' => $regshow_list[0]['email'],
			//'password' => $regshow_list[0]['Password'],
			'address' => $regshow_list[0]['address']
			
        );
		 
        $form->populate($details);
        if ($this->getRequest()->isPost()) {
           $request = $this->getRequest();
       // if ($this->form->isValidPartial($request->getPost())) {
                $editdata = $request->getPost();
				 
				 //$id = base64_decode($request->getParam('id'));
				 $datalist = $reg_list->updatebookingdetails($editdata , $id);
				 
                if ($datalist) {
                    $this->_redirect('/admin/registered-customer');
                } else {
                    $this->view->assign('msg', 'Something wrong');
                    return;
                }

	            }
		}
		
		public function deletePgcustomerdetailsAction() {

       

        $request = $this->getRequest();
		$id = base64_decode($request->getParam('id'));
       
		$show_list = new Application_Model_Admin;
		$record = $show_list->delownerinfo($id);
		
		//echo "<pre>";print_r( $record); die;
       
        $this->_redirect('/admin/index');
    }
		
		
	public function pgHomedetailsAction(){
		 $cmshow_list = new Application_Model_Admin;
         $user_record = $cmshow_list->sideviewidea();
		
		   $show_record = $cmshow_list->showdetailsid();
			$show_record2 = $cmshow_list->getdetailsidea();
			$show_record3 = $cmshow_list->getviewidea();
			$show_record4 = $cmshow_list->viewidea();
	//	$this->view->assign('showhome_details',$show_record );
		
	
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerpg_details',$user_record );
		 $this->view->assign('showhome1_details',$show_record );
		 $this->view->assign('showhome2_details',$show_record2 );
		 $this->view->assign('showhome3_details',$show_record3 );
		//  $this->_redirect('/customer/booking-details');
		 $this->view->assign('showhome4_details',$show_record4 );
        
	     //print_r($user_record);
		  //die;
		 
		
		
       
		  //die;
		 
	}
	public function loginAction(){
	       
		
      $form = new Application_Form_AdminForm();
        $form->addform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
			$dataform = $request->getPost();
			//$this->_redirect('admin/index');
    
	 $user_name="Admin";
		 $pd="admin";
		 if($user_name==$dataform[name]&& $pd==$dataform[password]) {
			  
			  $this->_redirect('admin/index');
			  
		 }
		 else
		 { 
	           $this->view->assign('errorMessage', 'Username and password is incorrect');
                        return false;
			  $this->_redirect('admin/login');
		 }
			 
			 
	}
	
	  }
	}
			
