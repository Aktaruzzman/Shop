<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Customer extends Public_Layout
{

    private $__customer = null;

    function __construct()
    {
        parent::__construct();
        if (!$this->isLoggedin)   redirect(site_url());
        $this->load->model('Pages');
        $this->load->model('Sales');
        $this->load->model('Saledetails');
        $this->load->model('Uploader');
        $this->load->model('Customers');
        $this->load->model('Geoareas');
        $this->load->model('Customeraddresses');
        $this->__customer = $this->Customers->getMe();
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
            $data['store_info'] = config_item('store_type') === 'multiple' ? store_info($this->session->userdata('store_branch') ? $this->session->userdata('store_branch') : null, $lang) : store_info();
            return $data;
        } else {
            redirect('/opps');
        }
    }

    function index()
    {
        set_last_page('customer');
        $page = $this->Pages->get(['slug' => 'customer'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $this->robots = "noindex,nofollow";
        $this->body_class = ["page-customer-index"];
        $data['my'] = $this->__customer;
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['sidebar_nav'] = 'dashboard';
        $this->render('customer/index', $data);
    }
    function orderlist($offset = 0)
    {

        set_last_page('customer/orderlist');
        $page = $this->Pages->get(['slug' => 'customer'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $this->robots = "noindex,nofollow";
        $this->body_class = ["page-customer-orderlist"];
        $data['sidebar_nav'] = 'orderlist';
        $data['my'] = $this->__customer;
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['orderlist'] = $this->Sales->get_by(['cust_id' => $this->session->userdata('customerId')], false, false, 10, $offset, null, [], $sortBy = 'id', $sortType = "DESC");
        $data['pagination'] = pagination(site_url('customer/orderlist'), $this->Sales->count_rows(['cust_id' => $this->session->userdata('customerId')]), 10, 'customer/orderlist');
        $this->render('customer/orderlist', $data);
    }
    function orderdetail($id = 0)
    {
        if (!empty($this->Sales->get($id))) {
            set_last_page('customer/orderdetail/' . $id);
            $page = $this->Pages->get(['slug' => 'customer'], true, true);
            $data = !empty($page) ? $this->prepare($page) : [];
            $this->robots = "noindex,nofollow";
            $this->body_class = ["page-home"];
            $data['sidebar_nav'] = 'orderlist';
            $data['my'] = $this->__customer;
            $data['sale'] = objectToArray($this->__get_sale($id));
            $data['breadcrumb'][site_url()] = $this->page_title;
            $this->render('customer/orderdetail', $data);
        } else redirect(get_last_page());
    }
    private function __get_sale($id)
    {
        $order = $this->Sales->get($id, true, true);
        $order->cust_meta = $this->Sales->decode($order->cust_meta);
        if (!empty($order->promo_meta)) $this->Sales->decode($order->promo_meta);
        $sale_details = $this->Saledetails->get_by(['sale_id' => $id], false, false);
        foreach ($sale_details as $index => $datum) {
            $datum->name = $this->Saledetails->decode($datum->name);
            $datum->category_name = $this->Saledetails->decode($datum->category_name);
            $datum->item_name = $this->Saledetails->decode($datum->item_name);
            $datum->option_name = $this->Saledetails->decode($datum->option_name);
            $datum->course_name = $this->Saledetails->decode($datum->course_name);
            $datum->unit_name = $this->Saledetails->decode($datum->unit_name);
            $datum->tax_rule = $this->Saledetails->decode($datum->tax_rule);
            $datum->discount_rule = $this->Saledetails->decode($datum->discount_rule);
            $datum->sides = !empty($datum->sides) ? $this->Saledetails->decode($datum->sides) : [];
            $sale_details[$index] = $datum;
        }
        $order->details = $sale_details;
        return $order;
    }
    function profile()
    {
        set_last_page('customer/profile');
        $page = $this->Pages->get(['slug' => 'customer'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $this->robots = "noindex,nofollow";
        $this->body_class = ["page-customer-index"];
        $data['my'] = $this->__customer;
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['sidebar_nav'] = 'profile';
        if ($this->input->post()) {
            $this->__save_profile(array('name' => json_encode(array('en' => $this->input->post('name_en'), 'bn' => $this->input->post('name_bn')), JSON_UNESCAPED_UNICODE)));
            redirect(site_url('customer/profile'));
        }
        $this->render('customer/profile', $data);
    }

    private function __save_profile($saving)
    {
        if (!empty($_FILES['photo']) && !empty($_FILES['photo']['name'])) {
            $config = array('upload_path' => APPPATH . '../uploads/customer', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'max_size' => 0, 'max_width' => 0, 'max_height' => 0, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : '';
            if ($upload_status) {
                $saving['thumb'] = $saving['photo'];
                $this->Uploader->resize_image(APPPATH . '../uploads/customer/' . $upload_data['file_name'], 250);
                $this->Uploader->create_thumbnail(APPPATH . '../uploads/customer/' . $upload_data['file_name'], APPPATH . '../uploads/customer/thumbnail/' . $upload_data['file_name'], 75, 50);
                $customer = $this->Customers->get($this->session->userdata('customerId'), true, true);
                $this->Customers->save($saving, $customer->id);
                $this->Uploader->unlink_image(APPPATH . "../uploads/customer/", $customer->photo);
                $this->Uploader->unlink_image(APPPATH . "../uploads/customer/thumbnail/", $customer->photo);
            }
            $data['errors'] = $this->upload->display_errors();
            $data['file'] = $saving['photo'];
        } else  $this->Customers->save($saving, $this->session->userdata('customerId'));
    }

    function point()
    {
        set_last_page('customer');
        $page = $this->Pages->get(['slug' => 'customer'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $this->robots = "noindex,nofollow";
        $this->body_class = ["page-customer-index"];
        $data['my'] = $this->__customer;
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['sidebar_nav'] = 'point';
        $this->render('customer/point', $data);
    }

    function changepassword()
    {

        set_last_page('customer/changepassword');
        $page = $this->Pages->get(['slug' => 'customer'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $this->robots = "noindex,nofollow";
        $this->body_class = ["page-customer-index"];
        $data['my'] = $this->__customer;
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['sidebar_nav'] = 'changepassword';

        if ($this->input->post()) {
            $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]', array('required' => lang('field_required_msg'), 'min_length' => sprintf(lang('min_error_msg'), 8)));
            $this->form_validation->set_rules('retype_password', lang('retype') . ' ' . lang('password'), 'required|min_length[8]', array('required' => lang('field_required_msg'), 'min_length' => sprintf(lang('min_error_msg'), 8), 'matches' => lang('equalto_error_msg')));
            if ($this->form_validation->run() === FALSE) $this->session->set_flashdata('warning_msg', '<i class="fa fa-warning">&nbsp;</i>' . sprintf(lang('saved_failed_msg'), lang('password')));
            else $this->Customers->save(array('password' => md5($this->input->post('password'))), $this->session->userdata('customerId')) ? $this->session->set_flashdata('warning_msg', '<i class="fa fa-check">&nbsp;</i>' . sprintf(lang('saved_success_msg'), lang('password'))) : $this->session->set_flashdata('warning_msg', '<i class="fa fa-warning">&nbsp;</i>' . sprintf(lang('saved_failed_msg'), lang('password')));
            redirect(site_url('customer/changepassword'));
        }
        $this->render('customer/changepassword', $data);
    }

    function addressbook($id = 0)
    {
        set_last_page('customer/addressbook');
        $page = $this->Pages->get(['slug' => 'customer'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $this->robots = "noindex,nofollow";
        $this->body_class = ["page-customer-index"];
        $data['my'] = $this->__customer;
        $data['areas'] = $this->Geoareas->get(['status' => 'active', 'deleted_at' => false], FALSE, TRUE);
        foreach ($data['areas'] as $k => $obj) $data['areas'][$k] = $this->Geoareas->entity_area($obj);
        $data['addresses'] = $this->Customeraddresses->get(['cust_id' => $this->__customer['id']], FALSE, TRUE);
        $data['entity'] = [];
        foreach ($data['addresses'] as $k => $obj) {
            $data['addresses'][$k]->label = $this->Geoareas->decode($obj->label);
            $data['addresses'][$k]->house = $this->Customeraddresses->decode($obj->house);
            $data['addresses'][$k]->area = $this->Geoareas->formatted($obj->area_id);
            if ($id == $obj->id)  $data['entity'] = objectToArray($data['addresses'][$k]);
        }
        $data['id'] = $id;
        $data['areas'] = objectToArray($data['areas']);
        $data['addresses'] = objectToArray($data['addresses']);
        $data['breadcrumb'][site_url()] = $this->page_title;
        $data['sidebar_nav'] = 'addressbook';
        if ($this->input->post()) {
            if ($this->Customeraddresses->validate()) {
                $post = $this->input->post();
                $post['label'] = $this->Customeraddresses->encode(['en' => $post['label_en'], 'bn' => $post['label_bn']]);
                $post['house'] = $this->Customeraddresses->encode(['en' => $post['house_en'], 'bn' => $post['house_bn']]);
                !empty($post['id']) ? $this->Customeraddresses->save($post, $post['id']) : $this->Customeraddresses->save($post);
                redirect('customer/addressbook');
            }
        }
        $this->render('customer/addressbook', $data);
    }

    function logout()
    {
        if ($this->Customers->loggedout()) {
            $this->Cookie_Model->clear_foodie();
            redirect(site_url());
        }
    }
}
