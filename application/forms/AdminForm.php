<?php

use Zend\Captcha;
use Zend\Form\Form;
class Application_Form_AdminForm extends Zend_Form{

    public function init() {
        // Set the method for the display form to POST
    }
		public function addform() {

        
		// Set the method for the display form to POST
         $this->setMethod('post');
		
          $this->addElement('text', 'name', array(
            'required' => true,
            'autocomplete' => 'off',
			'style' => array('width:240px'),
            'class' => 'form-control',
			 'placeholder' => 'username',
            'filters' => array('StringTrim'),
            'maxlength' => '50',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'UserName field cannot be empty'
                        )
                    )),
                array('Regex', false, array(
                    '/^[a-zA-Z0-9~!#$*_:?()@., \/\- ]{0,}$/i', 
                    'messages' => array(
                        'regexNotMatch'=>'Name field has special characters.Please remove them and try again.'
                    )
                ))					
            ),
			
        ));
		
		
	
	$this->addElement('password', 'password', array(
            'label' => '',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
            'required' => true,
            'placeholder' => 'Password',
			'class' => 'form-control',
            'filters' => array('StringTrim'),
			'decorators'=> array('ViewHelper','Errors'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'Password cannot be empty'
                    )
                ))
			)
        ));
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));	
		}
}