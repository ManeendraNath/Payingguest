<?php

require_once 'Zend/Session.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Zend/Validate.php';

class UserController extends Zend_Controller_Action {

    protected $sendgrid_mail;
    protected $user;
    protected $user_form;
    protected $user_model;
    protected $auth_form = '';
    protected $auth_model;
    protected $admin_role = array(1);

    public function init() {

        $this->user = new Zend_Session_Namespace('user_session');
        $this->captcha_session_id = new Zend_Session_Namespace('captcha_session');

        $this->user_form = new Application_Form_User;
        $this->auth_form = new Application_Form_Authnew;
        $this->auth_model = new Application_Model_Authnew;
        $this->user_model = new Application_Model_User;
        $this->role_model = new Application_Model_Role;
        $this->report_model = new Application_Model_Schemereportnew;

        if ($this->user->user_role == 1 || $this->user->user_role == 3 || $this->user->user_role == 6 || $this->user->user_role == 26) {
            $this->_helper->layout->setLayout('layout_admin');
        } else {
            $this->_helper->layout->setLayout('layout');
        }
        $this->sendgrid_mail = $this->_helper->Custom->sendGridConfig();

        if ($this->_helper->Functions->validateResponseMethod() == FALSE) {
            $this->_redirect('');
        }
    }

    public function addAction() {

        if (!in_array($this->user->user_role, $this->admin_role)) {
            $this->_redirect('');
        }

        $this->captcha_session_id = Zend_Session::getId();

        $request = $this->getRequest();
        $user_ip = $request->getServer('REMOTE_ADDR');

        // Add User Form
        $this->user_form->addform($this->user_model->getStatesList(), $this->user_model->getMinistryList(null), $this->user_model->listUserRoles());
        $this->view->form = $this->user_form;

        if ($this->getRequest()->isPost()) {
            if ($this->user_form->isValidPartial($request->getPost())) {
                $form_data = $request->getPost();
                if(IS_CAPTCHA_ENABLE=='Y')
				{
                if ($form_data['vercode'] != $_SESSION["vercode"]) {
                    $this->view->assign('error_message', INCORRECT_CAPTCHA);
                    return false;
                }
				}
                if ($form_data['sessionCheck'] != $this->captcha_session_id) {
                    $this->view->assign('error_message', CSRF_ATTACK);
                    return false;
                }
                if ($form_data['user_role'] == 2 && trim($form_data['state_name']) == '') {
                    $this->view->assign('error_message', 'Please select state for the state officials');
                    return false;
                }

                $alphabet_numeric_string = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
                $pass = array(); //remember to declare $pass as an array
                $alphaLength = strlen($alphabet_numeric_string) - 1; //put the length -1 in cache
                for ($i = 0; $i < 8; $i++) {
                    $n = rand(0, $alphaLength);
                    $pass[] = $alphabet_numeric_string[$n];
                }
                $randomPassword = implode($pass); //turn the array into a string
                $form_data['user_password'] = $randomPassword;

                $count_records = $this->user_model->checkUser($form_data['user_name']);

                if (($count_records == 0)) {

                    $user_token = $this->_helper->Custom->generateUserToken();

                    $form_data['reset_login_token'] = $user_token;
                    $form_data['ip_address'] = $user_ip;
                    $form_data['updated_by'] = $this->user->user_id;

                    $user_record_id = $this->user_model->createUser($form_data);

                    $this->user_model->getUserDetails($user_record_id);

                    /*                     * *** Send Mail block **** */
                    $weblink = WEB_LINK;
                    $mail_subject = MAIL_SUBJECT;
                    $mail_body = MESSAGE_BODY;
                    $mail_body = str_replace('{user_name}', $form_data['user_name'], $mail_body);
                    $mail_body = str_replace('{fname}', ucfirst($form_data['user_first_name']), $mail_body);
                    $mail_body = str_replace('{user_password}', $form_data['user_password'], $mail_body);
                    $mail_body = str_replace('{web_link}', $weblink, $mail_body);
                    $mail_to = $form_data['user_email'];
                    $mail_from = MAIL_FROM;
                    $mail_to_name = $mail_from_name = MAIL_NAME;

                    $this->send_mail($mail_subject, $mail_body, $mail_to, $mail_from, $mail_to_name, $mail_from_name);

                    $this->_redirect('/user/user-view?message=add');
                    /*                     * *** Send Mail block END **** */
                } else {
                    if ($countdata) {
                        $this->view->assign('error_message', 'Username already exists in the Database.');
                        return;
                    }
                }
            }
        }
    }

