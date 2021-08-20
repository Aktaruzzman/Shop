
<?php

class Subscribers extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'subscribers';
        $this->_primary_key = 'id';
        $this->_order_by = 'phone';
        $this->_order_type = "ASC";
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
        $is_phone_unique = $this->check_unique('phone', $this->input->post('phone')) ? "|is_unique[subscribers.phone]" : "";
        $is_email_unique = $this->check_unique('email', $this->input->post('email')) ? "|is_unique[subscribers.email]" : "";
        if (!empty($this->input->post('email'))) {
            $this->form_validation->set_rules('email', lang('email'), 'valid_email' . $is_email_unique, array('required' => lang('field_required_msg')));
        }
        $this->form_validation->set_rules('phone', lang('phone'), 'required' . $is_phone_unique, array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }

}
