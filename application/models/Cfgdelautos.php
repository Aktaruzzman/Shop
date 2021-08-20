
<?php

class Cfgdelautos extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'cfgdelautos';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = "ASC";
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Userhubs');
    }
    public function validate()
    {
        $is_hub_unique = $this->check_unique('hub_id', $this->input->post('hub_id')) ? "|is_unique[cfgdelautos.hub_id]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_hub_unique = (float) $existing->hub_id === (float)$this->input->post('hub_id') ? "" :  $is_hub_unique;
        }
        $this->form_validation->set_rules('hub_id', lang('hub'), 'required|numeric' . $is_hub_unique, array('required' => lang('field_required_msg')));
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
            if (empty($entity['hub_id'])) $entity['hub_id'] = getallheaders()['hub_id'];
        }
        return $entity;
    }
    public function build_entity($entity)
    {
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
