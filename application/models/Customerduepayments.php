
<?php

class Customerduepayments extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'customerduepayment';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
    }
    public function validate()
    {
        if ($this->input->post('id') && $this->input->post('cust_id')) return true;
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('payment', lang('payment'), 'required|in_list[Cash,Card,Bikash,Nagod,Rocket,Qcash,Cheque,Voucher]', array('required' => lang('field_required_msg')));
        if ($this->input->post('payment') === "Cash") {
            $this->form_validation->set_rules('source', lang('source'), 'required|in_list[till,other]', array('required' => lang('field_required_msg')));
        }
        $this->form_validation->set_rules('cust_id', lang('customer') . ' ' . lang('id'), 'required|numeric');
        return $this->form_validation->run();
    }
    function get_details($cust_id = NULL, $from = NULL, $to = NULL, $hub_id = NULL)
    {
        if ($cust_id) $this->db->where('cust_id', $cust_id);
        else  $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        if (config_item('store_type') === "multiple" && $hub_id) {
            $hub_id = $hub_id ? $hub_id : getallheaders('hub_id');
            $this->db->where('hub_id', $hub_id);
        }
        if (!empty($this->_order_by)) $this->db->order_by($this->_order_by, $this->_order_type);
        else $this->db->order_by($this->_primary_key);
        return $this->db->get($this->_table_name)->result();
    }
}

