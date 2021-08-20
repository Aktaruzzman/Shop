
<?php

class Contacts extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'contacts';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
    }
    public function validate()
    {
        if (array_key_exists('from_name', $this->input->post())) $this->form_validation->set_rules('from_name', lang('sender'), 'required', array('required' => lang('field_required_msg')));
        if (array_key_exists('from_email', $this->input->post())) $this->form_validation->set_rules('from_email', lang('email'), 'required', array('required' => lang('field_required_msg')));
        if (array_key_exists('from_phone', $this->input->post())) $this->form_validation->set_rules('from_phone', lang('phone'), 'required', array('required' => lang('field_required_msg')));
        if (array_key_exists('subject', $this->input->post())) $this->form_validation->set_rules('subject', lang('subject'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('message', lang('message'), 'required', array('required' => lang('field_required_msg')));
        if (array_key_exists('captcha_total', $this->input->post())) $this->form_validation->set_rules('captcha_total', lang('total'), 'required', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
}
