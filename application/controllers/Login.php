<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Login extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
        if (is_active_page('login')) {
            if ($this->isLoggedin)
                $this->session->userdata('lastAccessUrl') ? redirect($this->session->userdata('lastAccessUrl')) : redirect(site_url());
            $this->load->model('Customer_Model');
            $this->load->library('encryption');
            $this->session->set_tempdata('temp_url', base_url(uri_string()), 5);
        } else {
            $this->session->userdata('lastAccessUrl') ? redirect($this->session->userdata('lastAccessUrl')) : redirect(site_url());
        }
    }
    public function index()
    {

        if ($this->input->post()) {
            $this->__process();
        }
        $page = $this->db->where('slug', 'login')->get('pages')->row();
        $lang = lang_option();
        $this->body_class = ["page-login"];
        $data['lang'] = $lang;
        $data['isLoggedin'] = $this->isLoggedin;
        $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
        $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
        $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
        $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
        $data['header_nav'] = 'login';
        $data['sidebar_nav'] = '';
        $data['page'] = ['title' => $this->page_title, 'subtitle' =>  $this->page_subtitle, 'description' => json_decode($page->description, TRUE)[$lang]];
        $data['breadcrumb'][site_url(array("order"))] = $this->page_title;
        $this->render('login/login', $data);
    }
    private function __process()
    {
        $customer = $this->_log_customer($this->preparePostedData());
        if ($this->_has_valid_data() && !empty($customer)) {
            if (empty($customer->is_verified) || !$customer->is_verified) {
                if (config_item('sms_service') === "yes" || config_item('email_service') === "yes") {
                    config_item('sms_service') === "yes" ? $this->sms($customer->phone, sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code']) . '-' . get_domain()) : true;
                    config_item('email_service') === "yes" ? $this->__to_customer($customer, lang('resend'), sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code'])) : true;
                    $this->session->set_flashdata(array('warning_msg' => $this->lang->line('verification_code_sent_msg')));
                    redirect(site_url('login/verify'));
                } else {
                    $this->session->set_userdata('customerId', $customer->id);
                    set_cookie("__foodie", $this->encryption->encrypt($this->session->userdata('customerId')), 365 * 50 * 3600 * 24);
                    redirect(current_url());
                }
            } else {
                $this->session->set_userdata('customerId', $customer->id);
                set_cookie("__foodie", $this->encryption->encrypt($this->session->userdata('customerId')), 365 * 50 * 3600 * 24);
                redirect(current_url());
            }
        }
    }

    private function _has_valid_data()
    {
        $this->form_validation->set_rules('username', $this->lang->line('mobile_number') . '/' . $this->lang->line('email'), 'trim|required', array('required' => $this->lang->line('field_required_msg')));
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required', array('required' => $this->lang->line('field_required_msg')));
        return $this->form_validation->run();
    }
    private function _log_customer($data)
    {
        $customer =  $this->Customer_Model->get_by_key($data['username']);
        if (!empty($customer) && $customer->password === md5($data['password']) && $customer->status == "active") {
            (empty($customer->is_verified) || !$customer->is_verified) ? $this->session->set_tempdata('verification', array('id' => $customer->id, 'code' => $this->randomcode(6, 'number')), 600) : true;
            return $customer;
        } else {
            $this->session->set_flashdata(array('warning_msg' => $this->lang->line('invalid_email_password'), 'username' => $data['username']));
            return false;
        }
    }
    /*
    |---------------------------------------
    | Login Verification Process Block start   
    |---------------------------------------
    */
    public function verify($id = null)
    {
        if ($this->session->tempdata('verification') && (config_item('sms_service') === "yes" || config_item('email_service') === "yes")) {
            if (trim($id)  === md5($this->session->tempdata('verification')['id'])) {
                $to = $this->Customer_Model->get_single('customers', array('id' =>  $this->session->tempdata('verification')['id']))->row();
                $this->session->set_tempdata('verification', array('id' =>  $to->id, 'code' => $this->randomcode(6, 'number')), 600);
                config_item('sms_service') === "yes" ? $this->sms($to->phone, sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code']) . '-' . get_domain()) : true;
                config_item('email_service') === "yes" ? $this->__to_customer($to, lang('resend'), sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code'])) : true;
                $data['warning_msg'] = lang('verification_code_sent_msg');
            } else {
                $data['warning_msg'] = null;
            }
            if ($this->input->post()) {
                $this->__process_verification();
            }
            $page = $this->db->where('slug', 'login')->get('pages')->row();
            $lang = lang_option();
            $this->robots = "noindex,nofollow";
            $this->body_class = ["page-login"];
            $data['lang'] = $lang;
            $data['isLoggedin'] = $this->isLoggedin;
            $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
            $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
            $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
            $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
            $data['header_nav'] = 'login';
            $data['sidebar_nav'] = '';
            $data['id'] = md5($this->session->tempdata('verification')['id']);
            $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_title, 'description' => json_decode($page->description, TRUE)[$lang]];
            $data['breadcrumb'][site_url(array("order"))] = $this->page_title;
            $this->render('login/verify', $data);
        } else {
            redirect(site_url('login'));
        }
    }

    private function __process_verification()
    {
        $id = $this->session->tempdata('verification')['id'];
        if ($this->input->post('verification_code') === $this->session->tempdata('verification')['code'] &&  $this->db->update('customers', array('is_verified' => true), array('id' => $id))) {
            $this->session->set_userdata('customerId', $id);
            $this->session->unset_tempdata('verification');
            set_cookie("__foodie", $this->encryption->encrypt($this->session->userdata('customerId')), 365 * 50 * 3600 * 24);
            redirect($this->session->userdata('lastAccessUrl'));
        } else {
            $this->session->set_flashdata('warning_msg', lang('your_session_expired') . ' ' . lang('resend_code'));
            redirect(current_url());
        }
    }

    /*
    |---------------------------------------
    | Login Verification Process Block Ends   
    |---------------------------------------
    */
    private function __to_customer($to, $subject, $text)
    {
        if (config_item('email_service') === "yes") {
            $lang = lang_option();
            $shop_name = config_item('shop_name_' . $lang);
            $name = json_decode($to->name, TRUE)[$lang];
            $data['name'] = $name;
            $data['phone'] = $to->phone;
            $data['email'] = $to->email;
            $data['recipent'] = $name;
            $data['recipent_type'] = "customer";
            $data['to'] = $to->email;
            $data['from'] = config_item('shop_email');
            $data['from_title'] = $shop_name;
            $data['subject'] = $subject;
            $data['text'] = $text;
            $data['message'] = $this->load->view('email/common', $data, TRUE);
            $data['message_alt'] = $text;
            return $this->send_email($data);
        }
    }
    /*
    |---------------------------------------
    | Login Password recovery Block start   
    |---------------------------------------
    */
    public function recovery()
    {
        if (config_item('sms_service') === "yes" || config_item('email_service') === "yes") {
            if ($this->input->post()) {
                $customer =  $this->Customer_Model->get_by_key($this->input->post('username'));
                if (!empty($customer)) {
                    $this->session->set_tempdata('verification', array('id' => $customer->id, 'code' => $this->randomcode(6, 'number')), 900);
                    $this->__process_recovery($customer);
                } else {
                    $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('invalid_field'), $this->lang->line('phone') . '/' . $this->lang->line('email'))));
                }
            }
            $page = $this->db->where('slug', 'login')->get('pages')->row();
            $lang = lang_option();
            $this->robots = "noindex,nofollow";
            $this->body_class = ["page-login"];
            $data['lang'] = $lang;
            $data['isLoggedin'] = $this->isLoggedin;
            $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
            $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
            $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
            $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
            $data['header_nav'] = 'login';
            $data['sidebar_nav'] = '';
            $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_title, 'description' => json_decode($page->description, TRUE)[$lang]];
            $data['breadcrumb'][site_url(array("order"))] = $this->page_title;
            $this->render('login/recovery', $data);
        } else {
            redirect(site_url('login'));
        }
    }
    private function __process_recovery($to)
    {
        if (!empty($this->session->tempdata('verification')['id']) && !empty($this->session->tempdata('verification')['code']) && (config_item('sms_service') === "yes" || config_item('email_service') === "yes")) {
            $s1 = config_item('sms_service') === "yes" ? $this->sms($to->phone, sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code']) . '-' . get_domain()) : true;
            $s2 = config_item('email_service') === "yes" ? $this->__to_customer($to, lang('recovery'), sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code'])) : true;
            $this->session->set_flashdata(array('warning_msg' => $this->lang->line('verification_code_sent_msg')));
            ($s1 ||  $s2) ? redirect(site_url('login/recoverycode')) : true;
        } else {
            redirect(site_url('login/recovery'));
        }
    }
    public function recoverycode($customer_id = null)
    {
        if (!empty($this->session->tempdata('verification')['id']) && !empty($this->session->tempdata('verification')['code'])) {
            if (intval($customer_id) === intval($this->session->tempdata('verification')['id'])) {
                $this->session->set_tempdata('verification', array('id' => $this->session->tempdata('verification')['id'], 'code' => $this->randomcode(6, 'number')), 900);
                $this->__process_recovery($this->db->where('id', $this->session->tempdata('verification')['id'])->get('customers')->row());
            }
            if ($this->input->post()) {
                if ($this->input->post('verification_code') === $this->session->tempdata('verification')['code']) {
                    $this->session->set_tempdata('is_verified', true, 900);
                    redirect(site_url('login/changepassword'));
                } else {
                    $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('invalid_field'), $this->lang->line('code'))));
                }
            }
            $page = $this->db->where('slug', 'login')->get('pages')->row();
            $lang = lang_option();
            $this->robots = "noindex,nofollow";
            $this->body_class = ["page-login"];
            $data['lang'] = $lang;
            $data['isLoggedin'] = $this->isLoggedin;
            $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
            $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
            $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
            $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
            $data['header_nav'] = 'login';
            $data['sidebar_nav'] = '';
            $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_title, 'description' => json_decode($page->description, TRUE)[$lang]];
            $data['breadcrumb'][site_url(array("login/recovery"))] = $this->page_title;
            $data['customer_id'] = $this->session->tempdata('verification')['id'];
            $this->render('login/recoverycode', $data);
        } else {
            redirect(site_url('login/recovery'));
        }
    }
    public function changepassword()
    {
        if ($this->session->tempdata('is_verified') && !empty($this->session->tempdata('verification')['id'])) {
            if ($this->input->post()) {
                $this->form_validation->set_rules('password', $this->lang->line('password'), 'required', array('required' => $this->lang->line('field_required_msg')));
                if ($this->form_validation->run()) {
                    if ($this->db->update('customers', array('password' => md5($this->input->post('password')), 'is_verified' => true), array('id' => $this->session->tempdata('verification')['id']))) {
                        $this->session->unset_tempdata('verification');
                        $this->session->unset_tempdata('is_verified');
                        $this->session->set_flashdata(array('success_msg' => sprintf($this->lang->line('saved_success_msg'), $this->lang->line('password'))));
                        redirect(site_url('login'));
                    } else {
                        $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('saved_failed_msg'), $this->lang->line('password'))));
                    }
                } else {
                    $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('invalid_field'), $this->lang->line('password'))));
                }
            }
            $page = $this->db->where('slug', 'login')->get('pages')->row();
            $lang = lang_option();
            $this->robots = "noindex,nofollow";
            $this->body_class = ["page-login"];
            $data['lang'] = $lang;
            $data['isLoggedin'] = $this->isLoggedin;
            $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
            $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
            $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
            $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
            $data['header_nav'] = 'login';
            $data['sidebar_nav'] = '';
            $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_title, 'description' => json_decode($page->description, TRUE)[$lang]];
            $data['breadcrumb'][site_url(array("login/changepassword"))] = $this->page_title;
            $this->render('login/changepassword', $data);
        } else {
            redirect(site_url('login/recovery'));
        }
    }
    /*
    |---------------------------------------
    | Login Password recovery Block End   
    |---------------------------------------
    */
}
