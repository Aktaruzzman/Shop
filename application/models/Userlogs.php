
<?php

class Userlogs extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'userlogs';
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
        return true;
    }
    public function get_logs($id = 0, $user = 0, $deleted = NULL, $limit = NULL, $offset = 0, $from = 0, $to = 0)
    {
        $this->db->select('userlogs.*,users.name,users.hub_id,userhubs.name as hub_name');
        $this->db->from('userlogs');
        $this->db->join('users', 'users.id=userlogs.user_id');
        $this->db->join('userhubs', 'userhubs.id=users.hub_id');
        if (!empty($user)) $this->db->where('userlogs.user_id=', $user);
        if (!empty($id)) $this->db->where('userlogs.id', $id);
        if (!empty($from) && !empty($to)) $this->db->where('userlogs.login BETWEEN "' . $from . '" and "' . $to . '"');
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('userlogs.' . $this->_deleted_at, false);
        if (!empty($this->_order_by)) $this->db->order_by('userlogs.' . $this->_order_by, $this->_order_type);
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
    public function get_device_list()
    {
        $this->db->select('id,device_code,user_id');
        $this->db->from('userlogs');
        $this->db->group_by('device_code');
        return $this->db->get()->result();
    }
}

