<?php

use Zend\Captcha;
use Zend\Form\Form;
class Application_Form_Authnew extends Zend_Form{

    public function init(){
	
	   // Set the method for the display form to POST
        $this->setMethod('post');

        // Add an email element
        $this->addElement('text', 'user_name', array(
            'label' => '',
			'autocomplete' => 'off',
            'required' => true,
            'placeholder' => 'username',
            'filters' => array('StringTrim'),
			'decorators' => array('ViewHelper','Errors'),
            'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
                        'isEmpty' => 'Username cannot be empty'
                    )
                ))
			)			
		));

		$this->addElement('password', 'user_password', array(
            'label' => '',
			'autocomplete' => 'off',
            'required' => true,
            'placeholder' => 'password',
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

        $this->addElement('text', 'vercode', array(
			'required' => true,
			'class' => 'form-control captchain',
			'placeholder' => 'Enter Verification Code.',
			'autocomplete' => 'off',
			'decorators'=> array('ViewHelper','Errors',),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 20))
			)
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
			'decorators'=>Array(
			'ViewHelper','Errors',)
        ));		
	}
	
	public function changePasswordForm(){

		// Set the method for the display form to POST
        $this->setMethod('post');

		$this->addElement('password', 'old_password', array(
			'label' => '',
			'required' => true,
			'placeholder' => 'Enter old Password',			
			'filters' => array('StringTrim'),
			'size' =>'37',
			'id' =>'oldpassword',
			'class' => 'form-control',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
			'decorators' => array('ViewHelper','Errors',),
			'validators' => array(
			array('notEmpty', true, array(
				'messages' => array('isEmpty' => 'Oldpassword field can\'t be empty')
			))),
			
		));
			
		$this->addElement('password', 'new_password', array(
			'label' => '',
			'required' => true,
			'filters' => array('StringTrim'),
			'placeholder' => 'Enter New Password',			
			'size' =>'37',
			'id' =>'newpassword',
			'class' => 'form-control',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
				'decorators' => array('ViewHelper','Errors',),
			)
		);

        $this->addElement('password', 'confirm_new_password', array(
            'label' => '',
            'required' => true,
            'filters' => array('StringTrim'),
			'size' =>'37',
			'id' =>'conformnewpassword',
			'placeholder' => 'Confirm old Password',			
			'class' => 'form-control',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
			'decorators' => array('ViewHelper','Errors',),
		));

        $this->addElement('text', 'vercode', array(
			'required' => true,
			'class' => 'form-control captchain',
			'placeholder' => 'Enter Verification Code.',
			'autocomplete' => 'off',
			'decorators'=> array('ViewHelper','Errors',),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 20))
			)
        ));
		

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Submit',
        ));

        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
	}
	
	
	public function forgotPasswordForm(){

		// Set the method for the display form to POST
		$this->setMethod('post');

        $this->addElement('text', 'user_name', array(
            'label' => '',
			'autocomplete' => 'off',
            'required' => true,
			'placeholder' => 'username',
            'filters' => array('StringTrim'),
			'decorators' => array('ViewHelper','Errors'),
			 'validators' => array(
			    array('notEmpty', true, array(
                    'messages' => array(
					'isEmpty' => 'User name can\'t be empty'
                    )
                )),
			)
        ));

        $this->addElement('text', 'vercode', array(
			'required' => true,
			'class' => 'form-control captchain',
			'placeholder' => 'Enter Verification Code.',
			'autocomplete' => 'off',
			'decorators'=> array('ViewHelper','Errors'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 20))
			)
        ));
		
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Submit',
        ));
	}
	
	
	public function resetPasswordForm(){

		$this->setMethod('post');	
			
		$this->addElement('password', 'new_password', array(
			'label' => '',
			'required' => true,
			'filters' => array('StringTrim'),
			'placeholder' => 'Enter New Password',			
			'size' =>'37',
			'id' =>'newpassword',
			'class' => 'form-control',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
				'decorators' => array('ViewHelper','Errors',),
			)
		);

        $this->addElement('password', 'confirm_new_password', array(
            'label' => '',
            'required' => true,
            'filters' => array('StringTrim'),
			'size' =>'37',
			'id' =>'conformnewpassword',
			'placeholder' => 'Confirm New Password',			
			'class' => 'form-control',
			'autocomplete' => 'off',
			'style' => array('width:240px'),
			'decorators' => array('ViewHelper','Errors',),
		));

        $this->addElement('text', 'vercode', array(
			'required' => true,
			'class' => 'form-control captchain',
			'placeholder' => 'Enter Verification Code.',
			'autocomplete' => 'off',
			'decorators'=> array('ViewHelper','Errors'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 20))
			)
        ));

		$this->addElement('hidden', 'token', array(
			'id' =>'token',
			'autocomplete' => 'off',
			'decorators' => array('ViewHelper')
        ));

        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Submit',
        ));

        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));

	}
	
	public function isValidPartial($form_data){

		//call the parent method for basic form validation
        $isValid = parent::isValidPartial($form_data);
 
        if($isValid){
			if(strlen($form_data['new_password']) <= 7){
					$this->newpassword->setErrors(array('Password field should be minimum 8 characters.'));
					$isValid = false;
			}
			if(!($form_data['new_password'] == $form_data['confirm_new_password'])){
				 $this->conformnewpassword->setErrors(array('New Password and Confirm password should be same.'));
                $isValid = false;
            }	
        }
        return $isValid;
    }
}