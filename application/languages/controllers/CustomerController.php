<?php

require_once 'Zend/Controller/Action.php';


class CustomerController extends Zend_Controller_Action {


    public function init() {
		// $this->register_model = new Application_Model_Register;
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_CustomerForm();
	   
	}
	
	public function indexAction(){
	       
		
       $this->view->assign('customer_details' );
    
	   
	}
	
	public function divyaPghomedetailAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewid();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 // $this->_redirect('/customer/booking-details');
	}
	public function dakshPghomedetailAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->viewid();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		
	}
	public function shivaPghomedetailsAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->getviewid();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		
	}
	public function nikhilPghomedetailAction() {
		$show_list = new Application_Model_Customer;
		$show_record = $show_list->showdetails();
		$this->view->assign('showhome_details',$show_record );
		
	}
	public function kaushikaPghomedetailAction() {
		$show_list = new Application_Model_Customer;
		$show_record = $show_list->getdetails();
		$this->view->assign('showhome_details',$show_record );
		
	}
	 public function bookingDetailsAction() { 
        
       $form = new Application_Form_CustomerForm();
         $form->bookingform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
         
        if ($request->isPost()) {
			$dataform = $request->getPost();
		$cm_showlist = new Application_Model_Customer;
                $users_record = $cm_showlist->insertbookDetail($dataform);
				
				
                  if ($users_record) {
					  
					//$this->view->assign('success_message', 'Booking Successfully Done');
					 //return true;
                    $this->_redirect('Customer');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
	 }
		 }
		 }
	
	  
	