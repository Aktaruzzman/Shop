
<?php

class Prodcustsetoptions extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodcustsetoptions';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodtops');
        $this->load->model('Prodmods');
        $this->load->model('Cfgunits');
        $this->load->model('Stockdefines');
        $this->load->model('Stockentries');
        $this->load->model('Stockhubentries');
    }
    public function validate()
    {
        if (($this->input->post('set_id'))  && ($this->input->post('item_id') || $this->input->post('option_id') || $this->input->post('topping_id') || $this->input->post('modify_id'))) {
            $existing = !empty($this->input->post('id')) ? $this->get($this->input->post('id'), TRUE) : NULL;
            if (!empty($this->input->post('topping_id'))) {
                $is_topping_unique = $this->check_unique('topping_id', $this->input->post('topping_id'), ['set_id' => $this->input->post('set_id')]) ? "|is_unique[prodcustsetoptions.topping_id]" : "";
                $is_topping_unique = !empty($existing) && $existing->topping_id == $this->input->post('topping_id') ? "" : $is_topping_unique;
                $this->form_validation->set_rules('topping_id', lang('topping'), 'numeric' . $is_topping_unique);
            } else if (!empty($this->input->post('modify_id'))) {
                $is_modify_unique = $this->check_unique('modify_id', $this->input->post('modify_id'), ['set_id' => $this->input->post('set_id')]) ? "|is_unique[prodcustsetoptions.modify_id]" : "";
                $is_modify_unique = !empty($existing) && $existing->modify_id == $this->input->post('modify_id') ? "" : $is_modify_unique;
                $this->form_validation->set_rules('modify_id', lang('modify'), 'numeric' . $is_modify_unique);
            } else if (!empty($this->input->post('option_id'))) {
                $is_option_unique = $this->check_unique('option_id', $this->input->post('option_id'), ['set_id' => $this->input->post('set_id'), 'item_id' => $this->input->post('item_id')]) ? "|is_unique[prodcustsetoptions.option_id]" : "";
                $is_option_unique = !empty($existing) && $existing->option_id == $this->input->post('option_id') ? "" : $is_option_unique;
                $this->form_validation->set_rules('option_id', lang('option'), 'numeric' . $is_option_unique);
            } else {
                $is_item_unique = $this->check_unique('item_id', $this->input->post('item_id'), ['set_id' => $this->input->post('set_id')]) ? "|is_unique[prodcustsetoptions.item_id]" : "";
                $is_item_unique = !empty($existing) && $existing->item_id == $this->input->post('item_id') ? "" : $is_item_unique;
                $this->form_validation->set_rules('item_id', lang('item'), 'numeric' . $is_item_unique);
            }
            $this->form_validation->set_rules('set_id', lang('item'), 'required|numeric', array('required' => lang('field_required_msg')));
            return $this->form_validation->run();
        }
        return false;
    }
    public function build_entity($entity, $hub_id = null)
    {
        $unit = null;
        if (!empty($entity->modify_id)) {
            $row = $this->Prodmods->get($entity->modify_id, true, true);
            $entity->name = $this->Prodcustsetoptions->decode($row->name);
            $entity->base_price = isset($entity->base_price) ? $entity->base_price : $row->base_price;
            $entity->in_price = isset($entity->in_price) ? $entity->in_price : $row->in_price;
            $entity->out_price = isset($entity->out_price) ? $entity->out_price : $row->out_price;
            $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
            $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
            $entity->stock_id = null;
            $unit = null;
        } else if (!empty($entity->topping_id)) {
            $row = $this->Prodtops->get($entity->topping_id, true, true);
            $entity->name = $this->Prodcustsetoptions->decode($row->name);
            $unit = $this->Cfgunits->get($row->unit_id, true, true);
            $entity->stock_id = $row->stock_id;
            $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
            $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
        } else if (!empty($entity->option_id)) {
            $row = $this->Prodoptions->get($entity->option_id, true, true);
            $row->name = $this->Prodcustsetoptions->decode($row->name);
            $item = $this->Proditems->get($entity->item_id, true);
            $item->name = $this->Prodcustsetoptions->decode($item->name);
            $entity->name = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
            $unit = $this->Cfgunits->get($row->unit_id, true, true);
            $entity->stock_id = $row->stock_id;
            $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
            $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
        } else {
            $row = $this->Proditems->get($entity->item_id, true, true);
            $entity->name = $this->Prodcustsetoptions->decode($row->name);
            $unit = $this->Cfgunits->get($row->unit_id, true, true);
            $entity->stock_id = $row->stock_id;
            $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
            $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
        }
        if (!empty($unit)) {
            $unit->name = $this->Prodcustsetoptions->decode($unit->name);
            $unit->short = $this->Prodcustsetoptions->decode($unit->short);
            $entity->unit = $unit;
        } else {
            $entity->unit['name'] = ['en' => "", 'bn' => ""];
            $entity->unit['short'] = ['en' => "", 'bn' => ""];
            $entity->unit['id'] = 0;
        }
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
}
