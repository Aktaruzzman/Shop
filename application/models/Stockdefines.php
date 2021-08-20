
<?php

class Stockdefines extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'stockdefines';
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
        $this->load->model('Prodcats');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodtops');
        $this->load->model('Cfgunits');
        $this->load->model('Stockentries');
        $this->load->model('Stockhubentries');
        $this->load->model('Stockhubprices');
    }
    public function validate()
    {
        $this->form_validation->set_rules('maintain_stock', 'maintain stock', 'numeric');
        $this->form_validation->set_rules('warn_below_qty', 'warn below quantity', 'numeric');
        $this->form_validation->set_rules('stop_sale_qty', 'Stop sale quantity', 'required|numeric');
        $this->form_validation->set_rules('id', 'Stock id', 'required|numeric');
        return $this->form_validation->run();
    }
    function build_config($category)
    {
        $results['categories'] = $this->Prodcats->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name', 'base_id', 'unit_id']);
        foreach ($results['categories'] as $index => $obj) {
            $results['categories'][$index]->name = $this->decode($obj->name);
        }
        if (empty($category) && !empty($results['categories'])) $category = $results['categories'][0]->id;
        $results['base'] = $this->Prodcats->get($category, TRUE, TRUE);
        if (!empty($results['base'])) {
            $results['base']->name = $this->decode($results['base']->name);
            $results['base']->unit = $this->decode($this->Cfgunits->get($results['base']->unit_id, TRUE, TRUE)->short);
        }
        return $results;
    }
    function build_entity($entity, $hub_id)
    {
        if (!empty($entity)) {
            $row = null;
            if (!empty($entity->topping_id)) {
                $row = $this->Prodtops->get($entity->topping_id, TRUE, TRUE);
                $entity->name = $this->decode($row->name);
                $entity->unit = $this->decode($this->Cfgunits->get($row->unit_id, TRUE, TRUE)->short);
                $entity->base_price = $row->base_price;
                $entity->in_price = $row->in_price;
                $entity->out_price = $row->out_price;
                $entity->has_options = 0;
                $entity->category_unit = $entity->unit;
            } else if (!empty($entity->option_id)) {
                $row = $this->Prodoptions->get($entity->option_id, TRUE, TRUE);
                $entity->base_price = $row->base_price;
                $entity->in_price = $row->in_price;
                $entity->out_price = $row->out_price;
                $entity->has_options = 0;
                $row->name = $this->decode($row->name);
                $item = $this->Proditems->get($entity->item_id, TRUE);
                $item->name = $this->decode($item->name);
                $entity->name  = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
                $entity->unit = $this->decode($this->Cfgunits->get($row->unit_id, TRUE, TRUE)->short);
                $category = $this->Prodcats->get($row->category_id, true, true);
                $entity->category_unit = $this->decode($this->Cfgunits->get($category->unit_id, TRUE, TRUE)->short);
            } else {
                $row = $this->Proditems->get($entity->item_id, TRUE, TRUE);
                $entity->base_price = $row->base_price;
                $entity->in_price = $row->in_price;
                $entity->out_price = $row->out_price;
                $entity->has_options = $this->Prodoptions->count_rows(['item_id' => $entity->item_id]);
                $entity->name = json_decode($row->name);
                $entity->unit = $this->decode($this->Cfgunits->get($row->unit_id, TRUE, TRUE)->short);
                $category = $this->Prodcats->get($row->category_id, true, true);
                $entity->category_unit = $this->decode($this->Cfgunits->get($category->unit_id, TRUE, TRUE)->short);
            }
            $entity->entry = $this->Stockentries->get_by(['stock_id' => $entity->id], TRUE, TRUE, NULL, NULL, NULL, ['id', 'stock_id', 'stockloc_id', 'balance', 'sales']);
            $entity->entry->balance_weight = $row->weight *  $entity->entry->balance;
            $entity->entry->sale_weight = $row->weight *  $entity->entry->sales;
            if (!empty($hub_id)) {
                if ((config_item('store_type') === "multiple" && config_item('stock_type') !== "multiple") || (config_item('store_type') !== "multiple" && config_item('stock_type') == "multiple")) {
                    $hubentry = $this->Stockhubentries->get_by(['stock_id' => $entity->id, 'hub_id' => $hub_id], TRUE, TRUE);
                    if (!empty($hubentry)) {
                        $entity->entry->balance =  $hubentry->balance;
                        $entity->entry->sales =  $hubentry->sales;
                        $entity->entry->balance_weight = $row->weight *  $entity->entry->balance;
                        $entity->entry->sale_weight = $row->weight *  $entity->entry->sales;
                        $entity->entry->unit = $entity->category_unit;
                    } else {
                        $entity->entry->balance = 0;
                        $entity->entry->sales = 0;
                        $entity->entry->balance_weight = 0;
                        $entity->entry->sale_weight = 0;
                        $entity->entry->unit = $entity->category_unit;
                    }
                    $hubprice = $this->Stockhubprices->get_by(['stock_id' => $entity->id, 'hub_id' => $hub_id], TRUE, TRUE);
                    if (!empty($hubprice)) {
                        $entity->base_price = $hubprice->base_price;
                        $entity->in_price = $hubprice->in_price;
                        $entity->out_price = $hubprice->out_price;
                    }
                }
            }
        }
        return $entity;
    }
}
