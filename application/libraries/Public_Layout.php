<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Public_Layout extends MY_Controller
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
        $this->isLoggedin = $this->My_Model->isLoggedin();
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
            ->set('og_title', $this->page_title)
            ->set('og_keys', $this->page_meta_keywords)
            ->set('og_desc', $this->page_meta_description)
            ->set('og_img', ASSET_PATH . 'img/meta.jpg')
            ->set('og_site_name', $this->site_title)
            ->set('og_email', config_item('shop_email'))
            ->set('og_phone_number', config_item('shop_phone'))
            ->build($page, $data);
    }
}
