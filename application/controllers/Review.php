<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Review extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
        if (is_active_page('review')) {
            $this->session->set_userdata('lastAccessUrl', base_url(uri_string()));
            $this->load->model('Customer_Model');
        } else {
            $this->session->userdata('lastAccessUrl') ? redirect($this->session->userdata('lastAccessUrl')) : redirect(site_url());
        }
    }
    public function index($offset = 0)
    {
        if ($this->input->post()) {
            $this->__process();
        }
        $page = $this->db->where('slug', 'review')->get('pages')->row();
        $lang = lang_option();
        $this->body_class = ["page-booking"];
        $data['lang'] = $lang;
        $data['isLoggedin'] = $this->isLoggedin;
        $data['page_title'] = $this->page_title = json_decode($page->title, TRUE)[$lang];
        $data['page_subtitle'] = $this->page_subtitle = json_decode($page->subtitle, TRUE)[$lang];
        $data['page_meta_keywords'] = $this->page_meta_keywords = json_decode($page->meta_keys, TRUE)[$lang];
        $data['page_meta_description'] = $this->page_meta_description = json_decode($page->meta_desc, TRUE)[$lang];
        $data['header_nav'] = 'review';
        $data['sidebar_nav'] = '';
        $data['page'] = ['title' => $this->page_title, 'subtitle' =>  $this->page_subtitle, 'description' => json_decode($page->description, TRUE)[$lang]];
        $data['breadcrumb'][site_url('contact')] = $this->page_title;
        $data['reviews'] = $this->My_Model->get_list('customerreviews', array('stage' => 2), null, 10, $offset, 'id', 'DESC')->result();
        $data['pagination'] = pagination(site_url('review/index/'), $this->My_Model->count_rows('customerreviews', array('stage' => 2)), 10);
        $this->render('review', $data);
    }
    private function __process()
    {
        if ($this->__hasvaliddata()) {
            $customer = $this->Customer_Model->getMe();
            $data = $this->preparePostedData();
            $data['name'] = $customer['name'];
            $data['phone'] = $customer['phone'];
            $data['email'] = $customer['email'];
            $data['created_at'] = time() * 1000;
            $data['updated_at'] = time() * 1000;
            $data['cust_id'] = $customer['id'];
            $data['stage'] = 2;
            $post = $data;

            $post['name'] = json_encode($data['name'], JSON_UNESCAPED_UNICODE);
            $status = $this->db->insert('customerreviews',  $post);
            if ($status) {
                /*
                $to = (object) $data;
                $this->__send_message($to,  lang('new') . ' ' . lang('review'), trim($to->note));
                */
                $this->session->set_flashdata('success_msg', sprintf(lang('contact_mail_sent_success_msg'), ''));
                redirect(site_url('review'));
            } else {
                $this->session->set_flashdata('warning_msg', sprintf(lang('contact_mail_sent_failed_msg'), ''));
            }
        } else {
            $this->session->set_flashdata('warning_msg', sprintf(lang('contact_mail_sent_failed_msg'), ''));
        }
    }
    private function __hasvaliddata()
    {
        $this->form_validation->set_rules('product_rating', lang('rating'), 'trim|required|greater_than_equal_to[1]|less_than_equal_to[5]', array('required' => sprintf(lang('field_required_msg'), lang('rating'))));
        $this->form_validation->set_rules('price_rating', lang('rating'), 'trim|required|greater_than_equal_to[1]|less_than_equal_to[5]', array('required' => sprintf(lang('field_required_msg'), lang('rating'))));
        $this->form_validation->set_rules('service_rating', lang('rating'), 'trim|required|greater_than_equal_to[1]|less_than_equal_to[5]', array('required' => sprintf(lang('field_required_msg'), lang('rating'))));
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
