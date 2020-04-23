<?PHP

class Application_Form_ContactusForm extends Zend_Form {

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
            'filters' => array('StringTrim'),
            'maxlength' => '50',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'Name field cannot be empty'
                        )
                    )),
                array('Regex', false, array(
                    '/^[a-zA-Z0-9~!#$*_:?()@., \/\- ]{0,}$/i', 
                    'messages' => array(
                        'regexNotMatch'=>'FirstName field has special characters.Please remove them and try again.'
                    )
                ))					
            ),
			
        ));
		
		
		  $this->addElement('text', 'contactno', array(
            'required' => true,
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
                            'isEmpty' => 'ContactNo field cannot be empty'
                        )
                    )), 
               				
            )
        ));
 $this->addElement('text', 'address', array(
            'required' => true,
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
     $this->addElement('text', 'emailaddress', array(
            'required' => true,
            'autocomplete' => 'off',
            'class' => 'form-control',
			'style' => array('width:240px'),
			'field'=>'email',
            'filters' => array('StringTrim'),
            'maxlength' => '200',
            'decorators' => array('ViewHelper'),
            'validators' => array(
                array('notEmpty', true, array(
                        'messages' => array(
                            'isEmpty' => 'Emailaddress field cannot be empty'
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
            'label' => 'Submit',
        ));
		

	}
}