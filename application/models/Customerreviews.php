
<?php

class Customerreviews extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'customerreviews';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
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
        $this->form_validation->set_rules('product_rating', lang('product') . ' ' . lang('rating'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('service_rating', lang('service') . ' ' . lang('rating'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('price_rating', lang('price') . ' ' . lang('rating'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('note', lang('customer') . ' ' . lang('note'), 'required', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        $order_by = $this->orderBy();
        if (!empty($entity['id']) && (!empty($entity[$order_by]) && $order_by === 'order')) {
            $old_order = $this->get($entity['id'], TRUE)->order;
            if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
            unset($entity[$order_by]);
        } else {
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
        }
        return $entity;
    }
}
