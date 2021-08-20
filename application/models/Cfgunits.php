
<?php

class Cfgunits extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'cfgunits';
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
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en')) ? "|is_unique[cfgunits.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn')) ? "|is_unique[cfgunits.name_bn]" : "";
        $is_short_en_unique = $this->check_unique('short_en', $this->input->post('short_en')) ? "|is_unique[cfgunits.short_en]" : "";
        $is_short_bn_unique = $this->check_unique('short_bn', $this->input->post('short_bn')) ? "|is_unique[cfgunits.short_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
            $is_short_en_unique = strtolower($this->input->post('short_en')) == strtolower($existing->short_en) ? "" : $is_short_en_unique;
            $is_short_bn_unique = trim($this->input->post('short_bn')) == trim($existing->short_bn) ? "" : $is_short_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('name') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('name') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('short_en', lang('short') . ' ' . lang('english'), 'required' . $is_short_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('short_bn', lang('short') . ' ' . lang('bangla'), 'required' . $is_short_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
            $entity['name_en'] = strtolower($entity['name_en']);
            $entity['name'] = $this->encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']));
        }
        if (array_key_exists('short_en', $entity) && array_key_exists('short_bn', $entity)) {
            $entity['short_en'] = strtolower($entity['short_en']);
            $entity['short'] = $this->encode(array('en' => $entity['short_en'], 'bn' => $entity['short_bn']));
        }
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
    public function build_entity($entity)
    {
        if (!empty($entity)) {
            $entity->name = $this->decode($entity->name);
            $entity->short = $this->decode($entity->short);
        }
        return $entity;
    }
}
