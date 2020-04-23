<?php

use Zend\Captcha;
use Zend\Form\Form;
class Application_Form_AdmissionForm extends Zend_Form{

    public function init() {
        // Set the method for the display form to POST
    }
	 public function bookingform() {
	   // Set the method for the display form to POST
        $this->setMethod('post');
		 
		 $this->addElement('text', 'pg', array(
            'label' => '',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
            'required' => true,
            'placeholder' => 'PG Name',
			'class' => 'form-control',
            'filters' => array('StringTrim'),
			'decorators' => array('ViewHelper','Errors'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'Name cannot be empty'
                    )
                ))
			)			
		));
		
		 $this->addElement('text', 'name', array(
            'label' => '',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
            'required' => true,
            'placeholder' => 'Name',
			'class' => 'form-control',
            'filters' => array('StringTrim'),
			'decorators' => array('ViewHelper','Errors'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'Name cannot be empty'
                    )
                ))
			)			
		));
		
		
		 $this->addElement('text', 'contactno', array(
            'required' => true,
			'placeholder' => 'MobileNumber',
            'autocomplete' => 'off',
			'style' => array('width:240px'),
            'class' => 'form-control',
			'type'=>'number',
            'filters' => array('StringTrim'),
            'maxlength' => '20',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'Contact field cannot be empty'
                        )
                    )), 
               				
            )
        ));
		$this->addElement('text', 'address', array(
            'required' => true,
			'placeholder' => 'Address',
            'autocomplete' => 'off',
			'style' => array('width:240px'),
            'class' => 'form-control',
            'filters' => array('StringTrim'),
            'maxlength' => '200',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'address field cannot be empty'
                        )
                    )),
                array('Regex', false, array(
                    '/^[a-zA-Z0-9~!#$*_:?()@., \/\- ]{0,}$/i', 
                    'messages' => array(
                        'regexNotMatch'=>'Address field has special characters.Please remove them and try again.'
                    )
                ))					
            ),
			
        ));
		
		
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));		
	 }
}
	
