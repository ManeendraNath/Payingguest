<?php

require_once 'Zend/Session.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Zend/Validate.php';
require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Auth.php';
require_once 'Zend/Captcha/Image.php';

class AuthnewController extends Zend_Controller_Action {

    protected $method_name = '';
    protected $user = '';
    protected $captcha = '';
    protected $auth_form = '';
    protected $auth_model = '';
    protected $user_controller = '';
    protected $ministry_model = '';

    public function init() {

        $this->controller_name = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $this->method_name = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

        $this->user = new Zend_Session_Namespace('user_session');
        $this->captcha_session_id = new Zend_Session_Namespace('captchaa_session');

        $this->sendgrid_mail = $this->_helper->Custom->sendGridConfig();

        $this->auth_form = new Application_Form_Authnew;
        $this->auth_model = new Application_Model_Authnew;
        //$this->auth_modela = new Application_Model_Auth;
        $this->user_model = new Application_Model_User;
        $this->ministry_model = new Application_Model_DbtMinistry;

        if ($this->user->user_name == '') {
            $this->_helper->layout->setLayout('layout');
        } elseif ($this->user->role == 1 || $this->user->role == 4) {
            $this->_helper->layout->setLayout('layout_admin');
        } else {
            $this->_helper->layout->setLayout('layout');
        }

        if ($this->_helper->Functions->validateResponseMethod() == FALSE) {
            $this->_redirect('');
        }
    }

    public function loginAction() {
        $this->var_session_id = Zend_Session::getId();
        $request = $this->getRequest();

        if ($request->getParam('message') == "mailsent") {
            $success_message = "Password has been sent to registered email ID (if correct username was entered).";
        } elseif ($request->getParam('message') == "updatepass") {
            $success_message = "Password has been updated successfully";
        } elseif ($request->getParam('message') == "nologin") {
            //$success_message = "Username or Password is incorrect!";
            $this->view->assign('error_message', "Invalid username or password.");
        }

        $this->view->assign('success_message', $success_message);

        $redirect_page = $request->getParam('redirect-page');

        if ($redirect_page && $this->user->user_name != '') {
            $this->_redirect('/' . $redirect_page);
        }
        if (!empty($this->user->user_name)) {
            $this->_redirect('/user/user-profile');
        }

        $form = $this->auth_form;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {

            $form_data = $request->getPost();
          

            $get_user_account_details = $this->user_model->getUserDetails(null, $form_data['user_name']);

            if (($get_user_account_details['user_status'] == 2) && ($get_user_account_details['login_attempt'] >= LOCK_COUNT)) {
                $error_message = "Your account has been deactivated due to unsuccessful login attempts. Please contact Site Administrator.";
                $this->view->assign('error_message', $error_message);
                return false;
            } elseif ($get_user_account_details['user_status'] == 2) {
                $error_message = "Your account has been deactivated. Please contact Site Administrator.";
                $this->view->assign('error_message', $error_message);
                return false;
            }elseif ($get_user_account_details['user_status'] == 0) {
                $error_message = "Your account has been deactivated. Please contact Site Administrator.";
                $this->view->assign('error_message', $error_message);
                return false;
            }

            //Check if User Exists
            if (count($get_user_account_details) > 1) {

                //Authenticate User
                $user_exists = $this->auth_model->authenticateUser($form_data['user_name'], $this->_helper->Custom->findMd5Value($form_data['user_password']));

                if ($user_exists) {

                    //First time login by the user after account is created
                    if ($get_user_account_details['sys_gen_pwd_status'] == 'y' && $get_user_account_details['user_status'] == 1) {
                        $redirect_url = '/authnew/change-password?auth=' . base64_encode($get_user_account_details['user_id']) . '&token=' . $get_user_account_details['reset_login_token'];
                        $this->_redirect($redirect_url);
                    } else {

                        //Login to dbt community
                        $_SESSION['l_name'] = base64_encode($get_user_account_details['user_name']);
                        $_SESSION['l_id'] = base64_encode($get_user_account_details['id']);

                        // Set session values
                        $this->user->user_id = $get_user_account_details['user_id'];
                        $this->user->user_name = $get_user_account_details['user_name'];
                        $this->user->user_role = $get_user_account_details['user_role_id'];
                        $this->user->state_code = $get_user_account_details['state_code'];
                        $this->user->ministry_id = $get_user_account_details['ministry_id'];

                        session_regenerate_id();

                        //Update user status
                        $columns = array('sys_gen_pwd_status' => NULL, 'login_attempt' => 0, 'reset_login_token' => NULL);
                        $this->auth_model->updateUserStatus($get_user_account_details['user_id'], $columns);
                    }
                } else {

                    //Unsuccessful login Attempt- Increment counter
                    $this->auth_model->updateUserLoginAttempt($form_data['user_name']);

                    //Check unsuccessful attempts
                    if ($get_user_account_details['login_attempt'] >= LOCK_COUNT) {
                        $error_message = "Your account has been deactivated due to unsuccessful login attempts. Please contact the site administrator.";
                        $this->view->assign('error_message', $error_message);
                        return false;
                    } else {
                        $error_message = "Invalid username or password.";
                        $this->view->assign('error_message', $error_message);
                        $this->_redirect('/authnew/login?message=nologin');
                        //return false;
                    }
                }
            } else {
                $error_message = "Invalid username or password.";
                $this->view->assign('error_message', $error_message);
                return false;
            }

            $redirect_url = '';
            if ($this->user->user_role == 1) {
                $redirect_url = '/user/user-view';
            } elseif ($this->user->user_role == 2) {
                $redirect_url = '/stateut';
            } elseif ($this->user->user_role == 3) {
                $redirect_url = '/user/user-profile';
            } elseif ($this->user->user_role == 4) {
                $redirect_url = 'manageschemedatanew/scheme-ministry-wise';
            } elseif ($this->user->user_role == 5) {
                $redirect_url = 'diststateschemereport';
            } elseif ($this->user->user_role == 6) {
                $redirect_url = 'manageschemedatanew/scheme-ministry-wise';
            } else {
                $redirect_url = '/authnew/login';
            }
            $this->_redirect($redirect_url);
        }
    }

