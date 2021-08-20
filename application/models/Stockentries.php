
<?php

class Stockentries extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'stockentries';
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
        $this->form_validation->set_rules('id', 'id', 'required|numeric');
        $this->form_validation->set_rules('hub_id', 'Hub', 'required|numeric');
        $this->form_validation->set_rules('stock_id', 'Stock id', 'required|numeric');
        $this->form_validation->set_rules('stockloc_id', 'Stock Location', 'required|numeric');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|numeric');
        return $this->form_validation->run();
    }
    
}
