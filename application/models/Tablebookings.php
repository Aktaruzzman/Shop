
<?php

class Tablebookings extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'tablebookings';
        $this->_primary_key = 'id';
        $this->_order_by = 'sit_time';
        $this->_order_type = 'ASC';
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->load->model('Users');
    }
    public function validate()
    {
        $this->form_validation->set_rules('name_en', lang('customer') . ' ' . lang('english'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('customer') . ' ' . lang('bangla'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('phone', lang('phone'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'valid_email');
        $this->form_validation->set_rules('date', lang('date'), 'required');
        $this->form_validation->set_rules('time', lang('time'), 'required');
        $this->form_validation->set_rules('guest', lang('time'), 'required|numeric');
        return $this->form_validation->run();
    }
    public function validate_booking()
    {
        $this->form_validation->set_rules('guest', lang('guest'), 'required|numeric', array('required' => sprintf(lang('select_option'), sprintf(lang('option_number'), lang('guest')))));
        $this->form_validation->set_rules('date', lang('date'), 'required', array('required' => sprintf(lang('select_option'), lang('date'))));
        $this->form_validation->set_rules('time', lang('time'), 'required', array('required' => sprintf(lang('select_option'), lang('time'))));
        if (config_item('store_type') === 'multiple') $this->form_validation->set_rules('hub_id', lang('branch'), 'required|numeric', array('required' => sprintf(lang('select_option'), lang('branch')), 'numeric' => sprintf(lang('select_option'), lang('branch'))));
        return $this->form_validation->run();
    }


    public function get_bookings($id = null, $deleted = null, $from = null, $to = null, $report_type = null, $stage = null)
    {
        $headers = getallheaders();
        $logger = $this->Users->get(!empty($headers['user_id']) ? $headers['user_id'] : 1, true, true);
        if (empty($from))  $from = !empty($headers['start']) ? date('Y-m-d H:i:s', $headers['start'] / 1000) : date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' 00:00:01'));
        else  $from = date('Y-m-d H:i:s', ($from / 1000));
        if (empty($to)) $to = !empty($headers['end']) ? date('Y-m-d H:i:s', $headers['end'] / 1000) : date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' 23:59:00'));
        else $to = date('Y-m-d H:i:s', ($to / 1000));

        $this->db->select('tablebookings.*,customers.name,customers.phone,customers.email,userhubs.name as hub');
        $this->db->from('tablebookings');
        $this->db->join('customers', 'customers.id=tablebookings.cust_id');
        $this->db->join('userhubs', 'userhubs.id=tablebookings.hub_id');

        if (!empty($id)) {
            $this->db->where('tablebookings.id', $id);
            return $this->db->get()->row();
        } else {
            if (!empty($stage)) $this->db->where('tablebookings.stage', $stage);
            if (!empty($logger) && config_item('store_type') === "multiple")  $this->db->where('tablebookings.hub_id', $logger->hub_id);
            if ($report_type === 'deleted') {
                $this->db->where('tablebookings.' . $this->_deleted_at, 1);
                $this->db->where('tablebookings.created_at BETWEEN "' . $from . '" and "' . $to . '"');
            } else if ($report_type === 'sitting') $this->db->where('tablebookings.sit_time BETWEEN "' . $from . '" and "' . $to . '"');
            else $this->db->where('tablebookings.created_at BETWEEN "' . $from . '" and "' . $to . '"');
            if ($report_type !== 'deleted' && !$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('tablebookings.' . $this->_deleted_at, false);
            if (!empty($this->_order_by)) $this->db->order_by('tablebookings.' . $this->_order_by, $this->_order_type);
            return $this->db->get()->result();
        }
    }
}
