<?php

use Zend\Captcha;
use Zend\Form\Form;
class Application_Form_RegisterForm extends Zend_Form{

    public function init() {
        // Set the method for the display form to POST
    }
		
	 public function addform() {
	   // Set the method for the display form to POST
        $this->setMethod('post');
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
		$this->addElement('text', 'emailaddress', array(
            'required' => true,
            'autocomplete' => 'off',
			'style' => array('width:240px'),
			'placeholder' => 'Email',
            'class' => 'form-control',
			'field'=>'email',
            'filters' => array('StringTrim'),
            'maxlength' => '200',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'Email field cannot be empty'
                        )
                    )),
                    array('Regex',
                        false,
                          array('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,5})+$/', 'messages'=>array(
'regexNotMatch'=>'Email field has special characters.Please remove them and try again.')))
              					
            ),
        ));
		 $this->addElement('text', 'mobilenumber', array(
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
		
		$this->addElement('password', 'repeatpassword', array(
            'label' => '',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
            'required' => true,
			'class' => 'form-control',
            'placeholder' => 'Repeat Password',
            'filters' => array('StringTrim'),
			'decorators'=> array('ViewHelper','Errors'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'Repeat Password cannot be empty'
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
	  public function loginform() {
		  $this->setMethod('post');
		  $this->addElement('text', 'emailaddress', array(
            'required' => true,
            'autocomplete' => 'off',
			'style' => array('width:240px'),
			'placeholder' => 'Email',
            'class' => 'form-control',
			'field'=>'email',
            'filters' => array('StringTrim'),
            'maxlength' => '200',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'Email field cannot be empty'
                        )
                    )),
                    array('Regex',
                        false,
                          array('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,5})+$/', 'messages'=>array(
'regexNotMatch'=>'Email field has special characters.Please remove them and try again.')))
              					
            ),
        ));
		 $this->addElement('text', 'mobilenumber', array(
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
	public function forgetpasswordform() {
		  $this->setMethod('post');
		  $this->addElement('text', 'emailaddress', array(
            'required' => true,
            'autocomplete' => 'off',
			'style' => array('width:240px'),
			'placeholder' => 'Email',
            'class' => 'form-control',
			'field'=>'email',
            'filters' => array('StringTrim'),
            'maxlength' => '200',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'Email field cannot be empty'
                        )
                    )),
                    array('Regex',
                        false,
                          array('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,5})+$/', 'messages'=>array(
'regexNotMatch'=>'Email field has special characters.Please remove them and try again.')))
              					
            ),
        ));
		 $this->addElement('text', 'mobilenumber', array(
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
		
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));	
		
	}
}
	