
<?php

class Pagegalleries extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'pagegalleries';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
    }
    public function validate()
    {
        $this->form_validation->set_rules('title_en', lang('name') . ' ' . lang('english'), 'required',  array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('title_bn', lang('name') . ' ' . lang('bangla'), 'required', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
}
