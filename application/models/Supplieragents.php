
<?php

class Supplieragents extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'supplieragents';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = 'ASC';
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
        $is_phone_unique = $this->check_unique('phone', $this->input->post('phone')) ? "|is_unique[suppliers.phone]" : "";
        $is_email_unique = $this->check_unique('email', $this->input->post('email')) ? "|is_unique[suppliers.email]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_phone_unique = trim($this->input->post('phone')) == trim($existing->phone) ? "" : $is_phone_unique;
            $is_email_unique = trim($this->input->post('email')) == trim($existing->email) ? "" : $is_email_unique;
        }
        $this->form_validation->set_rules('name_en', lang('supplier') . ' ' . lang('english'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('supplier') . ' ' . lang('bangla'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('phone', lang('phone'), 'required' . $is_phone_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email' . $is_email_unique, array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function get_supplieragents($hub_id = null, $deleted = null, $limit = null, $offset = null, $status = null)
    {
        $this->db->select('SSA.supplieragent_id,SSA.hub_id,SSA.due as hub_due,SA.*');
        $this->db->from('storesupplieragents as SSA');
        $this->db->join('supplieragents as SA', 'SSA.supplieragent_id=SA.id');
        if ($hub_id) $this->db->where('SSA.hub_id', $hub_id);
        if ($status) $this->db->where('SSA.status', $status);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('SA.' . $this->_deleted_at, false);
        if (!empty($this->_order_by)) $this->db->order_by('SA.' . $this->_order_by, $this->_order_type);
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }


    public function get_owed_supplieragents($hub_id)
    {
        $this->db->select('SSA.supplieragent_id,SSA.hub_id,SSA.due,SA.id,SA.name,SA.phone,SA.email');
        $this->db->from('storesupplieragents as SSA');
        $this->db->join('supplieragents as SA', 'SSA.supplieragent_id=SA.id');
        $this->db->where('SSA.hub_id', $hub_id);
        $this->db->where('SSA.due >', 0);
        return $this->db->get()->result();
    }
}
