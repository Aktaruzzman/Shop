
<?php

class Cfgdelareas extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'cfgdelareas';
        $this->_primary_key = 'id';
        $this->_order_by = 'hub_id';
        $this->_order_type = "ASC";
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->_group_by = array("area_id");
        $this->load->model('Geoareas');
        $this->load->model('Cfgservices');
        $this->load->model('Userhubs');
    }
    public function validate()
    {
        $unique_condition = ['area_id' => $this->input->post('area_id')];
        $unique_condition['hub_id'] = $this->input->post('hub_id');
        $is_area_unique = $this->check_unique('area_id', $this->input->post('area_id'), $unique_condition) ? "|is_unique[cfgdelareas.area_id]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), true);
            $is_area_unique = ((int) $existing->area_id === (int)$this->input->post('area_id') && (int) $existing->hub_id === (int)$this->input->post('hub_id')) ? "" :  $is_area_unique;
        }
        $this->form_validation->set_rules('area_id', lang('area'), 'required|numeric' . $is_area_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('hub_id', lang('hub'), 'required|numeric', array('required' => lang('field_required_msg')));
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
    public function build_entity($entity)
    {
        $entity->area = $this->Geoareas->formatted($entity->area_id)->formatted;
        $entity->hub = $this->Userhubs->build_entity($this->Userhubs->get($entity->hub_id, true, true));
        return $entity;
    }
    public function build_config($hub_id)
    {
        $results['areas'] = $this->Geoareas->list_areas(null);
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false);
        foreach ($results['hubs'] as $index => $hub) {
            $results['hubs'][$index] = $this->Userhubs->build_entity($hub);
            if ($hub_id == $hub->id) $results['active_hub'] = $results['hubs'][$index];
        }
        return $results;
    }
}
