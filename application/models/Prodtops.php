
<?php

class Prodtops extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodtops';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
        $this->_order_type = "ASC";
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en')) ? "|is_unique[prodtops.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn')) ? "|is_unique[prodtops.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('name') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('name') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('base_price', lang('base') . ' ' . lang('price'), 'numeric');
        $this->form_validation->set_rules('in_price', lang('in') . ' ' . lang('price'), 'numeric');
        $this->form_validation->set_rules('out_price', lang('out') . ' ' . lang('price'), 'numeric');
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        $this->form_validation->set_rules('supplier_id', lang('supplier'), 'required|numeric');
        return $this->form_validation->run();
    }
    public function build_entity($entity, $hub_id = null)
    {
        $entity->name = $this->decode($entity->name);
        $unit = $this->Cfgunits->get($entity->unit_id, true, true, ['id', 'name', 'short']);
        $unit->name = $this->Cfgunits->decode($unit->name);
        $unit->short = $this->Cfgunits->decode($unit->short);
        $entity->unit = $unit;
        $entity->stock = $this->Stockdefines->get($entity->stock_id, true, true, ['id', 'maintain_stock', 'warn_below_qty', 'stop_sale_qty']);
        $entity->stock->balance = $this->Stockentries->get_by(['stock_id' => $entity->stock_id], true, true, 1, 0, null, ['balance'])->balance;
        $entity->hub_id = !empty($hub_id) ? (int) $hub_id : (int) getallheaders()['hub_id'];
        if (!empty($entity->hub_id)) {
            if ((config_item('store_type') === "multiple" && config_item('stock_type') !== "multiple") || (config_item('store_type') !== "multiple" && config_item('stock_type') == "multiple")) {
                $hubentry = $this->Stockhubentries->get_by(['stock_id' => $entity->stock->id, 'hub_id' => $entity->hub_id], true, true);
                if (!empty($hubentry))  $entity->stock->balance = $hubentry->balance;
                else $entity->stock->balance = 0;
                $hubprice = $this->Stockhubprices->get_by(['stock_id' => $entity->stock->id, 'hub_id' => $entity->hub_id], true, true);
                if (!empty($hubprice) && !$entity->edit) {
                    $entity->base_price = $hubprice->base_price;
                    $entity->in_price = $hubprice->in_price;
                    $entity->out_price = $hubprice->out_price;
                }
            }
        }
        $entity->photos['full'] = !empty($entity->photo) ? site_url('/uploads/product/item/' . $entity->photo) : './img/no-image.png';
        $entity->photos['thumb'] = !empty($entity->thumb) ? site_url('/uploads/product/item/thumbnail/' . $entity->thumb) : './img/no-image.png';
        return $entity;
    }
    public function define_stock($id)
    {
        $this->load->model('Stockdefines');
        $this->load->model('Stocklocations');
        $this->load->model('Stockentries');
        $stock_id = $this->Stockdefines->save(['topping_id' => $id]);
        $loc = $this->Stocklocations->get_by(['default' => TRUE], TRUE, TRUE);
        $this->Stockentries->save(['stock_id' => $stock_id, 'stockloc_id' => $loc->id]);
        return ['stock_id' => $stock_id];
    }
}
