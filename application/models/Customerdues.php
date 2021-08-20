
<?php

class Customerdues extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'customerdues';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = 'DESC';
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
        if ($this->input->post('id') && $this->input->post('cust_id')) {
            return true;
        }
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('cust_id', lang('customer') . ' ' . lang('id'), 'required|numeric');
        return $this->form_validation->run();
    }
    public function get_due_details($cust_id = null, $from = null, $to = null, $hub_id = null)
    {
        $user_role_value=$this->Users->get(getallheaders()['user_id'], true, true, ['role_value'])->role_value;
        if ($cust_id) {
            $this->db->where('cust_id', $cust_id);
        } else {
            $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        }
        if (config_item('store_type') === "multiple" &&   $user_role_value>=4) {
            $hub_id = $hub_id ? $hub_id : getallheaders('hub_id');
            $this->db->where('hub_id', $hub_id);
        }
        if (!empty($this->_order_by)) {
            $this->db->order_by($this->_order_by, $this->_order_type);
        } else {
            $this->db->order_by($this->_primary_key);
        }
        return $this->db->get($this->_table_name)->result();
    }
}
