
<?php

class Prodfreeplans extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodfreeplans';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
        $this->_order_type = 'ASC';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
    }
    public function validate()
    {

        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en')) ? "|is_unique[prodfreeplans.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn')) ? "|is_unique[prodfreeplans.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('name') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('name') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('min_order', lang('minimum') . ' ' . lang('order'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('quantity', lang('order'), 'required|numeric');
        $this->form_validation->set_rules('date_start', lang('date') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('date_end', lang('date') . ' ' . lang('end'), 'required');
        $this->form_validation->set_rules('time_start', lang('time') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('time_end', lang('time') . ' ' . lang('end'), 'required');
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
}
