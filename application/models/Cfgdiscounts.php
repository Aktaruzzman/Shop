
<?php

class Cfgdiscounts extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'cfgdiscounts';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
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
        $is_value_unique = $this->check_unique('value', $this->input->post('value'), ['func' => $this->input->post('func')]) ? "|is_unique[cfgdiscounts.value]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_value_unique = (float) $existing->value === (float)$this->input->post('value') ? "" :  $is_value_unique;
        }
        $this->form_validation->set_rules('func', lang('function'), 'required|in_list[percent,fixed]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('value', lang('discount'), 'required|numeric' . $is_value_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        $order_by = $this->orderBy();
        if (!empty($entity['id']) && (!empty($entity[$order_by]) && $order_by === 'order')) {
            $old_order = $this->get($entity['id'], TRUE, TRUE)->order;
            if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
            unset($entity[$order_by]);
        } else {
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
        }
        return $entity;
    }
}