    public function userViewAction() {

        $request = $this->getRequest();

        if ($this->user->user_name == '') {
            $this->_redirect('');
        }
        if (!in_array($this->user->user_role, $this->admin_role)) {
            $this->_redirect('');
        }

        $request = $this->getRequest();

        if ($request->getParam('message') == 'add') {
            $this->view->assign('success_message', RECORD_INSERTED);
        } elseif ($request->getParam('message') == 'edit' || $request->getParam('message') == 'update') {
            $this->view->assign('success_message', RECORD_UPDATED);
        } elseif ($request->getParam('message') == "sentmail") {
            $this->view->assign('success_message', 'Password has been sent to registered email ID (if correct username was entered)');
        }

        if (isset($start)) {
            // This variable is set to zero for the first page
            $start = 0;
        } else {
            $start = $request->getParam('start');
			if($start>START_COUNT)
				{
				  $this->_redirect('');
				}
        }

        $page = 0;
        $limit = 1000;

        $user_list = $this->user_model->getUsersList($start, $limit);
        $user_count = count($user_list);

        $this->view->assign('user_list', $user_list);
        $this->view->assign('user_count', $user_count);
    }

    public function userInactiveAction() {

        $this->captcha_session_id = Zend_Session::getId();
        $request = $this->getRequest();

//        if ($this->user->user_name == '') {
//            $this->_redirect('');
//        }
        if (!in_array($this->user->user_role, $this->admin_role)) {
            $this->_redirect('');
        }

        if ($this->getRequest()->isPost()) {
            $form_data = $request->getPost();

//            foreach ($form_data['user_id'] as $user_id) {
//                $columns = array('user_status' => $form_data['user_status']);
//                $this->auth_model->updateUserStatus(array_keys($form_data['user_id']), $columns);
//            }
            //echo "<pre>";print_r($form_data);echo "</pre>";die;
            foreach ($form_data['user_id'] as $keys => $val) {
                $data = array(
                    'user_status' => $form_data['user_status'],
                    'login_attempt' => 0,
                    'updated_by' => $this->user->user_id,
                    'updated' => date('Y-m-d H:i:s'),
                    'ip_address' => $request->getServer('REMOTE_ADDR'),
                );
                $this->user_model->updateUserStatus($data, $keys);
                unset($data);
            }
            $this->_redirect('/user/user-view?message=update');
        }
    }

    public function editUserAction() {

        $request = $this->getRequest();
        $user_id = base64_decode($request->getParam('id'));
        if ($user_id < 1) {
            $this->_redirect('');
        }
        $user_ip = $request->getServer('REMOTE_ADDR');

        $this->captcha_session_id = Zend_Session::getId();

        if ($this->user->user_name == '' || !($user_id)) {
            $this->_redirect('');
        }
        if (!in_array($this->user->user_role, $this->admin_role)) {
            $this->_redirect('');
        }

        $user_info = $this->user_model->getUserDetails($user_id);

        $this->user_form->addform($this->user_model->getStatesList(), $this->user_model->getMinistryList(null), $this->user_model->listUserRoles());
        $this->user_form->populate($user_info);
        $this->view->form = $this->user_form;

        $this->view->assign('user_info', $user_info);

        if ($this->getRequest()->isPost()) {

            $form_data = $request->getPost();
            if(IS_CAPTCHA_ENABLE=='Y')
			{
            if ($form_data['vercode'] != $_SESSION["vercode"]) {
                $this->view->assign('error_message', INCORRECT_CAPTCHA);
                return false;
            }
			}
            if ($form_data['sessionCheck'] != $this->captcha_session_id) {
                $this->view->assign('error_message', CSRF_ATTACK);
                return false;
            }

            $form_data['ip_address'] = $user_ip;
            $form_data['updated_by'] = $this->user->user_id;
            $count_record = $this->user_model->checkUser($form_data['user_name']);

            if (($count_record == 1)) {
                $this->user_model->editUser($form_data,$user_id);

                $this->_redirect('/user/user-view?message=update');
            } else {
                if ($countdata) {
                    $this->view->assign('error_message', 'Username already exists in the database.');
                    return;
                }
            }
        }
    }

