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
		//  $this->_redirect('/customer/booking-details');
	}
	public function dakshPghomedetailAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->viewid();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 //$this->_redirect('/customer/booking-details');
	}
	public function shivaPghomedetailsAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->getviewid();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		// $this->_redirect('/customer/booking-details');
	}
	public function nikhilPghomedetailAction() {
		$show_list = new Application_Model_Customer;
		$show_record = $show_list->showdetails();
		$this->view->assign('showhome_details',$show_record );
		//$this->_redirect('/customer/booking-details');
	}
	public function kaushikaPghomedetailAction() {
		$show_list = new Application_Model_Customer;
		$show_record = $show_list->getdetails();
		$this->view->assign('showhome_details',$show_record );
		//$this->_redirect('/customer/booking-details');
	}
	 public function bookingDetailsAction() { 
        
       $form = new Application_Form_CustomerForm();
        $form->bookingform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
			$dataform = $request->getPost();
		$cm_showlist = new Application_Model_Customer;
                $user_record = $cm_showlist->insertbookDetail($dataform);
                  if ($user_record) {
                    $this->_redirect('Owner');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
	 }
		 }
		 public function bookingsDetailsAction(){
		  $form = new Application_Form_CustomerForm();
        $form->bookingforms();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
			$dataform = $request->getPost();
		//  $this->_redirect('/customer/booking-details');
	}
		 }
	public function fulldetailsAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewidea();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		//  $this->_redirect('/customer/booking-details');
	}
	public function checkPgcustomerdetailsAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewidea1();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
	// public function check1PgcustomerdetailsAction(){
		 // $cmshow_list = new Application_Model_Customer;
         // $user_record = $cmshow_list->sideviewidea12();
	     // print_r($user_record);
		  // die;
		 // $this->view->assign('customerhome_details',$user_record );
		// }
		
		public function checkPgcustomerdetails15Action(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewideagoa();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
	public function checkPgcustomerdetails17Action(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewideapune();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
	public function checkPgcustomerdetails19Action(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewideamumbai();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
	public function checkPgcustomerdetails7Action(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->sideviewideachennai();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
	public function viewdetailsAction(){
		 $cmshow_list = new Application_Model_Customer;
         $user_record = $cmshow_list->getdata();
	     //print_r($user_record);
		  //die;
		 $this->view->assign('customerhome_details',$user_record );
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
}
	