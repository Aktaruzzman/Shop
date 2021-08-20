
<?php

class Useractivities extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'useractivities';
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
        $this->load->model('Users');
    }
    public function validate()
    {
        return true;
    }
    public function get_activities($id = 0, $user = 0, $deleted = NULL, $limit = NULL, $offset = 0, $from = 0, $to = 0)
    {
        $logger = $this->Users->get(getallheaders()['user_id'], TRUE, TRUE);
        $this->db->select('useractivities.*,users.name,userhubs.name as hub_name');
        $this->db->from('useractivities');
        $this->db->join('users', 'users.id=useractivities.user_id');
        $this->db->join('userhubs', 'userhubs.id=useractivities.hub_id');
        if (!empty($user)) $this->db->where('useractivities.user_id=', $user);
        else if ($logger->role !== "superadmin") $this->db->where_not_in('useractivities.user_id', [1]);
        if (!empty($id)) $this->db->where('useractivities.id', $id);
        if (!empty($from) && !empty($to)) $this->db->where('useractivities.created_at BETWEEN "' . $from . '" and "' . $to . '"');
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('useractivities.' . $this->_deleted_at, false);
        if (!empty($this->_order_by)) $this->db->order_by('useractivities.' . $this->_order_by, $this->_order_type);
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
}
