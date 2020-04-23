<?php

require_once 'Zend/Controller/Action.php';


class ImageController extends Zend_Controller_Action {


    public function init() {
		// $this->register_model = new Application_Model_Admin;
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_ImageForm();
	   
	   
	}
	public function imageAction(){
	       
		  $form = new Application_Form_ImageForm();
        $form->imageform();
        $this->view->form = $form;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
			$dataform = $request->getPost();
      
     
 if(isset($_POST['submit'])){
 
  $name = $_FILES['file']['name'];
  
  $target_dir = "uploads/";
  $target_file = $target_dir . basename($_FILES["file"]["name"]);

//Select file type
 $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

 // Valid file extensions
 $extensions_arr = array("jpg","jpeg","png","gif");
  $name=$_POST["filename"];
 //if(in_array($imageFileType,$extensions_arr) ) {
  move_uploaded_file($_FILES['file']['tmp_name'],$target_dir.$name);
 
  echo "old image name = ". $_FILES['file']['name']."<br/>";
  
  echo "new image name = " . $name.".".$imageFileType;
 
   //die;
  $cm_list = new Application_Model_Image;
		 //$dataform['name']= $name.$imageFileType;
		  $dataform['name']= $name.$imageFileType;
                $user_record = $cm_list->insertpgDetail($dataform);
			 //print_r($user_record);
		     //die;
				
                  if ($user_record) {
                    $this->_redirect('image/image1');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }

 
//$this->_redirect('owner/index');
 }
	}
}
public function image1Action(){
		 $cmshow_list = new Application_Model_Image;
         $user_record = $cmshow_list->side();
		  $this->view->assign('customerhome_details',$user_record );
		 
		
//print_r($user_record);
		  //die;
		 
		
	
		//$this->_redirect('/customer/booking-details');
	}
}