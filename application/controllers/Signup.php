<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Signup extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
        if (is_active_page('signup')) {
            if ($this->isLoggedin) {
                $this->session->userdata('lastAccessUrl') ? redirect($this->session->userdata('lastAccessUrl')) : redirect(site_url());
            }
            $this->load->model('Customer_Model');
            $this->load->model('Cookie_Model');
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
        $page = $this->db->where('slug', 'signup')->get('pages')->row();
        $lang = lang_option();
        $this->body_class = ["page-signup"];
        $data['lang'] = $lang;
        $data['isLoggedin'] = $this->isLoggedin;
        $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
        $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
        $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
        $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
        $data['header_nav'] = 'signup';
        $data['sidebar_nav'] = '';
        $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_title, 'description' => json_decode($page->description, TRUE)[$lang]];
        $data['breadcrumb'][site_url(array("order"))] = $this->page_title;
        $this->render('signup/signup', $data);
    }

    private function __process()
    {
        if ($this->__hasvaliddata() && empty($this->Customer_Model->precheckCustomer($this->input->post('email'), $this->input->post('phone')))) {
            $id = $this->__submit($this->preparePostedData());
            if (!empty($id)) {
                if ((config_item('email_service') === "yes" ||  config_item('sms_service') === "yes") && !empty($this->session->tempdata('verification'))) {
                    redirect(site_url('signup/verify'));
                } else {
                    $this->session->set_userdata('customerId', $id);
                    $this->session->unset_tempdata('verification');
                    set_cookie("__foodie", $this->encryption->encrypt($this->session->userdata('customerId')), 365 * 50 * 3600 * 24);
                    redirect($this->session->userdata('lastAccessUrl'));
                }
            } else {
                $this->session->set_flashdata('warning_msg', lang('resgistration_failed_msg_for_wrong_input'));
            }
        } else {
            $this->session->set_flashdata('warning_msg', lang('resgistration_failed_msg_for_wrong_input'));
        }
    }

    private function __submit($data)
    {
        $data['name'] = json_encode(array('en' => strtolower($data['name_en']), 'bn' => empty($data['name_bn']) ? $data['name_en'] : $data['name_bn']), JSON_UNESCAPED_UNICODE);
        $data['password'] = md5($data['password']);
        $id = $this->Customer_Model->save($this->__unset_data($data), NULL);
        if (!empty($id) && (config_item('email_service') === "yes" ||  config_item('sms_service') === "yes")) {
            $this->session->set_tempdata('verification', array('id' => $id, 'code' => $this->randomcode(6, 'number')), 600);
            $this->session->set_flashdata('success_msg', lang('resgistration_success_msg') . ' ' . lang('verification_code_sent_msg'));
            $text = lang('resgistration_success_msg') . ' ' . sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code']);
            $this->__send_message($this->Customer_Model->get_single('customers', array('id' => $id))->row(), lang('registration_success'), $text);
        }
        return $id;
    }
    private function __unset_data($data)
    {
        unset($data['retype_password']);
        unset($data['name_en']);
        unset($data['name_bn']);
        return $data;
    }
    private function __hasvaliddata()
    {
        $this->form_validation->set_rules('name_en', lang('name'), 'trim|required|min_length[2]|max_length[50]|regex_match[/^[a-z\s ]+$/i]', array('required' => lang('field_required_msg'), 'min_length' => sprintf($this->lang->line('min_error_msg'), 2), 'max_length' => sprintf($this->lang->line('max_error_msg'), 50), 'regex_match' => lang('alphaonly_error_msg')));
        $this->form_validation->set_rules('phone', lang('mobile_number'), 'trim|required|regex_match[/^[0-9]{9,15}$/]', array('required' => lang('field_required_msg'), 'regex_match' => lang('mobile_error_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email|max_length[100]', array('required' => lang('field_required_msg'), 'valid_email' => lang('email_error_msg')));
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]', array('required' => lang('field_required_msg'), 'min_length' => sprintf($this->lang->line('min_error_msg'), 8)));
        $this->form_validation->set_rules('retype_password', lang('retype') . ' ' . lang('password'), 'required|min_length[8]', array('required' => lang('field_required_msg'), 'min_length' => sprintf($this->lang->line('min_error_msg'), 8), 'matches' => lang('equalto_error_msg')));
        return $this->form_validation->run();
    }
    private function __send_message($to, $subject, $text)
    {
        if ($this->session->tempdata('verification')) {
            if (config_item('sms_service') === "yes") {
                $this->sms($to->phone, $text . '-' . get_domain());
            }
            if (config_item('email_service') === "yes") {
                $this->__to_customer($to, $subject, $text);
                $this->__to_accountteam($to, $subject, $text);
            }
        }
    }

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
            $this->send_email($data);
        }
    }

    private function __to_accountteam($to, $subject, $text)
    {
        if (config_item('email_service') === "yes") {
            $lang = lang_option();
            $shop_name = config_item('shop_name_' . $lang);
            $name = json_decode($to->name, TRUE)[$lang];
            $data['name'] = $name;
            $data['phone'] = $to->phone;
            $data['email'] = $to->email;
            $data['recipent'] = lang('account_manager');
            $data['recipent_type'] = "admin";
            $data['to'] = config_item('shop_email');
            $data['from'] = 'noreply@' . config_item('shop_web');
            $data['from_title'] = $shop_name;
            $data['subject'] =  $subject;
            $data['text'] =  $text;
            $data['message'] = $this->load->view('email/common', $data, TRUE);
            $data['message_alt'] = $text;
            $this->send_email($data);
        }
    }


    public function verify($id = null)
    {
        if ($this->session->tempdata('verification') && (config_item('sms_service') === "yes" || config_item('email_service') === "yes")) {
            if (trim($id)  === md5($this->session->tempdata('verification')['id'])) {
                $to = $this->Customer_Model->get_single('customers', array('id' =>  $this->session->tempdata('verification')['id']))->row();
                $this->session->set_tempdata('verification', array('id' =>  $to->id, 'code' => $this->randomcode(6, 'number')), 600);
                $this->sms($to->phone, sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code']) . '-' . get_domain());
                $this->__to_customer($to, lang('resend'), sprintf(lang('use_verification_code'), $this->session->tempdata('verification')['code']));
                $data['warning_msg'] = lang('verification_code_sent_msg');
            } else {
                $data['warning_msg'] = null;
            }
            if ($this->input->post()) {
                $this->__process_verification();
            }
            $page = $this->db->where('slug', 'signup')->get('pages')->row();
            $lang = lang_option();
            $this->robots = "noindex,nofollow";
            $this->body_class = ["page-signup"];
            $data['lang'] = $lang;
            $data['isLoggedin'] = $this->isLoggedin;
            $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
            $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
            $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
            $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
            $data['header_nav'] = 'signup';
            $data['sidebar_nav'] = '';
            $data['id'] = md5($this->session->tempdata('verification')['id']);
            $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_title, 'description' => json_decode($page->description, TRUE)[$lang]];
            $data['breadcrumb'][site_url(array("order"))] = $this->page_title;
            $this->render('signup/verify', $data);
        } else {
            redirect(site_url('signup'));
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
            $this->session->set_flashdata('warning_msg', lang('resgistration_failed_msg_for_wrong_input'));
            redirect(current_url());
        }
    }
}
