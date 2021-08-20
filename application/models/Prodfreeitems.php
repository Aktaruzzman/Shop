
<?php

class Prodfreeitems extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodfreeitems';
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
        if (($this->input->post('plan_id'))  && ($this->input->post('item_id') || $this->input->post('option_id') || $this->input->post('topping_id') || $this->input->post('modify_id'))) {
            $existing = !empty($this->input->post('id')) ? $this->get($this->input->post('id'), TRUE) : NULL;
            if (!empty($this->input->post('topping_id'))) {
                $is_topping_unique = $this->check_unique('topping_id', $this->input->post('topping_id'), ['plan_id' => $this->input->post('plan_id')]) ? "|is_unique[prodfreeitems.topping_id]" : "";
                $is_topping_unique = !empty($existing) && $existing->topping_id == $this->input->post('topping_id') ? "" : $is_topping_unique;
                $this->form_validation->set_rules('topping_id', lang('topping'), 'numeric' . $is_topping_unique);
            } else if (!empty($this->input->post('modify_id'))) {
                $is_modify_unique = $this->check_unique('modify_id', $this->input->post('modify_id'), ['plan_id' => $this->input->post('plan_id')]) ? "|is_unique[prodfreeitems.modify_id]" : "";
                $is_modify_unique = !empty($existing) && $existing->modify_id == $this->input->post('modify_id') ? "" : $is_modify_unique;
                $this->form_validation->set_rules('modify_id', lang('modify'), 'numeric' . $is_modify_unique);
            } else if (!empty($this->input->post('option_id'))) {
                $is_option_unique = $this->check_unique('option_id', $this->input->post('option_id'), ['plan_id' => $this->input->post('plan_id'), 'item_id' => $this->input->post('item_id')]) ? "|is_unique[prodfreeitems.option_id]" : "";
                $is_option_unique = !empty($existing) && $existing->option_id == $this->input->post('option_id') ? "" : $is_option_unique;
                $this->form_validation->set_rules('option_id', lang('option'), 'numeric' . $is_option_unique);
            } else {
                $is_item_unique = $this->check_unique('item_id', $this->input->post('item_id'), ['plan_id' => $this->input->post('plan_id')]) ? "|is_unique[prodfreeitems.item_id]" : "";
                $is_item_unique = !empty($existing) && $existing->item_id == $this->input->post('item_id') ? "" : $is_item_unique;
                $this->form_validation->set_rules('item_id', lang('item'), 'numeric' . $is_item_unique);
            }
            $this->form_validation->set_rules('plan_id', lang('item'), 'required|numeric', array('required' => lang('field_required_msg')));
            return $this->form_validation->run();
        }
        return false;
    }
}
