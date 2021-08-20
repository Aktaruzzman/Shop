
<?php

class Saledetails extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'sale_details';
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
        $this->load->model('Stocklocations');
        $this->load->model('Stockentries');
        $this->load->model('Stocklogs');
        $this->load->model('Stockhubentries');
    }
    function save_details($sale_details, $update_stock = FALSE, $action = 'insert')
    {

        foreach ($sale_details as $datum) {
            if (!empty($update_stock)) $this->update_stock($datum);
            $datum['name'] = $this->encode($datum['name']);
            $datum['category_name'] = $this->encode($datum['category_name']);
            $datum['base_name'] = $this->encode($datum['base_name']);
            $datum['item_name'] = $this->encode($datum['item_name']);
            $datum['option_name'] = $this->encode($datum['option_name']);
            $datum['course_name'] = $this->encode($datum['course_name']);
            $datum['unit_name'] = $this->encode($datum['unit_name']);
            $datum['tax_rule'] =  empty($datum['tax_rule']) ? null : $this->encode($datum['tax_rule']);
            $datum['discount_rule'] = empty($datum['discount_rule']) ? null : $this->encode($datum['discount_rule']);
            $datum['sides'] = empty($datum['sides']) ? null : $this->encode($datum['sides']);
            $datum = $this->entity($datum);
            $action === "insert" ? $this->save($datum) : $this->save_by($datum, ['sale_id' => $datum, 'cart_id' => $datum['cart_id']]);
        }
    }
    private function update_stock($line)
    {
        $loc = $this->Stocklocations->get_by(['default' => TRUE], TRUE);
        $this->update_stock_now($line, $loc);
        if (!empty($line['sides'])) {
            $sides = $line['sides'];
            foreach ($sides as $side) {
                if (!empty($side['stock_id'])) $this->update_stock_now($side, $loc);
            }
        }
    }
    private function update_stock_now($line, $loc)
    {
        if (!empty($line['stock_id']) && (int) $line['stock_id'] > 0 && !empty($line['hub_id']) > 0 && (int) $line['hub_id'] > 0) {
            $hub_id = $line['hub_id'];
            $stockEntry = $this->Stockentries->get_by(['stock_id' => $line['stock_id'], 'stockloc_id' => $loc->id], TRUE);
            $latest_balance = $stockEntry->balance - doubleval($line['qty']);
            $latest_sale = $stockEntry->sales + doubleval($line['qty']);
            $this->Stockentries->save(['balance' => $latest_balance, 'sales' => $latest_sale], $stockEntry->id);
            $stockhub = $this->Stockhubentries->get_by(['stock_id' => $line['stock_id'], 'hub_id' => $hub_id], TRUE);
            if (!empty($stockhub)) {
                $hub_latest_balance = $stockhub->balance - doubleval($line['qty']);
                $hub_latest_sale = $stockhub->sales + doubleval($line['qty']);
                $this->Stockhubentries->save(['balance' => $hub_latest_balance, 'sales' => $hub_latest_sale], $stockhub->id);
            } else {
                $hub_latest_balance = 0 - doubleval($line['qty']);
                $hub_latest_sale = doubleval($line['qty']);
                $this->Stockhubentries->save(['stock_id' =>  $line['stock_id'], 'hub_id' => $hub_id, 'balance' => $hub_latest_balance, 'sales' => $hub_latest_sale]);
            }
            $this->Stocklogs->save(['stock_id' => $line['stock_id'], 'closed_on' => closed_on(), 'stockloc_id' => $loc->id, 'hub_id' => $hub_id, 'item_out' => $line['qty']]);
        }
    }
}