    public function changePasswordAction() {

        $this->var_session_id = Zend_Session::getId();
        $request = $this->getRequest();

        $user_id = intval(base64_decode($request->getParam('auth')));
        $user_token = trim(base64_decode($request->getParam('token')));

        if ($user_id == '' || $user_token == '') {
            $this->_redirect('/');
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
                //return false;
            }
            if (strlen($form_data['confirm_new_password']) < 8) {
                $this->view->assign('error_message', "Confirm Password should contain minimum 8 characters");
                //return false;
            }

            if (strlen($form_data['new_password'] !== $form_data['confirm_new_password'])) {
                $this->view->assign('error_message', "New Password and Confirm password does not match");
                //return false;
            }

            if (!$user_id || !$user_token) {
                $this->view->assign('error_message', "Unable to Authenticate User");
                return false;
            }

            $get_user_details = $this->user_model->getUserDetails($user_id);

            //If User Exists
            if ($get_user_details) {

                //Authenticate User
                $authenticate_user = $this->auth_model->authenticateUser($get_user_details['user_name'], $this->_helper->Custom->findMd5Value($form_data['old_password']), base64_encode($user_token));

                //Update User Password & Status
                if ($authenticate_user) {
                    $get_change_password_status = $this->auth_model->changeUserPassword($this->_helper->Custom->findMd5Value($form_data['new_password']), $user_id);

                    $columns = array('sys_gen_pwd_status' => NULL, 'login_attempt' => 0, 'reset_login_token' => NULL);
                    $this->auth_model->updateUserStatus($user_id, $columns);
                } else {
                    $this->view->assign('error_message', "Unable to Authenticate User");
                    return false;
                }
            } else {
                $this->view->assign('error_message', "Unable to Authenticate User");
                return false;
            }

            session_regenerate_id();

            $this->user->user_id = $get_user_details['user_id'];
            $this->user->user_name = $get_user_details['user_name'];
            $this->user->user_role = $get_user_details['user_role_id'];
            $this->user->state_code = $get_user_details['state_code'];
            $this->user->ministry_id = $get_user_details['ministry_id'];

            if ($this->user->user_role == 1) {
                $redirect_url = '/authnew/login';
            } elseif ($this->user->user_role == 2) {
                $redirect_url = '/stateut';
            } elseif ($this->user->user_role == 3) {
                $redirect_url = '/user/user-profile';
            } elseif ($this->user->user_role == 4) {
                $redirect_url = '/manageschemedatanew/scheme-ministry-wise';
            } elseif ($this->user->user_role == 5) {
                $redirect_url = 'diststateschemereport';
            } elseif ($this->user->user_role == 6) {
                $redirect_url = 'manageschemedatanew/scheme-ministry-wise';
            } else {
                $redirect_url = '/authnew/login';
            }

            $this->_redirect($redirect_url);
        }
    }

    public function forgotPasswordAction() {

        $this->var_session_id = Zend_Session::getId();
        $request = $this->getRequest();

        if (!empty($this->user->user_role)) {
            $this->_redirect('');
        }

        $form = $this->auth_form;
        $form->forgotPasswordForm();
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

            $get_user_details = $this->user_model->getActiveUser(null, $form_data['user_name']);

            if ($get_user_details) {
                $user_token = $this->_helper->Custom->generateUserToken();

                $columns = array('sys_gen_pwd_status' => NULL, 'login_attempt' => 0, 'reset_login_token' => $user_token);
                $update_token = $this->auth_model->updateUserStatus($get_user_details['user_id'], $columns);

                $url = WEB_LINK . 'authnew/reset-password?auth=' . base64_encode($get_user_details['user_id']) . "&token=" . base64_encode($user_token);

                //Send Mail
                $mail_subject = FORGOT_SUBJECT;
                $mail_body = "Dear " . $get_user_details['user_first_name'] . ",<br /><br />Please take a note of your account details and keep them safe with you. You are requested to change<br />your password immediately. Please click on <a href='" . $url . "'>Reset Password</a> to change your password. <br/><br/>
				Regards,<br />DBT Bharat Team <br />
				Website: " . WEB_LINK . "<br />
				(This is a system generated message. Please do not reply to this email)";
                $mail_to = $get_user_details['user_email'];
                $mail_from = MAIL_FROM;
                $mail_to_name = $get_user_details['user_first_name'];
                $mail_from_name = WEBSITE_TITLE;

                $this->send_mail($mail_subject, $mail_body, $mail_to, $mail_from, $mail_to_name, $mail_from_name);

                $this->_redirect('/authnew/login?message=mailsent');
            } else {
                $this->view->assign('success_message', "Password has been sent to registered email ID (if correct username was entered).");
                return false;
            }
        }
    }

    public function resetPasswordAction() {

        $this->var_session_id = Zend_Session::getId();
        $request = $this->getRequest();

        $user_token = trim(base64_decode($request->getParam('token')));
        $user_id = intval(base64_decode($request->getParam('auth')));
        $form = $this->auth_form;
        $form->resetPasswordForm();
        $this->view->form = $form;

        if (!empty($user->user_role) || !$user_id) {
            $this->_redirect('');
        }

        if ($this->getRequest()->isPost()) {

            $form_data = $request->getPost();
            $new_password_length = strlen(trim($form_data['new_password']));
            $confirm_new_password_length = strlen(trim($form_data['confirm_new_password']));
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
            if ($new_password_length < 8) {
                $error_message = "New Password field will take minimum 8 characters!";
                $this->view->assign('error_message', $error_message);
                return false;
            }
            if ($confirm_new_password_length < 8) {
                $error_message = "Confirm Password field will take minimum 8 characters!";
                $this->view->assign('error_message', $error_message);
                return false;
            }

            if ($form_data['new_password'] !== $form_data['confirm_new_password']) {
                $this->view->assign('error_message', "New Password and Confirm password does not match!");
                return false;
            }

            if (!$user_id || !$user_token) {
                $this->view->assign('error_message', "Unable to Authenticate User");
                return false;
            }

            $get_user_details = $this->user_model->getUserDetails($user_id);
            if ($get_user_details) {

				$get_user_details_log = $this->user_model->getUserDetailsFromlog($user_id);

				$log_password = '';
				foreach($get_user_details_log as $key => $val){
					$log_password[] = $val['user_password'];
				}
				array_push($log_password,$get_user_details['user_password']);
				
				$new_enc_pwd = $this->_helper->Custom->findMd5Value($form_data['new_password']);

				if ($get_user_details['reset_login_token'] != $user_token) {
                    $this->view->assign('error_message', "Unable to Authenticate User");
                    return false;
                } elseif (in_array($new_enc_pwd, $log_password)) {
                    $this->view->assign('error_message', "You are not allowed to use last three password; please enter new password");
                    return false;
                } else if (($get_user_details['reset_login_token'] === $user_token)) {

                    $this->auth_model->changeUserPassword($new_enc_pwd, $user_id);

                    $columns = array('sys_gen_pwd_status' => NULL, 'login_attempt' => 0, 'reset_login_token' => NULL);
                    $this->auth_model->updateUserStatus($user_id, $columns);

                    $this->_redirect('/authnew/login?message=updatepass');
                } else {
                    $this->view->assign('error_message', "Some Error Occured");
                    return false;
                }
            } else {
                $this->view->assign('error_message', "Unable to Authenticate User");
                return false;
            }
        }
    }

    public function logoutAction() {
        $user_id = new Zend_Session_Namespace('user_session');
        $user_id = $user_id->user_id;
        $sesid = session_id();
        $login_statatus = 0;
        $userobj = new Application_Model_Auth;
        $userdata = $userobj->updatesessionstatus($sesid);
        session_regenerate_id();
        Zend_Session::destroy(true);
        $this->_redirect('');
    }

    public function indexAction() {

        $slang = new Zend_Session_Namespace('languageold');
        $state_model = new Application_Model_State();
        $current_fy = $this->_helper->custom->getCurrentFinancialYear();
        $current_fynw = $this->_helper->custom->getCurrentFinancialYearNw();
        $language = ($website_lang->language) ? $website_lang->language : '2';
        $data_obj_for_multimedia = new Application_Model_MultimediaModel;
        if ($admname->adminname == '') {
            $_SESSION['user_out'] = base64_encode('userout');
        }

        $scheme_group = $this->auth_model->getSchemeGroup();
        $no_of_ministries = $this->auth_model->getNoOfMinistryFromSchemeMaster();
        $no_of_schemes = $this->auth_model->getNoOfSchemesFromSchemeMaster();

        $this->view->assign("no_of_ministries", count($no_of_ministries));
        $this->view->assign("no_of_schemes", count($no_of_schemes));
        $language_id = $slang->language;
        if ($language_id == '') {
            $language_id = 2;
        }

        $photogallary_data = $data_obj_for_multimedia->getGalleryData(array(7), $language_id);
        $this->view->assign('homepage_banner', $photogallary_data);
        $this->view->assign('getfinancialyear', $current_fynw);

        $state_details = $state_model->getStateData();
        foreach ($state_details as $key => $val) {
            $state_list[$val['state_code']] = $val['state_name'];
        }

        $this->view->assign("state_list", $state_list);
        $scheme_wise_results = '';
        $sumArray = array();
        $statewise = 1;
        $statewise_data = array();
        $statewise_data = $this->auth_model->getTransactionalDetails($current_fy);

        $this->view->assign("state_wise_report", $statewise_data);

        /*         * ********** Showing data on India map for previous FY **************** */
        $data_before_current_ty = $this->auth_model->getHomeGraph();

        foreach ($data_before_current_ty as $key => $val) {

            $groupdata['fund_data'][$val['financial_year']][$val['scheme_group_id']][$val['benefit_type_id']] = $this->_helper->functions->round_number(($val['total_fund_transfer']) / 10000000, 3);
            $groupdata['beneficiary'][$val['financial_year']][$val['scheme_group_id']][$val['benefit_type_id']] = $this->_helper->functions->round_number($val['no_of_beneficiaries'] / 10000000, 3);
            if ($val['benefit_type_id'] == 1) {
                $groupdata['total_schemes'][$val['financial_year']]['cash'] += $val['no_of_scheme'];
            } else {
                $groupdata['total_schemes'][$val['financial_year']]['inkind'] += $val['no_of_scheme'];
            }

            $total_dbt_transfer_cumulative += $val['total_fund_transfer'];
        }
        /*         * ********** Showing data on India map for previous FY END **************** */


        /*         * *************** Getting data group by scheme group ******************* */
        $groupwise_transactional_data = $this->auth_model->getTransactionalDetailsBySchemeGroup($current_fy, $fund_absolute_val = 'n');
        $groupdata['fund_data'][$current_fynw] = $groupwise_transactional_data['transactional_data_arr'][$current_fy];
        $total_dbt_transfer_in_current_fy = $groupwise_transactional_data['total_dbt_transfer_in_current_fy'];
        $total_no_of_transactions_in_current_fy = $groupwise_transactional_data['total_no_of_transactions_in_current_fy'];

        /*         * *************** Getting data group by scheme group ******************* */

        /*         * ************ Getting Count of Onboarded schemes ***************** */
        $scheme_by_benefit_type = $this->auth_model->countSchemesByBenefitType();

        $groupdata['total_schemes'][$current_fynw]['cash'] = $scheme_by_benefit_type['scheme_count_benefit_type']['cash_scheme'];
        $groupdata['total_schemes'][$current_fynw]['inkind'] = $scheme_by_benefit_type['scheme_count_benefit_type']['inkind_scheme'];
        $groupdata['total_schemes'][$current_fynw]['cash_and_inkind'] = $scheme_by_benefit_type['scheme_count_benefit_type']['cash_and_inkind_scheme'];

        /*         * ************ Getting Count of Onboarded schemes END ***************** */

        /*         * ************ Getting Beneficiaries data of current FY ************** */
        $benefit_type_by_scheme = $scheme_by_benefit_type['benefit_type_by_scheme'];
        $get_beneficiaries = $this->auth_model->getBeneficiaries($current_fy);

        foreach ($get_beneficiaries as $key => $val) {

            if ($benefit_type_by_scheme[$val['scheme_id']] == 2) {
                $no_beneficiaries_aadhaar = $val['no_beneficiaries_aadhaar'];
                $groupdata['beneficiary'][$current_fynw][$val['scheme_group_id']][2] += $no_beneficiaries_aadhaar;
            } else {
                $no_beneficiaries_normative = $val['no_beneficiaries_normative'] + $val['no_beneficiaries_additional_state'];
                $groupdata['beneficiary'][$current_fynw][$val['scheme_group_id']][1] += $no_beneficiaries_normative;
            }
        }

        foreach ($groupdata['beneficiary'][$current_fynw] as $ykey => $yval) {
            if ($yval[1]) {
                $groupdata['beneficiary'][$current_fynw][$ykey][1] = $this->_helper->functions->round_number($yval[1] / 10000000, 2);
            }
            if ($yval[2]) {
                $groupdata['beneficiary'][$current_fynw][$ykey][2] = $this->_helper->functions->round_number($yval[2] / 10000000, 2);
            }
        }
        /*         * ************ Getting Beneficiaries data of current FY END ************** */

        //Cumulative fund transfer
        $total_dbt_transfer_cumulative +=$total_dbt_transfer_in_current_fy;

        $this->view->assign("data", $groupdata);
        $this->view->assign("total_dbt_transfer", $total_dbt_transfer_in_current_fy);
        $this->view->assign("total_no_of_transactions", $total_no_of_transactions_in_current_fy);
        $this->view->assign("scheme_group", $scheme_group);
        $this->view->assign("total_dbt_transfer_cumulative", $total_dbt_transfer_cumulative);

        $statewise = 0;

        $data_obj = new Application_Model_Estimatedgain;
        $estimated_gain_data = $data_obj->getEstimatedGain(null, 1);
        foreach ($estimated_gain_data as $key => $val) {
            $total_estimated += ($val['Prev_fy_savings'] + $val['cur_fy_savings']);
        }

        $this->view->assign("total_estimated_gain", $total_estimated);
    }

    public function send_mail($mail_subject, $mail_body, $mail_to, $mail_from, $mail_to_name, $mail_from_name) {
        $mailObj = new Zend_Mail();
        $mailObj->setSubject($mail_subject);
        $mailObj->setBodyHtml($mail_body);
        $mailObj->addTo($mail_to, $mail_to_name);
        $mailObj->setFrom($mail_from, $mail_from_name);
        $mailObj->send($this->sendgrid_mail);
    }

}
?>