    public function editUserProfileAction() {

        $this->captcha_session_id = Zend_Session::getId();

        if ($this->user->user_role == '') {
            $this->_redirect('');
        }
//        if (!in_array($this->user->user_role, $this->admin_role)) {
//            $this->_redirect('');
//        }

        $request = $this->getRequest();
        $user_ip = $request->getServer('REMOTE_ADDR');

        if ($request->getParam('message') == 'add') {
            $this->view->assign('success_message', RECORD_INSERTED);
        }
        if ($request->getParam('message') == 'edit' || $request->getParam('message') == 'update') {
            $this->view->assign('success_message', RECORD_UPDATED);
        }

        $user_info = $this->user_model->getUserDetails($this->user->user_id);

        $this->user_form->addform();
        $this->user_form->populate($user_info);
        $this->view->form = $this->user_form;

        if ($this->getRequest()->isPost()) {

			if ($this->user_form->isValidPartial($request->getPost())) {
				$form_data = $request->getPost();
				if(IS_CAPTCHA_ENABLE=='Y')
				{
				if ($form_data['vercode'] != $_SESSION["vercode"]) {
					$this->view->assign('error_message', INCORRECT_CAPTCHA);
					return false;
				}
				}
				if ($form_data['sessionCheck'] != $this->captcha_session_id) {
					$this->view->assign('error_message', CSRF_ATTACK);
					return false;
				}

				$form_data['ip_address'] = $user_ip;
				$form_data['updated_by'] = $this->user->user_id;
				//$count_record = $this->user_model->checkUser($form_data['user_name']);

				$this->user_model->editUser($form_data, $this->user->user_id);
				$this->_redirect('/user/user-profile?message=update');
			}
        }
    }

    public function userProfileAction() {

        $request = $this->getRequest();

        if ($request->getParam('message') == 'add') {
            $this->view->assign('success_message', RECORD_INSERTED);
        }
        if ($request->getParam('message') == 'edit' || $request->getParam('message') == 'update') {
            $this->view->assign('success_message', RECORD_UPDATED);
        }

        if ($this->user->user_id == '') {
            $this->_redirect('');
        }

        $user_info = $this->user_model->getUserDetails($this->user->user_id);
        $this->view->assign('user_info', $user_info);
    }

    public function changeUserPasswordAction() {
		
        $log_password=array();
        $this->var_session_id = Zend_Session::getId();

        $request = $this->getRequest();

        if ($this->user->user_name == '') {
            $this->_redirect('');
        }

        $form = $this->auth_form;
        $form->changePasswordForm();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {

            $form_data = $request->getPost();
           if(IS_CAPTCHA_ENABLE=='Y')
			{
            if ($form_data['vercode'] != $_SESSION["vercode"]) {
                $this->view->assign('error_message', INCORRECT_CAPTCHA);
                return false;
            }
			}
            if ($form_data['sessionCheck'] != $this->var_session_id) {
                $this->view->assign('error_message', CSRF_ATTACK);
                return false;
            }

            if (strlen($form_data['new_password']) < 8) {
                $this->view->assign('error_message', "New Password should contain minimum 8 characters");
                return false;
            }
            if (strlen($form_data['confirm_new_password']) < 8) {
                $this->view->assign('error_message', "Confirm Password should contain minimum 8 characters");
                return false;
            }

            if (strlen($form_data['new_password'] != $form_data['confirm_new_password'])) {
                $this->view->assign('error_message', "New Password and Confirm password does not match");
                return false;
            }

            $get_user_details = $this->user_model->getUserDetails($this->user->user_id);
            $get_user_details_log = $this->user_model->getUserDetailsFromlog($this->user->user_id);

			$log_password = '';
			foreach($get_user_details_log as $key => $val){
				$log_password[] = $val['user_password'];
			}
            //If User Exists
            if ($get_user_details) {

                $old_enc_pwd = $this->_helper->Custom->findMd5Value($form_data['old_password']);
                $new_enc_pwd = $this->_helper->Custom->findMd5Value($form_data['new_password']);
                //Authenticate User
                $authenticate_user = $this->auth_model->authenticateUser($get_user_details['user_name'], $old_enc_pwd);

                //Update User Password & Status
                if ($authenticate_user) {
					
					array_push($log_password,$get_user_details['user_password']);//Add last password to Array
					
					if (in_array($new_enc_pwd, $log_password)){
						$this->view->assign('error_message', "You are not allowed to use last three password; please enter new password");
						return false;
					}
                    /*if ($get_user_details['user_password'] == trim($new_enc_pwd)) {
                        $this->view->assign('error_message', "Password is already used before; please enter new password");
                        return false;
                    }*/
                    $get_change_password_status = $this->auth_model->changeUserPassword($new_enc_pwd, $this->user->user_id);
                    $this->view->assign('success_message', RECORD_UPDATED);
                } else {
                    $this->view->assign('error_message', "Unable to Authenticate User");
                    return false;
                }
            } else {
                $this->view->assign('error_message', "Unable to Authenticate User");
                return false;
            }
        }
    }

