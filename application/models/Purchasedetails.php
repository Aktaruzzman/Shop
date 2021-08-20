
<?php

class Purchasedetails extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name   = 'purchase_details';
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
        $this->load->model('Stocklocations');
        $this->load->model('Stockentries');
        $this->load->model('Stocklogs');
        $this->load->model('Stockhubentries');
    }
    public function validate()
    {
        return true;
    }
    public function save_details($sale_details)
    {
        foreach ($sale_details as $datum) {
            $this->update_stock($datum);
            $datum['name']      = $this->encode($datum['name']);
            $datum['unit_name'] = $this->encode($datum['unit_name']);
            $this->save($this->entity($datum));
        }
    }
    private function update_stock($line)
    {
        $loc = $this->Stocklocations->get_by(['default' => true], true);
        $this->update_stock_now($line, $loc);
    }
    private function update_stock_now($line, $loc)
    {
        if (!empty($line['stock_id']) && (int) $line['stock_id'] > 0 && !empty($line['hub_id']) > 0 && (int) $line['hub_id'] > 0) {
            $hub_id         = $line['hub_id'];
            $stockEntry     = $this->Stockentries->get_by(['stock_id' => $line['stock_id'], 'stockloc_id' => $loc->id], true);
            $latest_balance = $stockEntry->balance + doubleval($line['qty']);

            $this->Stockentries->save(['balance' => $latest_balance], $stockEntry->id);
            $stockhub = $this->Stockhubentries->get_by(['stock_id' => $line['stock_id'], 'hub_id' => $hub_id], true);
            if (!empty($stockhub)) {
                $hub_latest_balance = $stockhub->balance + doubleval($line['qty']);
                $this->Stockhubentries->save(['balance' => $hub_latest_balance], $stockhub->id);
            } else {
                $hub_latest_balance = doubleval($line['qty']);
                $this->Stockhubentries->save(['stock_id' => $line['stock_id'], 'hub_id' => $hub_id, 'balance' => $hub_latest_balance]);
            }
            $this->Stocklogs->save(['stock_id' => $line['stock_id'], 'closed_on' => closed_on(), 'stockloc_id' => $loc->id, 'hub_id' => $hub_id, 'item_in' => $line['qty']]);
        }
    }
}
