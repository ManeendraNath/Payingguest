<?php

use Zend\Captcha;
use Zend\Form\Form;
class Application_Form_CustomerForm extends Zend_Form{

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
		$gender = new Zend_Form_Element_Select('gender', array(
            'label' => 'Select Gender',
            'required' => true,
            'style' => array('width:240px'),
            'class' => 'form-control',
            'multiOptions' => array(
                '0' => '--Select--',
                'Male' => 'Male',
                'Female' => 'Female',
				'Others' => 'Others'
            ),
            'decorators' => Array(
                'ViewHelper', 'Errors'
            ),
        ));
        $validation = new Zend_Validate_NotEmpty();
        $validation->setType($validation->getType() | Zend_Validate_NotEmpty::INTEGER | Zend_Validate_NotEmpty::ZERO);
        $gender->addValidators(array($validation));
        $this->addElement($gender);
		
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
		$this->addElement('text', 'email', array(
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
		
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));		
	 }
	 public function editbookingform() {
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
		$gender = new Zend_Form_Element_Select('gender', array(
            'label' => 'Select Gender',
            'required' => true,
            'style' => array('width:240px'),
            'class' => 'form-control',
            'multiOptions' => array(
                '0' => '--Select--',
                'Male' => 'Male',
                'Female' => 'Female',
				'Others' => 'Others'
            ),
            'decorators' => Array(
                'ViewHelper', 'Errors'
            ),
        ));
        $validation = new Zend_Validate_NotEmpty();
        $validation->setType($validation->getType() | Zend_Validate_NotEmpty::INTEGER | Zend_Validate_NotEmpty::ZERO);
        $gender->addValidators(array($validation));
        $this->addElement($gender);
		
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
		$this->addElement('text', 'email', array(
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
		
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));		
	 }
	 	 public function bookingforms() {
	   // Set the method for the display form to POST
        $this->setMethod('post');
		 $location = new Zend_Form_Element_Select('location', array(
            'label' => 'Select location',
            'required' => true,
            'style' => array('width:240px'),
            'class' => 'form-control',
            'multiOptions' => array(
                '0' => '--Select--',
                'Male' => 'Delhi',
                'Female' => 'Goa',
				'Others' => 'Mumbai'
				
            ),
            'decorators' => Array(
                'ViewHelper', 'Errors'
            ),
        ));
        $validation = new Zend_Validate_NotEmpty();
        $validation->setType($validation->getType() | Zend_Validate_NotEmpty::INTEGER | Zend_Validate_NotEmpty::ZERO);
        $location->addValidators(array($validation));
        $this->addElement($location);
	
		$this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));	
}
}