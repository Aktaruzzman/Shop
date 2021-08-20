
<?php

class Prodcustsets extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodcustsets';
        $this->_primary_key = 'id';
        $this->_order_by = 'level';
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
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en'), ['hook_item_id' => $this->input->post('hook_item_id'), 'hook_option_id' => $this->input->post('hook_option_id')]) ? "|is_unique[prodcustsets.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn'), ['hook_item_id' => $this->input->post('hook_item_id'), 'hook_option_id' => $this->input->post('hook_option_id')]) ? "|is_unique[prodcustsets.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('set') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('set') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('hook_item_id', lang('item'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('hook_option_id', lang('option'), 'numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('level', lang('level'), 'required|numeric', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
}
