
<?php

class Supplierduepayments extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name   = 'supplierduepayment';
        $this->_primary_key  = 'id';
        $this->_order_by     = 'created_at';
        $this->_order_type   = "DESC";
        $this->_default      = '';
        $this->_created_at   = 'created_at';
        $this->_updated_at   = 'updated_at';
        $this->_deleted_at   = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps   = true;
        $this->_rules        = array();
        $this->load->model('Users');
    }
    public function validate()
    {
        if ($this->input->post('id') && $this->input->post('supplier_id')) return true;
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('payment', lang('payment'), 'required|in_list[Cash,Card,Bikash,Nagod,Rocket,Qcash,Cheque,Voucher]', array('required' => lang('field_required_msg')));
        if ($this->input->post('payment') === "Cash") {
            $this->form_validation->set_rules('source', lang('source'), 'required|in_list[till,other]', array('required' => lang('field_required_msg')));
        }
        $this->form_validation->set_rules('supplieragent_id', lang('supplier') . ' ' . lang('id'), 'required|numeric');
        return $this->form_validation->run();
    }
    public function get_details($supplieragent_id = null, $from = null, $to = null, $hub_id = null)
    {
        if ($supplieragent_id) $this->db->where('supplieragent_id', $supplieragent_id);
        else $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        $hub_id = $hub_id ? $hub_id : getallheaders()['hub_id'];
        $this->db->where('hub_id', $hub_id);
        if (!empty($this->_order_by)) $this->db->order_by($this->_order_by, $this->_order_type);
        else $this->db->order_by($this->_primary_key);
        return $this->db->get($this->_table_name)->result();
    }
}
