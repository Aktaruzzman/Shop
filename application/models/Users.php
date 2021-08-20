
<?php

class Users extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'users';
        $this->_primary_key = 'id';
        $this->_order_by = 'role_value';
        $this->_order_type = 'ASC';
        $this->_default = '';
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
        $is_phone_unique = $this->check_unique('phone', $this->input->post('phone')) ? "|is_unique[users.phone]" : "";
        $is_email_unique = $this->check_unique('email', $this->input->post('email')) ? "|is_unique[users.email]" : "";
        $is_username_unique = $this->check_unique('username', $this->input->post('username')) ? "|is_unique[users.username]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_phone_unique = trim($this->input->post('phone')) == trim($existing->phone) ? "" : $is_phone_unique;
            $is_email_unique = trim($this->input->post('email')) == trim($existing->email) ? "" : $is_email_unique;
            $is_username_unique = trim($this->input->post('username')) == trim($existing->username) ? "" : $is_username_unique;
        } else {
            $this->form_validation->set_rules('password', lang('password'), 'required|min_length[4]');
            $this->form_validation->set_rules('role', lang('role'), 'required', array('required' => lang('field_required_msg')));
            $this->form_validation->set_rules('hub_id', lang('hub'), 'required|numeric', array('required' => lang('field_required_msg')));
        }
        $this->form_validation->set_rules('name_en', lang('name') . ' ' . lang('english'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('name') . ' ' . lang('bangla'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('phone', lang('phone'), 'required' . $is_phone_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email' . $is_email_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('username', lang('email'), 'required|alpha_numeric' . $is_email_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('gender', lang('gender'), 'required', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function get_users($id = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = 0)
    {
        $user = $this->get(getallheaders()['user_id']);
        $this->db->select('users.*,userhubs.name as hub_name');
        $this->db->from('users');
        $this->db->join('userhubs', 'users.hub_id=userhubs.id');
        if (!empty($id)) $this->db->where('users.id', $id);
        if ($user->role == "superadmin") {
            if (!empty($base)) $this->db->where('users.hub_id=', $base);
        } else if ($user->role == "owner") {
            $this->db->where_not_in('users.role', ['superadmin']);
        } else if ($user->role == "admin") {
            $this->db->where_not_in('users.role', ['superadmin', 'owner']);
            $this->db->where('users.hub_id=', $user->hub_id);
        } else $this->db->where('users.id', $user->id);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('users.' . $this->_deleted_at, false);
        $this->db->order_by('users.hub_id ASC, users.role_value ASC');
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
    public function down_users($user)
    {
        $users = [];
        if ($user->role === "superadmin") {
            $this->db->select('id,name,hub_id');
            $this->db->from($this->getTable());
            $this->db->where_not_in('role', ['superadmin']);
            //$this->db->where('hub_id', $user->hub_id);
            $users = $this->db->get()->result();
        }
        if ($user->role === "owner") {
            $this->db->select('id,name,hub_id');
            $this->db->from($this->getTable());
            $this->db->where_not_in('role', ['superadmin', 'owner']);
            //$this->db->where('hub_id', $user->hub_id);
            $users = $this->db->get()->result();
        } else if ($user->role === "admin") {
            $this->db->select('id,name,hub_id');
            $this->db->from($this->getTable());
            $this->db->where_not_in('role', ['superadmin', 'owner', 'admin']);
            $this->db->where('hub_id', $user->hub_id);
            $users = $this->db->get()->result();
        }
        foreach ($users as $k => $v) {
            $users[$k]->name = $this->decode($v->name);
            $users[$k]->hub = $this->decode($this->Userhubs->get($v->hub_id, true, true)->name);
        }
        return $users;
    }
}
