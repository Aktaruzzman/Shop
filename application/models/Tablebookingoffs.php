
<?php

class Tablebookingoffs extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'tablebookingoffs';
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
}
