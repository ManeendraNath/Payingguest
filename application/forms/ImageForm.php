<?php

use Zend\Captcha;
use Zend\Form\Form;
class Application_Form_ImageForm extends Zend_Form{

    public function init() {
        // Set the method for the display form to POST
    }
		public function imageform() {

        
		// Set the method for the display form to POST
         $this->setMethod('post');
		
		
          $this->addElement('file', 'file', array(
            'label' => '',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
            'required' => true,
            
			
            
            
        ));
		 $this->addElement('text', 'filename', array(
            'label' => '',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
            'required' => true,
            
			
            
            
        ));
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));	
		}
}