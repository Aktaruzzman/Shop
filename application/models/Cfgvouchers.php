
<?php

class Cfgvouchers extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'cfgvouchers';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = 'DESC';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
    }

    public function validate()
    {
        $is_code_unique = $this->check_unique('code', $this->input->post('code')) ? "|is_unique[cfgvouchers.code]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_code_unique = (float) $existing->code === (float)$this->input->post('code') ? "" :  $is_code_unique;
        }
        $this->form_validation->set_rules('code', lang('code'), 'required' . $is_code_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric', array('required' => lang('field_required_msg')));
        if (empty($this->input->post('id'))) $this->form_validation->set_rules('payment', lang('payment'), 'required|in_list[Cash,Card,Bikash,Nagod,Rocket,Qcash,Cheque,Voucher]', array('required' => lang('field_required_msg')));
        if (empty($this->input->post('id'))) $this->form_validation->set_rules('source', lang('source'), 'required|in_list[till,other]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name', lang('customer') . ' ' . lang('name'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('phone', lang('phone'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('start', lang('start') . ' ' . lang('date'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('end', lang('end') . ' ' . lang('date'), 'required|numeric', array('required' => lang('field_required_msg')));
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
            if (!empty($order_by) && $order_by === 'order')  $entity[$order_by] = $this->get_max($order_by) + 1;
            $entity['balance'] = $entity['amount'];
            $entity['closed_on'] = closed_on();
        }
        return $entity;
    }
}
