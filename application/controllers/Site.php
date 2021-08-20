<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Site extends Public_Layout
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Userhubs');
        $this->load->model('Sites');
        $this->load->model('Pages');
        $this->load->model('Customers');
        $this->load->model('Cookie_Model');
        $this->load->model('Cart');
        $this->load->model('Tablebookings');
        $this->load->model('Dispatchers');
        $this->load->model('Contacts');
        //debugPrint($this->session->userdata('order_receive_date'));
        //$this->load->model('Sales');
        //debugPrint($this->Cart->get_order_receive_date());
    }
    public function english()
    {
        $this->Cookie_Model->set_lang('en');
        redirect(!empty($this->session->tempdata('temp_url')) ? $this->session->tempdata('temp_url') : get_last_page());
    }
    public function bengali()
    {
        $this->Cookie_Model->set_lang('bn');
        redirect(!empty($this->session->tempdata('temp_url')) ? $this->session->tempdata('temp_url') : get_last_page());
    }
    private function prepare($page = null)
    {
        if ($page->status === 'active') {
            $page = (array) $page;
            $lang = lang_option();
            $this->body_class = ["page-home"];
            $data['lang'] = $lang;
            $data['default'] = $this->Pages->get(['default' => true], true)->slug;
            $data['slug'] = $page['slug'];
            $data['isLoggedin'] = $this->isLoggedin;
            $data['page_title'] = $this->page_title = $page['title_' . $lang];
            $data['page_subtitle'] = $this->page_subtitle = $page['subtitle_' . $lang];
            $data['page_meta_keywords'] = $this->page_meta_keywords = $page['meta_keys_' . $lang];
            $data['page_meta_description'] = $this->page_meta_description = $page['meta_desc_' . $lang];
            $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_subtitle, 'description' => $page['description_' . $lang], 'photo' => $page['photo'], 'thumb' => $page['thumb']];
            $data['header_nav'] = $page['slug'];
            $data['sidebar_nav'] = $page['slug'];
            $data['my'] = $this->Customers->getMe();
            $data['store_info'] = config_item('store_type') === 'multiple' ? store_info($this->session->userdata('store_branch') ? $this->session->userdata('store_branch') : null, $lang) : store_info();
            return $data;
        } else {
            redirect('/opps');
        }
    }
    public function index()
    {
        $page = $this->Pages->get(['default' => true], true, true)->slug;
        $this->$page();
    }
    public function subscribe()
    {
        echo $this->Sites->subscribe();
    }
    public function home()
    {
        set_last_page('home');
        $page = $this->Pages->get(['slug' => 'home'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        if (config_item('store_type') === "multiple") $data['stores'] = objectToArray(stores()['list']);
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    public function order($store = 0)
    {
        if ((int) $store > 0) {
            $this->Sites->products($store, true);
            redirect(order_slug());
        }
        set_last_page('order');
        set_page_slug('order');
        $page = $this->Pages->get(['slug' => 'order'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['page_style'] = config_item('product_page_style');
        $this->render('site/' . $page->slug, $data);
    }
    public function booking()
    {
        set_last_page('booking');
        $page = $this->Pages->get(['slug' => 'booking'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        if ($this->input->post() && $this->isLoggedin) {
            if ($this->Tablebookings->validate_booking() && $this->input->post('captcha_total') == $this->session->userdata('captcha_total')) {
                $this->__save_booking();
            } else $this->session->set_flashdata('warning_msg', sprintf(lang('saved_failed_msg'), lang('booking')));
        }
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['dates'] = date_slot(config_item('booking_offest_day'), config_item('booking_max_day'));
        $data['times'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . config_item('store_opening_time'))), date('h:i A', strtotime(date('Y-m-d ') . config_item('store_closing_time'))), 60, 30, false);
        if (config_item('store_type') === 'multiple') {
            $data['stores'] = objectToArray(stores()['list']);
            if (!empty(set_value('hub_id'))) {
                $selected_store = array_filter($data['stores'], function ($v, $k) {
                    return $v['id'] == set_value('hub_id');
                }, ARRAY_FILTER_USE_BOTH);
                if (!empty($selected_store)) $data['selected_store'] = array_values($selected_store)[0];
            }
        }
        $data['captcha_val_1'] = $captcha_val_1 = rand(1, 9);
        $data['captcha_val_2'] = $captcha_val_2 = rand(1, 9);
        $this->session->set_userdata('captcha_total', $captcha_val_1 + $captcha_val_2);
        $this->render('site/' . $page->slug, $data);
    }
    public function contact()
    {
        $page = $this->Pages->get(['slug' => 'contact'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        set_last_page('contact');
        set_page_slug('contact');
        if ($this->input->post() && $this->isLoggedin) {
            if ($this->Contacts->validate() && $this->input->post('captcha_total') == $this->session->userdata('captcha_total')) {
                $post = $this->Contacts->post_entity($this->input->post());
                $entity = $this->Contacts->entity($post);
                $entity['subject'] = "Website Query";
                $entity['to_email'] = $data['store_info']['email'];
                $entity['to_phone'] = $data['store_info']['phone'];
                $entity['to_name'] = config_item('shop_name_en');
                if ($this->Contacts->save($entity)) {
                    echo email(['to' => $entity['to_email'], 'from' => $entity['from_email'], 'from_title' => $entity['to_name'], 'subject' => $entity['subject'], 'message' => $entity['message']]);
                    $this->session->set_flashdata('success_msg', sprintf(lang('sent_option_success'), lang('message')));
                    redirect(current_url());
                }
            } else $this->session->set_flashdata('warning_msg', sprintf(lang('sent_option_fail'), lang('message')));
        }
        $data['captcha_val_1'] = $captcha_val_1 = rand(1, 9);
        $data['captcha_val_2'] = $captcha_val_2 = rand(1, 9);
        $this->session->set_userdata('captcha_total', $captcha_val_1 + $captcha_val_2);
        $data['map_height'] = "300";
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    public function review($offset = 0)
    {
        set_last_page('review');
        $this->load->model('Customerreviews');
        if ($this->input->post() && $this->isLoggedin) {
            if ($this->Customerreviews->validate() && $this->input->post('captcha_total') == $this->session->userdata('captcha_total')) {
                $this->db->trans_start();
                $entity = $this->Customerreviews->entity($this->input->post());
                $entity['cust_id'] = $this->session->userdata('customerId');
                $entity['stage'] = 2;
                $entity['promoted'] = true;
                $id = $this->Customerreviews->save($entity);
                $this->db->trans_complete();
                $status = $this->db->trans_status();
                if ($id && $status) {
                    $this->session->set_flashdata('success_msg', sprintf(lang('sent_option_success'), lang('rating')));
                    redirect(current_url());
                } else  $this->session->set_flashdata('warning_msg', sprintf(lang('saved_failed_msg'), lang('rating')));
            } else $this->session->set_flashdata('warning_msg', sprintf(lang('saved_failed_msg'), lang('rating')));
        }
        $page = $this->Pages->get(['slug' => 'review'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['list'] = $this->Customerreviews->get_by(['stage' => 2], false, false, 10, $offset, null, [], $sortBy = 'id', $sortType = "DESC");
        $data['pagination'] = pagination(site_url('review/'), $this->Customerreviews->count_rows(['stage' => 2]), 10, 'review');
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['captcha_val_1'] = $captcha_val_1 = rand(1, 9);
        $data['captcha_val_2'] = $captcha_val_2 = rand(1, 9);
        $this->session->set_userdata('captcha_total', $captcha_val_1 + $captcha_val_2);
        $this->render('site/' . $page->slug, $data);
    }
    public function gallery($offset = 0)
    {
        set_last_page('gallery');
        $this->load->model('Pagegalleries');
        $page = $this->Pages->get(['slug' => 'gallery'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['list'] = $this->Pagegalleries->get_by(['status' => 'active', 'photo !=' => '', 'thumb !=' => ''], false, false, 9, $offset, null, [], $sortBy = 'id', $sortType = "DESC");
        foreach ($data['list'] as $index => $obj) {
            $data['list'][$index]->title = $this->Pagegalleries->decode($obj->title, true)[$data['lang']];
        }
        $data['pagination'] = pagination(site_url('gallery/'), $this->Pagegalleries->count_rows(['status' => 'active']), 9, 'gallery');
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    public function login()
    {
        if ($this->isLoggedin) {
            $redirect_url = get_last_page();
            $uri = str_replace("//", "/", $redirect_url);
            redirect(strpos($uri, '/login') ? '/' : $redirect_url);
        } else if ($this->input->post() && $this->Customers->validate_login()) {
            $this->Customers->login($this->post_data());
            redirect(current_url());
        }
        $page = $this->Pages->get(['slug' => 'login'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    public function forgot_password()
    {
        if (!$this->isLoggedin) {
            set_last_page('forgot-password');
            if ($this->input->post()) $this->send_otp();
            $page = $this->Pages->get(['slug' => 'login'], true, true);
            $data = !empty($page) ? $this->prepare($page) : [];
            $data['otp_customer'] = $this->session->userdata();
            $data['otp_code'] = $this->session->tempdata('otp_code');
            $data['breadcrumb'][site_url()] = $this->page_title;
            $this->render('site/forgot_password', $data);
        } else  redirect(get_last_page());
    }
    public function send_otp()
    {
        $code = $this->randomcode(4, 'number');
        if ($this->input->post('username')) {
            $customer =  $this->Customers->get_by_key($this->input->post('username'));
            if (!empty($customer)) {
                $this->session->set_userdata('otp_customer', ['id' => $customer->id, 'email' => $customer->email, 'phone' => $customer->phone]);
                $this->session->set_tempdata('otp_code', $code, 30);
                $this->__send_otp();
            } else $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('invalid_field'), $this->lang->line('phone') . '/' . $this->lang->line('email'))));
        } else if ($this->input->post('otp_code')) {
            if ($this->input->post('otp_code') == $this->session->tempdata('otp_code')) {
                $this->session->unset_userdata('otp_customer');
                $this->session->unset_tempdata('otp_code');
                redirect(site_url('login'));
            } else $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('invalid_field'), $this->lang->line('code'))));
        } else {
            if ($this->session->userdata('otp_customer')) {
                $this->session->set_tempdata('otp_code', $code, 30);
                $this->__send_otp();
                redirect(site_url('forgot-password'));
            }
        }
    }
    private function __send_otp()
    {
        $customer = $this->session->userdata('otp_customer');
        $code = $this->session->userdata('otp_code');
        $store_info = store_info();
        if (!empty($customer) && !empty('code')) {
            email(['to' => $customer['email'], 'from' => $store_info['email'], 'from_title' => $store_info['name'], 'subject' => lang('password') . ' ' . lang('recovery'), 'message' => sprintf(lang('option_is_your_option_code'), $code, lang('password') . ' ' . lang('reset')) . '-' . get_domain()]);
            sms($customer['phone'], sprintf(lang('option_is_your_option_code'), $code, lang('password') . ' ' . lang('reset')) . '-' . get_domain());
            $this->session->set_flashdata(array('warning_msg' => sprintf($this->lang->line('option_sent_option'), lang('code'), lang('phone') . ' / ' . lang('email'))));
        }
    }
    public function signup()
    {
        if ($this->isLoggedin) {
            $redirect_url = get_last_page();
            $uri = str_replace("//", "/", $redirect_url);
            redirect(strpos($uri, '/signup') ? '/' : $redirect_url);
        } else if ($this->input->post() && $this->Customers->validate_signup() && $this->input->post('captcha_total') == $this->session->userdata('captcha_total')) {
            $status = $this->Customers->signup($this->Customers->entity($this->input->post()));
            if (!$status) $this->session->set_flashdata('warning_msg', sprintf(lang('option_failed'), lang('registration')));
            else if ($status === 'account_exists') $this->session->set_flashdata('warning_msg', sprintf(lang('option_failed'), lang('registration')) . ' ' . sprintf(lang('field_already_in_use_msg'), lang('phone')));
            else redirect(current_url());
        }
        $page = $this->Pages->get(['slug' => 'signup'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['captcha_val_1'] = $captcha_val_1 = rand(1, 9);
        $data['captcha_val_2'] = $captcha_val_2 = rand(1, 9);
        $this->session->set_userdata('captcha_total', $captcha_val_1 + $captcha_val_2);
        set_last_page('signup');
        $this->render('site/' . $page->slug, $data);
    }
    public function checkout()
    {
        set_last_page('order/checkout');
        if ($this->isLoggedin) {
            if ($this->Cart->allow_checkout()) {
                set_page_slug('checkout');
                if ($this->input->post()) {
                    $data = $this->input->post();
                    if ($data['payment_type'] === 'COD' || $data['payment_type'] === 'EFT') {
                        $this->Cart->set_checkout_info($data);
                        if ($data['payment_type'] === 'COD') $this->Sites->checkout();
                    }
                }
                $page = $this->Pages->get(['slug' => 'checkout'], true, true);
                $data = !empty($page) ? $this->prepare($page) : [];
                $data['breadcrumb'][site_url()] = $this->page_title;
                $data['page_style'] = config_item('product_page_style');
                $this->render('site/' . $page->slug, $data);
            } else redirect('order');
        } else redirect('login');
    }
    private function __save_booking()
    {
        if (verify_date('Y-m-d h:i A', $this->input->post('date') . ' ' . $this->input->post('time')) && (strtotime($this->input->post('date') . ' ' . $this->input->post('time')) > time())) {
            $this->db->trans_start();
            $entity = $this->Tablebookings->post_entity($this->input->post());
            $entity['cust_id'] = $this->session->userdata('customerId');
            $booking_entity = $this->Tablebookings->entity($entity);
            $AsapTime = new DateTime($entity['date'] . ' ' . $entity['time']);
            $booking_entity['sit_time'] = $AsapTime->format('Y-m-d H:i:s');
            $booking_entity['stage'] = 1;
            if (empty($booking_entity['hub_id'])) $booking_entity['hub_id'] =  $this->Userhubs->get(['default' => true], true, true)->id;;
            $booking_entity['lang'] = lang_option();
            $id = $this->Tablebookings->save($booking_entity);
            $this->Dispatchers->save(['status' => 'await', 'event' => 'booking', 'document' => 'invoice', 'hub_id' =>  $booking_entity['hub_id'], 'booking_id' => $id]);
            $this->db->trans_complete();
            $status = $this->db->trans_status();
            if ($id && $status) {
                $this->session->set_flashdata('success_msg', sprintf(lang('sent_option_success'), lang('booking')) . ' ' . sprintf(lang('confirmation_msg_warning'), lang('restaurant')));
                redirect(current_url());
            } else  $this->session->set_flashdata('warning_msg', sprintf(lang('saved_failed_msg'), lang('booking')));
        } else  $this->session->set_flashdata('warning_msg', sprintf(lang('your_option_is_incorrect'), lang('time')));
    }
    public function privacypolicy()
    {
        set_last_page('privacy-policy');
        $page = $this->Pages->get(['slug' => 'privacypolicy'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    public function termsofuse()
    {
        set_last_page('terms-of-use');
        $page = $this->Pages->get(['slug' => 'termsofuse'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    public function cookiepolicy()
    {
        set_last_page('cookie-policy');
        $page = $this->Pages->get(['slug' => 'cookiepolicy'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('site/' . $page->slug, $data);
    }
    private function keep()
    {
        $this->output->cache(30);
    }
    public function clear()
    {
        $this->output->delete_cache('/home');
        $this->output->delete_cache('/order');
        $this->output->delete_cache('/gallery');
    }
}
