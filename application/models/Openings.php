
<?php

class Openings extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'openings';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
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
        if (!empty($this->input->post('id'))) {
            $this->form_validation->set_rules('id', lang('day'), 'required');
            $this->form_validation->set_rules('start', lang('start'), 'required');
            $this->form_validation->set_rules('end', lang('end'), 'required');
            return $this->form_validation->run();
        }
        return false;
    }
}
