
<?php

class Stocklogs extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'stocklogs';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = "ASC";
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
        if ((!empty($this->input->post('stock_id')) && !empty($this->input->post('stockloc_id'))) && (!empty($this->input->post('item_in')) || !empty($this->input->post('item_out')))) {
            return true;
        }
        return false;
    }
}
