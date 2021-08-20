
<?php

class Tableplans extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'tableplans';
        $this->_primary_key = 'id';
        $this->_order_by = 'name';
        $this->_order_type = "ASC";
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
        $is_name_unique = $this->check_unique('name', $this->input->post('name')) ? "|is_unique[tableplans.name]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_unique = strtolower($this->input->post('name')) == strtolower($existing->name) ? "" : $is_name_unique;
        }
        $this->form_validation->set_rules('name', lang('name') . ' ' . lang('english'), 'required' . $is_name_unique, array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
}