    public function send_mail($mail_subject, $mail_body, $mail_to, $mail_from, $mail_to_name, $mail_from_name) {
        $mailObj = new Zend_Mail();
        $mailObj->setSubject($mail_subject);
        $mailObj->setBodyHtml($mail_body);
        $mailObj->addTo($mail_to, $mail_to_name);
        $mailObj->setFrom($mail_from, $mail_from_name);
        $mailObj->send($this->sendgrid_mail);
    }

    public function userCheckSessionStatusAction() {
        $userid = $this->user->user_id;
        $login_session = new Application_Model_User;
        $check_status = $login_session->userModelCheckSesionStatus($userid);
        echo $check_status;
        die;
    }

    public function userchecksessionAction() {
        //echo "gfgf"; die;
        $userid = $this->user->user_id;
        //echo $userid ; die;
        if ($userid != "") {
            $sesid = session_id();
            $login_session = new Application_Model_User;
            $check_sesid = $login_session->usermodelchecksession($sesid);
            print_r($check_sesid);
            die;
        }
    }

    public function userlogininsertsessionAction() {


        $userid = $this->user->user_id;
        if ($userid != "") {
            $userid = $userid;
            $sesid = session_id();
            $ip = $_SERVER['REMOTE_ADDR'];
            $serverdetails = serialize($_SERVER);
        }

        $login_session = new Application_Model_User;
        if (!empty($sesid)) {
            $login_sesid = $login_session->usermodellogininsertsession($sesid, $ip, $serverdetails, $userid);
        }
        $count_sesid = $login_session->usermodellogincounsession();

        echo $count_sesid;
        die;
    }

