<?php

class Expensesectors extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'expensesectors';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->load->model('Userhubs');
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en'), ['hub_id' => $this->input->post('hub_id')]) ? "|is_unique[expensesectors.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn'), ['hub_id' => $this->input->post('hub_id')]) ? "|is_unique[expensesectors.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), true);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('sector') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('sector') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }

    public function arrange_entity($entity)
    {
        if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) $entity['name'] = $this->encode(['en' => $entity['name_en'], 'bn' => $entity['name_bn']]);
        $order_by = $this->orderBy();
        if (!empty($entity['id']) && (!empty($entity[$order_by]) && $order_by === 'order')) {
            $old_order = $this->get($entity['id'], TRUE)->order;
            if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
            unset($entity[$order_by]);
        } else {
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
            if (empty($entity['hub_id'])) $entity['hub_id'] = getallheaders()['hub_id'];
        }
        return $entity;
    }

    public function build_entity($entity)
    {
        $entity->name = $this->decode($entity->name);
        $entity->hub = $this->Userhubs->build_entity($this->Userhubs->get($entity->hub_id, true, true));
        return $entity;
    }
    public function build_config($hub_id)
    {
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false);
        foreach ($results['hubs'] as $index => $hub) {
            $results['hubs'][$index] = $this->Userhubs->build_entity($hub);
            if ($hub_id == $hub->id) $results['active_hub'] = $results['hubs'][$index];
        }
        return $results;
    }
}
