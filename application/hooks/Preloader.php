<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Preloader
{

    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Cfgservices');
        $this->CI->load->model('Openings');
    }

    function load($language = 'english')
    {
        foreach ($this->CI->Appconfig->get_all()->result() as $app_config) $this->CI->config->set_item($app_config->key, $app_config->value);
        $this->CI->config->set_item('delivery', $this->CI->Cfgservices->get(['value' => 'delivery', 'status' => 'active'], true));
        $this->CI->config->set_item('collection', $this->CI->Cfgservices->get(['value' => 'collection', 'status' => 'active'], true));
        $this->CI->config->set_item('opening_hour', $this->CI->Openings->get(['day' => date('D')], true, true));
        $language = $this->CI->Cookie_Model->get_lang() === "bn" ? 'bengali' : 'english';
        $map = directory_map(APPPATH . 'language/' . $language);
        foreach ($map as $file) {
            if (!is_array($file) && substr(strrchr($file, '.'), 1) == "php") $this->CI->lang->load(str_replace('_lang.php', '', $file), $language);
        }
        if (config_item('timezone')) date_default_timezone_set(config_item('timezone'));
        else  date_default_timezone_set('Asia/Dhaka');
        if (empty($this->CI->session->userdata('customerId')) && !empty($this->CI->Cookie_Model->get_foodie())) $this->CI->session->set_userdata('customerId', $this->CI->Cookie_Model->get_foodie());
        if (empty($this->CI->session->userdata('store_branch')) && !empty($this->CI->Cookie_Model->get_store())) $this->CI->session->set_userdata('store_branch', $this->CI->Cookie_Model->get_store());
        if (empty($this->CI->session->userdata('delivery_area')) && !empty($this->CI->Cookie_Model->get_delivery_area())) $this->CI->session->set_userdata('delivery_area', $this->CI->Cookie_Model->get_delivery_area());
        if (empty($this->CI->session->userdata('delivery_home')) && !empty($this->CI->Cookie_Model->get_delivery_home())) $this->CI->session->set_userdata('delivery_home', $this->CI->Cookie_Model->get_delivery_home());
        if (empty($this->CI->session->userdata('order_receive_date'))) $this->CI->session->set_userdata('order_receive_date', date('Y-m-d'));
    }
}
