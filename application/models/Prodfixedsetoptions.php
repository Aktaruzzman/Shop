
<?php

class Prodfixedsetoptions extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodfixedsetoptions';
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
        $this->load->model('Prodcats');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodtops');
        $this->load->model('Prodmods');
        $this->load->model('Cfgunits');
        $this->load->model('Stockentries');
        $this->load->model('Stockhubentries');
    }

    public function validate()
    {
        if (!empty($this->input->post('hook_item_id'))  && (!empty($this->input->post('item_id')) || !empty($this->input->post('option_id')) || !empty($this->input->post('topping_id')) || !empty($this->input->post('modify_id')))) {
            $this->form_validation->set_rules('hook_item_id', lang('item'), 'required|numeric', array('required' => lang('field_required_msg')));
            return $this->form_validation->run();
        }
        return false;
    }
    public function build_entity($entity, $hub_id)
    {
        $unit = null;
        $row = null;
        if ((!empty($entity->modify_id) || !is_null($entity->modify_id)) && $entity->modify_id > 0) {
            $row = $this->Prodmods->get($entity->modify_id, true);
            $entity->name = $this->Prodfixedsetoptions->decode($row->name);
            $entity->base_price = !empty($row->base_price) ? $row->base_price : 0;
            $entity->in_price = !empty($row->in_price) ? $row->in_price : 0;
            $entity->out_price = !empty($row->out_price) ? $row->out_price : 0;
            $entity->stock_id = 0;
            $unit = null;
        } else if ((!empty($entity->topping_id) || !is_null($entity->topping_id)) && $entity->topping_id > 0) {
            $row = $this->Prodtops->get($entity->topping_id, true, true);
            $entity->name = $this->Prodfixedsetoptions->decode($row->name);
            $unit = $this->Cfgunits->get($row->unit_id, true, true, ['id', 'name', 'short']);
            $entity->base_price = $row->base_price;
            $entity->in_price = $row->in_price;
            $entity->out_price = $row->out_price;
            $entity->stock_id = $row->stock_id;
        } else if ((!empty($entity->option_id) || !is_null($entity->option_id)) && $entity->option_id > 0) {
            $row = $this->Prodoptions->get($entity->option_id, true, true);
            $row->name = $this->Prodfixedsetoptions->decode($row->name);
            $item = $this->Proditems->get($entity->item_id, true);
            $item->name = $this->Prodfixedsetoptions->decode($item->name);
            $entity->name = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
            $unit = $this->Cfgunits->get($row->unit_id, true, true, ['id', 'name', 'short']);
            $entity->base_price = $row->base_price;
            $entity->in_price = $row->in_price;
            $entity->out_price = $row->out_price;
            $entity->stock_id = $row->stock_id;
        } else {
            $row = $this->Proditems->get($entity->item_id, true, true);
            $entity->name = $this->Prodfixedsetoptions->decode($row->name);
            $unit = $this->Cfgunits->get($row->unit_id, true, true, ['id', 'name', 'short']);
            $entity->base_price = $row->base_price;
            $entity->in_price = $row->in_price;
            $entity->out_price = $row->out_price;
            $entity->stock_id = $row->stock_id;
        }
        if (!empty($unit)) {
            $unit->name = $this->Prodfixedsetoptions->decode($unit->name);
            $unit->short = $this->Prodfixedsetoptions->decode($unit->short);
            $entity->unit = $unit;
        }
        $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
        $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';

        $entity->hub_id = !empty($hub_id) ? (int) $hub_id : (int) getallheaders()['hub_id'];
        if (!empty($entity->stock_id)) {
            $entity->stock = $this->Stockdefines->get($entity->stock_id, true, true, ['id', 'maintain_stock', 'warn_below_qty', 'stop_sale_qty']);
            $entity->stock->balance = $this->Stockentries->get_by(['stock_id' => $entity->stock_id], true, true, 1, 0, null, ['balance'])->balance;
            if (!empty($entity->hub_id)) {
                if ((config_item('store_type') === "multiple" && config_item('stock_type') !== "multiple") || (config_item('store_type') !== "multiple" && config_item('stock_type') == "multiple")) {
                    $hubentry = $this->Stockhubentries->get_by(['stock_id' => $entity->stock->id, 'hub_id' => $entity->hub_id], true, true);
                    if (!empty($hubentry)) $entity->stock->balance = $hubentry->balance;
                    else $entity->stock->balance = 0;
                }
            }
        }
        return $entity;
    }
    public function build_config($source, $source_id)
    {
        $results['categories'] = $this->Prodcats->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name']);
        foreach ($results['categories'] as $index => $obj) {
            $results['categories'][$index]->name = $this->Prodfixedsetoptions->decode($obj->name);
            if (intval($source_id) === $obj->id) {
                $results['item_category'] = $results['categories'][$index];
            }
        }
        if ($source === 'item' && !empty($source_id)) {
            $results['items'] = $this->Proditems->get_by(['status' => 'active', 'category_id' => $source_id], false, false, null, null, null, ['id', 'name', 'category_id', 'stock_id', 'unit_id']);
            foreach ($results['items'] as $index => $obj) {
                $results['items'][$index]->name = $this->Prodfixedsetoptions->decode($obj->name);
                $results['items'][$index]->options = $this->Prodoptions->get_by(['status' => 'active', 'item_id' => $obj->id], false, false, null, null, null, ['id', 'name', 'item_id', 'category_id', 'stock_id', 'unit_id']);
                foreach ($results['items'][$index]->options as $i => $option) {
                    $results['items'][$index]->options[$i]->name = $this->Prodfixedsetoptions->decode($option->name);
                }
            }
        }
        if ($source === 'topping') {
            $results['toppings'] = $this->Prodtops->get_by(['status' => 'active'], false, false, null, null, null);
            foreach ($results['toppings'] as $index => $top) {
                $results['toppings'][$index]->name = $this->Prodfixedsetoptions->decode($top->name);
            }
        }
        if ($source === 'modify') {
            $results['modifys'] = $this->Prodmods->get_by(['status' => 'active'], false, false, null, null, null);
            foreach ($results['modifys'] as $index => $mod) {
                $results['modifys'][$index]->name = $this->Prodfixedsetoptions->decode($mod->name);
            }
        }
        return $results;
    }
}
