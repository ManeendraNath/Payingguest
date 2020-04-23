<?php

require_once 'Zend/Controller/Action.php';


class AdmissionController extends Zend_Controller_Action {


    public function init() {
		// $this->register_model = new Application_Model_Register;
		 $this->_helper->layout->setLayout('layout');
       
		
       $this->form = new Application_Form_AdmissionForm();
	   
	}
	public function imageAction() { 
        
       $form = new Application_Form_AdmissionForm();
        if(isset($_POST['submit'])){ 
    // Include the database configuration file 
  
    // File upload configuration 
    $targetDir = "uploads/"; 
    $allowTypes = array('jpg','png','jpeg','gif'); 
     
    $statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = ''; 
    $fileNames = array_filter($_FILES['files']['name']); 
    if(!empty($fileNames)){ 
        foreach($_FILES['files']['name'] as $key=>$val){ 
            // File upload path 
            $fileName = basename($_FILES['files']['name'][$key]); 
            $targetFilePath = $targetDir . $fileName; 
             
            // Check whether file type is valid 
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION); 
            if(in_array($fileType, $allowTypes)){ 
                // Upload file to server 
                if(move_uploaded_file($_FILES["files"]["tmp_name"][$key], $targetFilePath)){ 
                    // Image db insert sql 
                    $insertValuesSQL .= "('".$fileName."', NOW()),"; 
                }else{ 
                    $errorUpload .= $_FILES['files']['name'][$key].' | '; 
                } 
            }else{ 
                $errorUploadType .= $_FILES['files']['name'][$key].' | '; 
            } 
        } 
         
        if(!empty($insertValuesSQL)){ 
            $insertValuesSQL = trim($insertValuesSQL, ','); 
          $cm_showlist = new Application_Model_Admission;
                $user_record = $cm_showlist->insertbookDetail($dataform);
                  if ($user_record) {
					   $errorUpload = !empty($errorUpload)?'Upload Error: '.trim($errorUpload, ' | '):''; 
                $errorUploadType = !empty($errorUploadType)?'File Type Error: '.trim($errorUploadType, ' | '):''; 
                $errorMsg = !empty($errorUpload)?'<br/>'.$errorUpload.'<br/>'.$errorUploadType:'<br/>'.$errorUploadType; 
                $statusMsg = "Files are uploaded successfully.".$errorMsg; 
                    $this->_redirect('Owner');
                } else {
                    $this->view->assign('errorMessage', 'Error to insert data.');
                }
       
           

		
	 }
		 }
		}
	}
	
		}