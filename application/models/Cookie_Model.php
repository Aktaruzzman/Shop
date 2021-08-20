<?php

class Cookie_Model extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->library('encryption');
    }

    function set_foodie()
    {
        $expire = 365 * 24 * 3600;
        set_cookie("__foodie", $this->encryption->encrypt($this->session->userdata('customerId')), $expire);
    }
    function get_foodie()
    {
        return $this->encryption->decrypt(get_cookie('__foodie'));
    }
    function clear_foodie()
    {
        delete_cookie("__foodie");
    }

    function set_lang($language)
    {
        $expire = 365 * 24 * 3600;
        set_cookie("__lang", $this->encryption->encrypt($language), $expire);
    }
    function get_lang()
    {
        if (!empty($this->encryption->decrypt(get_cookie('__lang')))) {
            return $this->encryption->decrypt(get_cookie('__lang'));
        } else {
            $this->set_lang('en');
            return 'en';
        }
    }
    function clear_lang()
    {
        delete_cookie("__lang");
    }
    function set_store()
    {
        $expire = 365 * 24 * 3600;
        set_cookie("__store", $this->encryption->encrypt($this->session->userdata('store_branch')), $expire);
    }
    function get_store()
    {
        return $this->encryption->decrypt(get_cookie('__store'));
    }
    function clear_store()
    {
        delete_cookie("__store");
    }
    function set_delivery_area()
    {
        $expire = 365 * 24 * 3600;
        set_cookie("__area", $this->encryption->encrypt($this->session->userdata('delivery_area')), $expire);
    }
    function get_delivery_area()
    {
        return $this->encryption->decrypt(get_cookie('__area'));
    }
    function clear_delivery_area()
    {
        delete_cookie("__area");
    }

    function set_delivery_home()
    {
        $expire = 365 * 24 * 3600;
        set_cookie("__home", $this->encryption->encrypt($this->session->userdata('delivery_home')), $expire);
    }
    function get_delivery_home()
    {
        return $this->encryption->decrypt(get_cookie('__home'));
    }
    function clear_delivery_home()
    {
        delete_cookie("__home");
    }
}
