<?php

class Application_Form_User extends Zend_Form{

	public function init(){
	// Set the method for the display form to POST
	}

	public function addform($states_list = null, $ministry_list = null, $user_roles = null){

		// Set the method for the display form to POST
		$this->setMethod('post');

		//getting here the Ministry Name from the USer Model and display against to the Minisry User Role End here
		$role_form_element = new Zend_Form_Element_Select('user_role',array( 
            'label' => '',
            'required' => true,
            'class' => 'form-control',
            'multiOptions' => array(
				'' => '--Select--',       
			),			
		    'decorators' => array('ViewHelper'),
			'validators' => array(
				'options' => 
				array('notEmpty', true, array(
					'messages' => array(
						'isEmpty' => 'Please select a role'
					)
				))
				
			)			
        ));
		//$required = new Zend_Validate_NotEmpty();
		//$required->setType($required->getType() | Zend_Validate_NotEmpty::INTEGER | Zend_Validate_NotEmpty::ZERO);
		//$role_form_element->addValidators(array($required));

		if($user_roles){
			foreach($user_roles as $key => $value){
				$name = $value['role_name'];
				$role_form_element->addMultiOption($value['role_id'], $name);
			}
		}
        $this->addElement($role_form_element);	
		

		$this->addElement('text', 'user_name', array(
				'required' => true,
				'autocomplete' => 'off',
				'class' => 'form-control',
				'filters' => array('StringTrim'),
				'maxlength' => '50',
				'decorators' => array('ViewHelper'),
				'validators' => array(
					array('notEmpty', true, array(
						'messages' => array(
							'isEmpty' => 'Username field cannot be empty'
						)
					)),
					array('Regex',false,array('/^[a-z][a-z0-9._, \'-]{0,}$/i', 'messages' => array(
					'regexNotMatch' => 'Username field has special characters.Please remove them and try again'
					)))
				)
		));
			
		$this->addElement('text','user_first_name', array(
				'required' => true,
				'autocomplete' => 'off',
				'class' => 'form-control',
				'filters' => array('StringTrim'),
				'maxlength' => '30',
				'decorators' => array('ViewHelper'),
				'validators' => array(
					array('notEmpty', true, array(
					'messages' => array(
						'isEmpty' => 'First name field cannot be empty'
					)
					)),
					array('Regex',false,array('/^[a-z][a-z0-9., \'-]{0,}$/i', 'messages' => array(
					'regexNotMatch'=>'First Name field has special characters.Please remove them and try again.')))
				)
		));

		$this->addElement('text', 'user_last_name', array(            
            'required' => true,
			'autocomplete' => 'off',
            'filters' => array('StringTrim'),
			'class' => 'form-control',
			'maxlength'  => '30',
			'decorators' => array('ViewHelper'),
			'validators' => array(
				array('notEmpty', true, array(
					'messages' => array(
						'isEmpty' => 'Last Name field cannot be empty'
					)
				)),
				array('Regex',false,array('/^[a-z][a-z0-9., \'-]{0,}$/i', 'messages'=>array(
					'regexNotMatch' => 'Last name field has special characters.Please remove them and try again.')))
				)
		));

		$this->addElement('text', 'user_designation', array( 
			'label' => '',
			'autocomplete' => 'off',
            'required' => false,
            'filters' => array('StringTrim'),
			'maxlength' => '100',
			'class' => 'form-control',
			'decorators' => array('ViewHelper'),
			'validators' => array(
				array('Regex',false,array(TEXT_FIELD_VALIDATION, 'messages' => array(
					'regexNotMatch' => 'Designation field has some special characters.Please remove them and try again'
				)))
			)	
		));

		$this->addElement('text', 'user_mobile', array(           
            'required' => true,
			'autocomplete' => 'off',
            'filters' => array('StringTrim'),
			'class' => 'form-control',
			'maxlength' => '12',
			'decorators'=> array('ViewHelper'),
			'validators' => array(
                array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'Mobile Number field cannot be empty'
				))),
                array('StringLength', false, array(10, 10, 'messages' => array(
					'stringLengthTooShort' => "Mobile Number field is invalid"
				))),
            )
        ));

		$this->addElement('text', 'user_email', array( 
			'label' => '',
			'autocomplete' => 'off',
            'required' => true,
            'filters' => array('StringTrim'),
			'maxlength' => '50',
			'class' => 'form-control',
			'decorators' => array('ViewHelper'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'E-mail Address field cannot be empty'
				))),
                'EmailAddress',
			)));
		
		$this->addElement('textarea', 'user_address', array( 
            'filters' => array('StringTrim'),			
			'class' => 'form-control',			
			'decorators' => array('ViewHelper'),
		));

		//this field is use for the add states into the form as multioption
        $state_form_element = new Zend_Form_Element_Select('state_name',array(       
			'label' => '',
			'class' => 'form-control',
			'multiOptions' => array(
				'' => '--Select State--',          
			),
            'decorators' => array('ViewHelper')
        ));
		
		if($states_list){
			foreach($states_list as $key => $value){
				$state_name = $value['state_name']; 
				$state_form_element->addMultiOption($value['state_code'], $state_name);        
			}
		}
        $this->addElement($state_form_element);

		//getting here the Ministry Name from the USer Model and display against to the Minisry User Role 
		$ministry_form_element = new Zend_Form_Element_Select('ministry_name',array(
            'label' => '',
			'class' => 'form-control',
            'multiOptions' => array(
				'' => '--Select Ministry--',          
			),  
            'decorators' => array('ViewHelper')
        ));

		if($ministry_list){
			foreach($ministry_list as $key => $value){
				$ministry = $value['ministry_name'];  
				$ministry_form_element->addMultiOption($value['ministry_id'], $ministry);        
			}
		}
        $this->addElement($ministry_form_element);
		
		// Add a captcha
        $this->addElement('text', 'vercode', array(
            //'required' => true,
			'class' => 'form-control captchain',
			'placeholder' => 'Enter Verification Code.',
			'autocomplete' => 'off',
			'decorators' => array('ViewHelper'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
					'isEmpty' => 'Captcha field cannot be empty'
				)))				
			)
        ));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Submit',
        ));

        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
	}
	
	public function assignScheme($scheme_owner = null, $scheme_list = null){

		// Set the method for the display form to POST
		$this->setMethod('post');

		//getting here the Ministry Name from the USer Model and display against to the Minisry User Role End here
		/*$scheme_owner_list = new Zend_Form_Element_Select('scheme_owner',array( 
            'label' => '',
            'required' => true,
            'class' => 'form-control',
            'multiOptions' => array(
				'' => '--Select--',       
			),			
		    'decorators' => array('ViewHelper'),
			'validators' => array(
				'options' => 
				array('notEmpty', true, array(
					'messages' => array(
						'isEmpty' => 'Please select a Scheme owner'
					)
				))
			)
        ));

		if($scheme_owner){
			foreach($scheme_owner as $key => $value){
				$name = $value['role_name'];
				$scheme_owner_list->addMultiOption($value['user_id'], $value['user_name']);
			}
		}
        $this->addElement($scheme_owner_list);*/

		$this->addElement('text', 'scheme_owner', array( 
			'label' => '',
			'autocomplete' => 'off',
			'readonly' => true,
            'required' => true,
            'filters' => array('StringTrim'),
			'maxlength' => '50',
			'value' => $scheme_owner['user_name'],
			'class' => 'form-control',
			'decorators' => array('ViewHelper'),
		));

		$this->addElement('hidden', 'scheme_owner_id', array( 
			'label' => '',
			'autocomplete' => 'off',
			'readonly' => true,
            'required' => true,
            'filters' => array('StringTrim'),
			'maxlength' => '50',
			'value' => $scheme_owner['user_id'],
			'class' => 'form-control',
			'decorators' => array('ViewHelper'),
		));
		
		$scheme_list_checkbox = new Zend_Form_Element_MultiCheckbox('scheme_list',array( 			
			'label' => '',
			'required' => false,
			'class' => 'scheme_list',
			'disableLoadDefaultDecorators' => true,
			'separator' => '<tr><td>',
			'registerInArrayValidator' => true,
			'multiOptions' => array(),
			'decorators' => array('ViewHelper','Errors')
		));
		$requiredtype = new Zend_Validate_NotEmpty ();
		$requiredtype->setType($requiredtype->getType() | Zend_Validate_NotEmpty::INTEGER | Zend_Validate_NotEmpty::ZERO);
		$scheme_list_checkbox->addValidators(array($requiredtype));
		
		if($scheme_list){
			foreach($scheme_list as $key => $value){
				$scheme_list_checkbox->addMultiOption($value['scheme_id'], $value['scheme_name']);
			}
		}		
		$this->addElement($scheme_list_checkbox);
		
		// Add a captcha
        $this->addElement('text', 'vercode', array(
            'required' => true,
			'class' => 'form-control captchain',
			'placeholder' => 'Enter Verification Code.',
			'autocomplete' => 'off',
			'decorators' => array('ViewHelper'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
					'isEmpty' => 'Captcha field cannot be empty'
				)))				
			)
        ));		
	}	
}