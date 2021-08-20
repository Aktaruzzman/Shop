
<?php

class Geodistricts extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'geodistricts';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
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
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en'), ['division_id' => $this->input->post('division_id')]) ? "|is_unique[geodistricts.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn'), ['division_id' => $this->input->post('division_id')]) ? "|is_unique[geodistricts.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('district') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('district') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('division_id', lang('division'), 'required|integer', array(
            'required' => lang('field_required_msg'),
        ));
        return $this->form_validation->run();
    }
}
