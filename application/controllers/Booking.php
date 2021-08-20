<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Booking extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        if ($this->input->post()) {
            $this->__process();
        }
        $page = $this->db->where('slug', 'booking')->get('pages')->row();
        $lang = lang_option();
        $this->body_class = ["page-booking"];
        $data['lang'] = $lang;
        $data['isLoggedin'] = $this->isLoggedin;
        $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
        $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
        $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
        $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
        $data['header_nav'] = 'booking';
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
        $data['map_height'] = "250";
        $this->render('booking', $data);
    }
    private function __process()
    {
        if ($this->__hasvaliddata()) {
            $customer = $this->Customer_Model->getMe();
            $data = $this->preparePostedData();
            $data['cust_id'] = $customer['id'];
            $data['name'] = $customer['name'];
            $data['phone'] = $customer['phone'];
            $data['email'] = $customer['email'];
            $data['stage'] = 1;
            $data['created_at'] = time() * 1000;
            $data['updated_at'] = time() * 1000;
            $post = $data;
            $post['name'] = json_encode($data['name'], JSON_UNESCAPED_UNICODE);
            $status = $this->db->insert('tablebookings',  $post);
            if ($status) {
                $to = (object) $data;
                //$this->__send_message($to,  lang('new') . ' ' . lang('booking'), trim($to->note));
                $this->session->set_flashdata('success_msg', sprintf(lang('contact_mail_sent_success_msg'), ''));
                redirect(site_url('booking'));
            } else {
                $this->session->set_flashdata('warning_msg', sprintf(lang('contact_mail_sent_failed_msg'), ''));
            }
        } else {
            $this->session->set_flashdata('warning_msg', sprintf(lang('contact_mail_sent_failed_msg'), ''));
        }
    }
    private function __hasvaliddata()
    {
        $this->form_validation->set_rules('date', lang('date'), 'trim|required', array('required' => sprintf(lang('field_required_msg'), lang('date'))));
        $this->form_validation->set_rules('time', lang('time'), 'trim|required', array('required' => sprintf(lang('field_required_msg'), lang('date'))));
        $this->form_validation->set_rules('guest', lang('guest_number'), 'trim|required', array('required' => sprintf(lang('field_required_msg'), lang('date'))));
        $this->form_validation->set_rules('note', lang('message'), 'trim|required', array('required' => sprintf(lang('field_required_msg'), lang('message'))));
        return $this->form_validation->run();
    }
    private function __send_message($to, $subject, $text)
    {
        if (config_item('sms_service') === "yes") {
            $smsprefix = $subject . ' ' . lang('guest_number') . ' : ' . $to->guest . ', ';
            $smsprefix .= lang('date') . ' : ' . date('d/m/Y', strtotime($to->date)) . ', ';
            $smsprefix .= lang('time') . ' : ' . date('h:i A', strtotime($to->date . ' ' . $to->time)) . ', ';
            $this->sms(config_item('shop_phone'), $smsprefix . $text . ' ' . lang('requested_by') . ' ' . $to->name[lang_option()] . ' / ' . $to->phone . '-' . get_domain());
        }
        if (config_item('email_service') === "yes") {
            $mailprefix = $subject . '<br/>' . lang('guest_number') . ' : ' . $to->guest . '<br/>';
            $mailprefix .=  lang('date') . ' : ' . date('d/m/Y', strtotime($to->date)) . '<br/>';
            $mailprefix .= lang('time') . ' : ' . date('h:i A', strtotime($to->date . ' ' . $to->time)) . '<br/>';
            $this->__to_admin($to, $subject,  $mailprefix . $text . "<br/><b>" . lang('requested_by') . "</b><br/>" . $to->name[lang_option()] . "<br/>" . $to->phone . "<br/>" . $to->email . "<br/>");
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
            $data['message_alt'] = $text;
            $this->send_email($data);
        }
    }
}
