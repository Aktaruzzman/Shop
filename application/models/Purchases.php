
<?php

class Purchases extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name   = 'purchases';
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
        return true;
    }

    public function build_entity($entity)
    {
        if (!empty($entity)) {
            $entity->supplier = $this->Purchases->decode($this->Suppliers->get($entity->supplier_id, true)->name);
            $entity->supplieragent = $this->Purchases->decode($this->Supplieragents->get($entity->supplieragent_id, true)->name);
            $entity->details  = $this->Purchasedetails->get(['purchase_id' => $entity->id], false, false);
            foreach ($entity->details as $index => $obj) {
                $entity->details[$index]->name      = $this->Purchasedetails->decode($obj->name);
                $entity->details[$index]->unit_name = $this->Purchasedetails->decode($obj->unit_name);
            }
            $entity->hub        = $this->Userhubs->get(['id' => $entity->hub_id], true, false, ['id', 'name', 'house', 'area_id']);
            $entity->hub->name  = $this->Userhubs->decode($entity->hub->name);
            $entity->hub->house = $this->Userhubs->decode($entity->hub->house);
            $entity->hub->area  = $this->Geoareas->formatted($entity->hub->area_id)->formatted;
        }
        return $entity;
    }

    public function get_purchases($from = null, $to = null, $search = null, $fields = null, $limit = null, $offset = null, $hub_id = null, $deleted = false)
    {
        $header = getallheaders();
        $user   = $this->Users->get($header['user_id'], true);
        $this->db->where('is_paid', 1);
        if (empty($search)) {
            $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        }
        if (config_item('store_type') === "multiple") {
            if (!empty($hub_id)) {
                if ($user->role_value <= 3) {
                    $this->db->where('hub_id', $hub_id);
                } else {
                    $this->db->where('updated_by', $user->id);
                }
            } else {
                if ($user->role_value <= 3) {
                    $this->db->where('hub_id', $user->hub_id);
                } else {
                    $this->db->where('updated_by', $user->id);
                }
            }
        } else {
            if ($user->role_value <= 3) {
                $this->db->where('hub_id', $user->hub_id);
            } else {
                $this->db->where('updated_by', $user->id);
            }
        }
        if (!empty($search)) {
            $criteria = [];
            foreach ($fields as $col) {
                $criteria[$col] = $search;
            }
            $this->db->where($criteria);
        }
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) {
            $this->db->where($this->_table_name . '.' . $this->_deleted_at, false);
        }
        $this->db->order_by($this->_order_by, $this->_order_type);
        return $this->db->get($this->_table_name)->result();
    }
}