    public function assignSchemeAction() {

        if (!in_array($this->user->user_role, $this->admin_role)) {
            $this->_redirect('');
        }

        $this->captcha_session_id = Zend_Session::getId();

        $request = $this->getRequest();
        $ministry_id = base64_decode($request->getParam('ministry_id'));
        $user_id = base64_decode($request->getParam('user_id'));
        if ($user_id < 1) {
            $this->_redirect('');
        }
        $user_ip = $request->getServer('REMOTE_ADDR');

        $get_user_info = $this->user_model->getUserDetails($user_id);

        $ministry_name = $this->user_model->getMinistryList($ministry_id);
        $this->view->assign('ministry_name', $ministry_name[0]['ministry_name']);

        //Get All scheme owner along with details of assigned scheme to the particular user
        $get_all_assigned_schemes = $this->user_model->getSchemeOwnerDetails(null, $scheme_owner_role);
        foreach ($get_all_assigned_schemes as $skey => $sval) {
            $assigned_schemes_list[] = $sval['scheme_list'];
        }

        //Get details of assigned scheme of a specific scheme owner
        $scheme_owner_details = $this->user_model->getSchemeOwnerDetails($user_id);
        foreach ($scheme_owner_details as $skey => $sval) {
            $assigned_schemes_list_scheme_owner['scheme_list'][] = $sval['scheme_list'];
        }

        $scheme_list_arr = $this->report_model->getSchemeList(null, $ministry_id);
        //$assigned_schemes_list_scheme_owner['scheme_list'] = [];
        //If scheme is already assigned; remove it from the scheme list that is visible to other scheme owner
        foreach ($scheme_list_arr as $skey => $sval) {
            if ((in_array($sval['scheme_id'], $assigned_schemes_list)) &&
                    (!in_array($sval['scheme_id'], $assigned_schemes_list_scheme_owner['scheme_list'])))
                unset($scheme_list_arr[$skey]);
        }
        $scheme_count = count($scheme_list_arr);
        $this->view->assign('scheme_count', $scheme_count);

        // Assign Form
        $this->user_form->assignScheme($get_user_info, $scheme_list_arr);
        $this->view->form = $this->user_form;

        //Populate any preassgined schemes for the scheme owner
        if ($assigned_schemes_list_scheme_owner) {
            $this->user_form->populate($assigned_schemes_list_scheme_owner);
        }

        //Form Submit Request Logic
        if ($this->getRequest()->isPost()) {
            if ($this->user_form->isValidPartial($request->getPost())) {
                $form_data = $request->getPost();
                if(IS_CAPTCHA_ENABLE=='Y')
				{
                if ($form_data['vercode'] != $_SESSION["vercode"]) {
                    $this->view->assign('error_message', INCORRECT_CAPTCHA);
                    return false;
                }
				}
                if ($form_data['sessionCheck'] != $this->captcha_session_id) {
                    $this->view->assign('error_message', CSRF_ATTACK);
                    return false;
                }

                $form_data['ip_address'] = $user_ip;
                $form_data['updated_by'] = $this->user->user_id;
                if (isset($form_data['scheme_list'])) {
                    $query_res = $this->user_model->updateSchemeOwner($form_data);
                    if ($query_res) {
                        $this->_redirect('/user/assign-scheme-user-list?message=update');
                    }
                } else {
                    $this->view->assign('error_message', 'Please select a Scheme to assign');
                }
            }
        }
    }

    public function assignSchemeUserListAction() {

        if (!in_array($this->user->user_role, $this->admin_role)) {
            $this->_redirect('');
        }

        $this->captcha_session_id = Zend_Session::getId();

        $request = $this->getRequest();
        $ministry_id = $request->getParam('ministry_id');
        $user_id = $request->getParam('user_id');
        $user_ip = $request->getServer('REMOTE_ADDR');
        $scheme_owner_role = 4;

        if ($request->getParam('message') == 'update') {
            $this->view->assign('success_message', RECORD_UPDATED);
        }

        //Get All scheme owner along with details of assigned scheme to the particular user
        $scheme_owner_details = $this->user_model->getSchemeOwnerDetails(null, $scheme_owner_role);

        //Process DB result to create array
        foreach ($scheme_owner_details as $skey => $sval) {
            $user_id = $sval['user_id'];
            $scheme_owner_details_arr[$user_id]['user_id'] = $user_id;
            $scheme_owner_details_arr[$user_id]['user_name'] = $sval['user_name'];
            $scheme_owner_details_arr[$user_id]['user_first_name'] = $sval['user_first_name'];
            $scheme_owner_details_arr[$user_id]['user_last_name'] = $sval['user_last_name'];
            $scheme_owner_details_arr[$user_id]['user_email'] = $sval['user_email'];
            $scheme_owner_details_arr[$user_id]['user_mobile'] = $sval['user_mobile'];
            $scheme_owner_details_arr[$user_id]['user_designation'] = $sval['user_designation'];
            $scheme_owner_details_arr[$user_id]['user_designation'] = $sval['user_designation'];
            $scheme_owner_details_arr[$user_id]['role_name'] = $sval['role_name'];
            $scheme_owner_details_arr[$user_id]['ministry_name'] = $sval['ministry_name'];
            $scheme_owner_details_arr[$user_id]['ministry_id'] = $sval['ministry_id'];

            if ($sval['scheme_name']) {
                $scheme_owner_details_arr[$user_id]['assigned_schemes'][] = $sval['scheme_name'];
            }
        }
        $this->view->assign('scheme_owner_details', $scheme_owner_details_arr);
    }

}
