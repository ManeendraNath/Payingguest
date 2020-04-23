<?php

require_once 'Zend/Controller/Action.php';


class AboutusController extends Zend_Controller_Action {


    public function init() {
		 $this->register_model = new Application_Model_Register;
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_AboutusForm();
	   
	}
	
	public function indexAction(){
	       
		
       $this->view->assign('aboutus_details' );
    
	   
	}
	}