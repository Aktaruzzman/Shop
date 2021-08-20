
<?php

class Cfgdelplans extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'cfgdelplans';
        $this->_primary_key = 'id';
        $this->_order_by = 'area_id';
        $this->_order_type = "ASC";
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->load->model('Cfgdelareas');
        $this->load->model('Geoareas');
        $this->load->model('Cfgservices');
        $this->load->model('Userhubs');
    }

    public function validate()
    {
        $unique_condition = ['area_id' => $this->input->post('area_id'), 'hub_id' => $this->input->post('hub_id')];
        $unique_condition['pos'] = !empty($this->input->post('pos')) ? true : false;
        $unique_condition['web'] = !empty($this->input->post('web')) ? true : false;
        $is_base_unique = $this->check_unique('base', $this->input->post('base'), $unique_condition) ? "|is_unique[Cfgdelplans.base]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), true);
            $is_base_unique = (float) $existing->base === (float)$this->input->post('base') ? "" :  $is_base_unique;
        }
        $this->form_validation->set_rules('area_id', lang('area'), 'required|integer', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('hub_id', lang('hub'), 'required|integer', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('delay', lang('delivery') . ' ' . lang('time'), 'required|integer', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('base', lang('order') . ' ' . lang('amount'), 'required|numeric' . $is_base_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('charge', lang('delivery') . ' ' . lang('fee'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('free_on', lang('free_on'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('consider_on', lang('consider_on'), 'required|numeric', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {

        $entity['pos'] = !empty($entity['pos']) ? true : false;
        $entity['web'] = !empty($entity['web']) ? true : false;
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
        $entity->area = $this->Geoareas->formatted($entity->area_id);
        $entity->hub = $this->Userhubs->build_entity($this->Userhubs->get($entity->hub_id, true, true));
        return $entity;
    }

    public function build_config($hub_id = null)
    {
        $condition['status'] = 'active';
        if ($hub_id) $condition['hub_id'] = $hub_id;
        $results['areas'] = $this->Cfgdelareas->get_by($condition, false, true);
        foreach ($results['areas'] as $index => $hub) {
            $results['areas'][$index] = $this->Cfgdelareas->build_entity($hub);
        }
        $results['service'] = $this->Cfgservices->get_by(['value' => 'delivery'], true, true, ['id', 'name', 'value', 'delay']);
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false);
        foreach ($results['hubs'] as $index => $hub) {
            $results['hubs'][$index] = $this->Userhubs->build_entity($hub);
            if ($hub_id == $hub->id) $results['active_hub'] = $results['hubs'][$index];
        }
        return $results;
    }
}
