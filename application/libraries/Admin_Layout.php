<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_Layout extends MY_Controller
{

    protected $robots = 'index,follow';
    protected $site_title = "";
    protected $page_title = '';
    protected $page_subtitle = '';
    protected $page_meta_keywords = '';
    protected $page_meta_description = '';
    protected $body_class = array();
    protected $page_heading = '';
    protected $isLoggedin = false;

    public function __construct()
    {
        parent::__construct();
        $this->template->set_layout('admin_layout');
        $this->isLoggedin = $this->My_Model->is_admin_loggedin();
        $this->site_title = config_item('shop_name_' . lang_option());
    }

    public function render($page, $data = array())
    {
        $this->template
            ->set_metadata('robots', $this->robots)
            ->set_metadata('keywords', $this->page_meta_keywords)
            ->set_metadata('description', $this->page_meta_description)
            ->set_metadata('canonical', site_url($this->uri->uri_string()), 'link')
            ->title($this->page_title, $this->site_title)
            ->set('body_class', implode(' ', $this->body_class))
            ->set_partial('header', 'partials/header')
            ->set_partial('flash', 'partials/flash')
            ->set_partial('footer', 'partials/footer')
            ->build($page, $data);
    }
}

