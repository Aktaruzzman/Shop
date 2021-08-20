
<?php

class Stockshiftings extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'stockshiftings';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "ASC";
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
        $this->form_validation->set_rules('from_hub', 'From Hub', 'required|numeric');
        $this->form_validation->set_rules('to_hub', 'To Hub', 'required|numeric');
        $this->form_validation->set_rules('qty', 'Quantity', 'required|numeric');
        $this->form_validation->set_rules('stock_id', 'Stock id', 'required|numeric');
        $this->form_validation->set_rules('stockloc_id', 'Stock Location', 'required|numeric');
        return $this->form_validation->run();
    }

    public function get_rows($from, $to, $hub_id)
    {
        $this->db->select('ss.*,sd.item_id,sd.option_id,sd.topping_id');
        $this->db->from('stockshiftings as ss');
        $this->db->join('stockdefines as sd', 'sd.id=ss.stock_id');
        $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        if (!empty($hub_id) && intval($hub_id)) {
            $this->db->where('ss.from_hub', $hub_id);
            $this->db->or_where('ss.to_hub', $hub_id);
        }
        $shifting_items = $this->db->get()->result();
        foreach ($shifting_items as $index => $obj) {
            $shifting_items[$index]->from_hub_name  = $this->decode($this->Userhubs->get($obj->from_hub, TRUE)->name);
            $shifting_items[$index]->to_hub_name  = $this->decode($this->Userhubs->get($obj->to_hub, TRUE)->name);
            if ($obj->topping_id > 0) {
                $top = $this->db->where('id', $obj->topping_id)->get('prodtops')->row();
                if (!empty($top)) {
                    $shifting_items[$index]->price = $top->base_price;
                    $shifting_items[$index]->total = $top->base_price * $obj->qty;
                    $shifting_items[$index]->name = $this->decode($top->name);
                    $shifting_items[$index]->unit_name = $this->decode($this->db->where('id', $top->unit_id)->get('cfgunits')->row()->short);
                }
            }
            if ($obj->option_id > 0) {
                $optionObj = $this->db->where('id', $obj->option_id)->get('prodoptions')->row();
                if (!empty($optionObj)) {
                    $optionObj->name = $this->decode($optionObj->name);
                    $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                    $itemObj->name = $this->decode($itemObj->name);
                    $shifting_items[$index]->price = $optionObj->base_price;
                    $shifting_items[$index]->total = $optionObj->base_price * $obj->qty;
                    $shifting_items[$index]->name = array('en' => $optionObj->name->en . ' ' . $itemObj->name->en, 'bn' => $optionObj->name->bn . ' ' . $itemObj->name->bn);
                    $shifting_items[$index]->unit_name = $this->decode($this->db->where('id', $optionObj->unit_id)->get('cfgunits')->row()->short);
                }
            } else {
                $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                if (!empty($itemObj)) {
                    $shifting_items[$index]->price = $itemObj->base_price;
                    $shifting_items[$index]->total = $itemObj->base_price * $obj->qty;
                    $shifting_items[$index]->name = $this->decode($itemObj->name);
                    $shifting_items[$index]->unit_name = $this->decode($this->db->where('id', $itemObj->unit_id)->get('cfgunits')->row()->short);
                }
            }
        }
        return $shifting_items;
    }
}
