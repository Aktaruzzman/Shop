<?php

class Expenses extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'expenses';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->load->model('Expensesectors');
        $this->load->model('Payments');
    }
    public function validate()
    {
        if ($this->input->post('id') && $this->input->post('sector_id')) {
            return true;
        }
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('payment', lang('payment'), 'required|in_list[Cash,Card,Bikash,Nagod,Rocket,Qcash,Cheque,Voucher]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('sector_id', lang('sector') . ' ' . lang('id'), 'required|numeric');
        return $this->form_validation->run();
    }
    public function get_sector_expense($sector_id, $from, $to, $hub_id)
    {
        $this->db->select_sum('amount');
        $this->db->where('sector_id', $sector_id);
        if ($from > 0 && $to > 0) $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        if (!empty($hub_id)) $this->db->where('hub_id', $hub_id);
        $this->db->where('deleted_at!=', 1);
        $row = $this->db->get('expenses')->row();
        return !empty($row->amount) ? $row->amount : 0;
    }
    public function get_expenses($sector_id, $from, $to, $deleted, $hub_id)
    {
        $this->db->select('exp.*,exps.id as sector_id,exps.name as sector_name');
        $this->db->from('expenses as exp');
        $this->db->join('expensesectors as exps', 'exps.id=exp.sector_id');
        if ($sector_id > 0) $this->db->where('exp.sector_id', $sector_id);
        if ($from && $to) $this->db->where('exp.closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        if (!empty($hub_id)) $this->db->where('exp.hub_id', $hub_id);
        if (empty($deleted)) $this->db->where('exp.deleted_at!=', 1);
        $this->db->group_by('exp.id');
        return $this->db->get('expenses')->result();
    }

    public function build_config($hub_id)
    {
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false);
        foreach ($results['hubs'] as $index => $hub) {
            $results['hubs'][$index] = $this->Userhubs->build_entity($hub);
            if ($hub_id == $hub->id) $results['active_hub'] = $results['hubs'][$index];
        }
        $results['sectors'] = $this->Expensesectors->get(['status' => 'active', 'hub_id' => $hub_id], false, false, ['id', 'name']);
        foreach ($results['sectors'] as $k => $v) {
            $results['sectors'][$k]->name = $this->Expensesectors->decode($v->name);
        }
        $results['payments'] = $this->Payments->get(['status' => 'active'], false, false, ['id', 'name', 'value']);
        foreach ($results['payments'] as $k => $v) {
            $results['payments'][$k]->name = $this->Payments->decode($v->name);
        }
        return $results;
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
        $entity['closed_on'] = closed_on();
        if (empty($entity['hub_id'])) $entity['hub_id'] = getallheaders()['hub_id'];
        if (empty($entity['source'])) {
            if ($entity['payment'] !== "Cash") {
                $entity['source'] = $entity['payment'];
            }
        }
        return $entity;
    }
    public function build_entity($entity)
    {
        $entity->name = $this->decode($entity->name);
        $entity->hub = $this->Userhubs->build_entity($this->Userhubs->get($entity->hub_id, true, true));
        return $entity;
    }
}
