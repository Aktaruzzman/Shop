
<?php

class Empattendances extends MY_Model
{
    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'empattendance';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Userhubs');
        $this->load->model('Employees');
    }
    public function validate()
    {
        if ($this->input->post('id') && $this->input->post('emp_id')) {
            return true;
        }
        $this->form_validation->set_rules('in_time', lang('time'), 'required');
        $this->form_validation->set_rules('emp_id', lang('customer') . ' ' . lang('id'), 'required');
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
            if (empty($entity['hub_id'])) $entity['hub_id'] = getallheaders()['hub_id'];
        }
        return $entity;
    }
    public function check_entry($entity)
    {
        $this->db->where('emp_id', (int)$entity['emp_id']);
        $this->db->where('closed_on  BETWEEN ' . $this->db->escape($entity['closed_on']) . ' AND ' . $this->db->escape($entity['closed_on']));
        $rows = $this->db->get('empattendance')->result();
        return !empty($rows) ? FALSE : TRUE;
    }
    public function get_attendance($emp_id, $hub_id, $from, $to, $deleted = NULL, $limit = NULL, $offset = 0)
    {
        $this->db->select('att.*,emp.name');
        $this->db->from('empattendance AS att');
        $this->db->join('employees as emp', 'emp.id=att.emp_id');
        $this->db->where('att.closed_on  BETWEEN ' . $this->db->escape($from) . ' AND ' . $this->db->escape($to));
        //$this->db->where('att.closed_on >=', $this->db->escape($from));
        //$this->db->where('att.closed_on <=', $this->db->escape($to));
        if (!empty($emp_id)) $this->db->where('emp.id', $emp_id);
        else if (config_item('store_type') === 'multiple' && !empty($hub_id)) $this->db->where('att.hub_id', $hub_id);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('att.' . $this->_deleted_at, false);
        if ($limit) $this->db->limit($limit, $offset);
        $this->db->order_by('att.created_at', 'DSC');
        return $this->db->get()->result();
    }

    public function build_entity($entity)
    {
        $entity->name = $this->decode($entity->name);
        return $entity;
    }
    public function build_config($hub_id)
    {
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false);
        foreach ($results['hubs'] as $index => $hub) {
            $results['hubs'][$index] = $this->Userhubs->build_entity($hub);
            if ($hub_id == $hub->id) $results['active_hub'] = $results['hubs'][$index];
        }
        $results['employees'] = $this->Employees->get_by(['status' => 'active', 'hub_id' => $hub_id], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name']);
        foreach ($results['employees'] as $index => $obj) {
            $results['employees'][$index]->name = $this->decode($obj->name);
        }
        return $results;
    }
}
