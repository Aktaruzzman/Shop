<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Contact extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
        if (is_active_page('contact')) {
            $this->session->set_userdata('lastAccessUrl', base_url(uri_string()));
            $this->load->model('Customer_Model');
        } else {
            $this->session->userdata('lastAccessUrl') ? redirect($this->session->userdata('lastAccessUrl')) : redirect(site_url());
        }
    }
    public function index()
    {
        if ($this->input->post()) {
            $this->__process();
        }
        $page = $this->db->where('slug', 'contact')->get('pages')->row();
        $lang = lang_option();
        $this->body_class = ["page-booking"];
        $data['lang'] = $lang;
        $data['isLoggedin'] = $this->isLoggedin;
        $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
        $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
        $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
        $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
        $data['header_nav'] = 'contact';
        $data['sidebar_nav'] = '';
        $data['page'] = ['title' => $this->page_title, 'subtitle' =>  $this->page_subtitle, 'description' => json_decode($page->description, TRUE)[$lang]];
        $data['breadcrumb'][site_url('contact')] = $this->page_title;
        $data['my'] = $this->Customer_Model->getMe();
        if (!$this->isLoggedin) {
            $data['my']['name']['en'] = "";
            $data['my']['name']['bn'] = "";
            $data['my']['phone'] = "";
            $data['my']['email'] = "";
        }
        $data['map_height'] = "165";
        $this->render('contact', $data);
    }
    private function __process()
    {
        $to = (object) $this->preparePostedData();
        if ($this->__hasvaliddata()) {
            !empty($to->message) ? $this->__send_message($to, lang('new') . ' ' . lang('contact'), trim($to->message)) : false;
            $this->session->set_flashdata('success_msg', sprintf(lang('contact_mail_sent_success_msg'), $to->name));
            redirect(site_url('contact'));
        } else {
            $this->session->set_flashdata('warning_msg', sprintf(lang('contact_mail_sent_failed_msg'), $to->name));
        }
    }
    private function __hasvaliddata()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required|min_length[2]|max_length[50]|regex_match[/^[a-z\s ]+$/i]', array('required' => lang('field_required_msg'), 'min_length' => sprintf($this->lang->line('min_error_msg'), 2), 'max_length' => sprintf($this->lang->line('max_error_msg'), 50), 'regex_match' => lang('alphaonly_error_msg')));
        $this->form_validation->set_rules('phone', lang('mobile_number'), 'trim|required|regex_match[/^[0-9]{9,15}$/]', array('required' => lang('field_required_msg'), 'regex_match' => lang('mobile_error_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email|max_length[100]', array('required' => lang('field_required_msg'), 'valid_email' => lang('email_error_msg')));
        $this->form_validation->set_rules('message', lang('query'), 'trim|required|max_length[150]', array('required' => lang('field_required_msg'), 'max_length' => sprintf($this->lang->line('max_error_msg'), 150)));
        return $this->form_validation->run();
    }
    private function __send_message($to, $subject, $text)
    {
        if (config_item('sms_service') === "yes") {
            $smsprefix = $subject . ', ';
            $this->sms(config_item('shop_phone'), $smsprefix . $text . ' ' . lang('requested_by') . ' ' . $to->name . ' / ' . $to->phone . '-' . get_domain());
        }
        if (config_item('email_service') === "yes") {
            $mailprefix = $subject . '<br/>';
            $this->__to_admin($to, $subject,  $mailprefix . $text . "<br/><b>" . lang('requested_by') . "</b><br/>" . $to->name . "<br/>" . $to->phone . "<br/>" . $to->email . "<br/>");
        }
    }


    private function __to_admin($to, $subject, $text)
    {
        if (config_item('email_service') === "yes") {
            $lang = lang_option();
            $shop_name = config_item('shop_name_' . $lang);
            $data['recipent'] = lang('admin');
            $data['recipent_type'] = "admin";
            $data['to'] = config_item('shop_email');
            $data['from'] = $to->email;
            $data['from_title'] = $shop_name . '-' . lang('customer');
            $data['subject'] =  $subject;
            $data['text'] =  $text;
            $data['message'] = $this->load->view('email/common', $data, TRUE);
            echo $data['message'];
            exit();
            $data['message_alt'] = $text;
            $this->send_email($data);
        }
    }
}
