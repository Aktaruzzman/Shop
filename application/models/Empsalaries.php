
<?php

class Empsalaries extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name   = 'empsalaries';
        $this->_primary_key  = 'id';
        $this->_order_by     = 'created_at';
        $this->_default      = '';
        $this->_created_at   = 'created_at';
        $this->_updated_at   = 'updated_at';
        $this->_deleted_at   = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps   = true;
        $this->_rules        = array();
        $this->load->model('Userhubs');
    }
    public function validate()
    {
        if ($this->input->post('id') && $this->input->post('emp_id')) {
            return true;
        }
        $this->form_validation->set_rules('salary', lang('amount'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('payment', lang('payment'), 'required|in_list[Cash,Card,Bikash,Nagod,Rocket,Qcash,Cheque,Voucher]', array('required' => lang('field_required_msg')));
        if ($this->input->post('payment') === "Cash") {
            $this->form_validation->set_rules('source', lang('source'), 'required|in_list[till,other]', array('required' => lang('field_required_msg')));
        }
        $this->form_validation->set_rules('emp_id', lang('employee') . ' ' . lang('id'), 'required|numeric');
        $this->form_validation->set_rules('date_from', lang('date') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('date_end', lang('date') . ' ' . lang('end'), 'required');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {

        if (empty($entity['source'])) {
            if ("Cash" !== $entity['payment']) {
                $entity['source'] = $entity['payment'];
            }
        }
        if (empty($entity['id'])) {
            if (empty($entity['hub_id'])) $entity['hub_id'] = getallheaders()['hub_id'];
            $entity['closed_on'] = closed_on();
        }
        return $entity;
    }

    public function check_entry($data)
    {
        $max_end = $this->get_max('date_end', ['emp_id' => $data['emp_id']]);
        if (strtotime($max_end) >= strtotime($data['date_from']) || strtotime($max_end) >= strtotime($data['date_end'])) {
            return false;
        } else {
            $this->db->where('emp_id', $data['emp_id']);
            $this->db->where('date_from BETWEEN "' . $data['date_from'] . '" and "' . $data['date_end'] . '"');
            $this->db->where('date_end BETWEEN "' . $data['date_from'] . '" and "' . $data['date_end'] . '"');
            $rows = $this->db->count_all_results($this->_table_name);
            return $rows > 0 ? false : true;
        }
    }
    public function get_salary($emp_id, $hub_id, $from, $to, $deleted = null, $limit = null, $offset = 0)
    {
        $this->db->select('empsal.*,emp.name');
        $this->db->from('empsalaries AS empsal');
        $this->db->join('employees as emp', 'emp.id=empsal.emp_id');
        $this->db->where('empsal.closed_on >=', $from);
        $this->db->where('empsal.closed_on <=', $to);
        //$this->db->where('empsal.closed_on BETWEEN "' . intval($from) . '" and "' . intval($to) . '"');
        if (!empty($emp_id)) $this->db->where('emp.id', $emp_id);
        else if (config_item('store_type') === 'multiple' && !empty($hub_id)) $this->db->where('empsal.hub_id', $hub_id);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('empsal.' . $this->_deleted_at, false);
        if ($limit) $this->db->limit($limit, $offset);
        $this->db->order_by('empsal.closed_on', 'ASC');
        return $this->db->get()->result();
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
        $results['employees'] = $this->Employees->get_by(['status' => 'active', 'hub_id' => $hub_id], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name']);
        foreach ($results['employees'] as $index => $obj) {
            $results['employees'][$index]->name = $this->decode($obj->name);
        }
        $results['payments'] = $this->Payments->get_by(['status' => 'active'], false, null, null, null, null, ['id', 'name', 'value']);
        foreach ($results['payments'] as $index => $obj) {
            $results['payments'][$index]->name = $this->decode($obj->name);
        }
        return $results;
    }
}
