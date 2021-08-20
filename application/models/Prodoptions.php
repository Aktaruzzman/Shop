
<?php

class Prodoptions extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodoptions';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->load->model('Prodbases');
        $this->load->model('Prodcats');
        $this->load->model('Proditems');
        $this->load->model('Cfgtaxes');
        $this->load->model('Cfgdiscounts');
        $this->load->model('Cfgunits');
        $this->load->model('Suppliers');
        $this->load->model('Prodcourses');
        $this->load->model('prodtimings');
        $this->load->model('Stockdefines');
        $this->load->model('Stockentries');
        $this->load->model('Prodcodes');
        $this->load->model('Prodcustsets');
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Prodfixedsetoptions');
        $this->load->model('Prodfreeitems');
        $this->load->model('Discountmultis');
        $this->load->model('Stockdefines');
        $this->load->model('Stocklocations');
        $this->load->model('Stockhubentries');
        $this->load->model('Stockhubprices');
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en'), ['item_id' => $this->input->post('item_id')]) ? "|is_unique[prodoptions.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn'), ['item_id' => $this->input->post('item_id')]) ? "|is_unique[prodoptions.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), true);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('item') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('item') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('base_price', lang('base') . '' . lang('price'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('in_price', lang('in') . '' . lang('price'), 'numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('out_price', lang('out') . '' . lang('price'), 'numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('base_id', lang('base'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('category_id', lang('category'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('item_id', lang('item'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('tax_id', lang('vat'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('discount_id', lang('discount'), 'required|numeric', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        if (empty($entity['name_bn'])) $entity['name_bn'] = $entity['name_en'];
        $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
        if (empty($entity['description_bn'])) $entity['description_bn'] = $entity['description_en'];
        $entity['description'] = json_encode(array('en' => $entity['description_en'], 'bn' => $entity['description_bn']), JSON_UNESCAPED_UNICODE);
        $entity['show_in'] = array_key_exists('show_in', $entity) ? true : false;
        $entity['show_out'] = array_key_exists('show_out', $entity) ? true : false;
        $entity['breakfast'] = array_key_exists('breakfast', $entity) ? true : false;
        $entity['lunch'] = array_key_exists('lunch', $entity) ? true : false;
        $entity['dinner'] = array_key_exists('lunch', $entity) ? true : false;
        $entity['in_price'] = !empty($entity['in_price']) ? $entity['in_price'] : $entity['base_price'];
        $entity['out_price'] = !empty($entity['out_price']) ? $entity['out_price'] : $entity['base_price'];
        $order_by = $this->orderBy();
        if (!empty($entity['id'])) {
            if ($this->hasField('slug') && $this->get($entity['id'], true, true)->slug !== url_title($entity['name_en'], '-', true))
                $entity['slug'] = $this->slug($entity['name_en'], true);
            if (!empty($entity[$order_by]) && 'order' === $order_by) {
                $old_order = $this->get($entity['id'], true)->order;
                if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by], 'category_id', $entity['category_id']);
                unset($entity[$order_by]);
            }
        } else {
            if ($this->hasField('slug')) $entity['slug'] = $this->slug($entity['name_en'], true);
            if (!empty($order_by) && 'order' === $order_by) $entity[$order_by] = $this->get_max($order_by, ['category_id' => $entity['category_id']]) + 1;
        }
        return $entity;
    }
    public function build_entity($entity, $hub_id = null, $array = false)
    {
        $entity->name = $this->decode($entity->name, $array);
        $entity->description = $this->decode($entity->description, $array);
        $entity->base = $this->Prodbases->get($entity->base_id, true, true);
        $entity->base->name = $this->decode($entity->base->name, $array);
        $entity->category = $this->Prodcats->get($entity->category_id, true, true);
        $entity->category->name = $this->decode($entity->category->name, $array);
        $entity->category->discount = $this->Cfgdiscounts->get($entity->category->discount_id, true, true);
        $entity->category->tax = $this->Cfgtaxes->get($entity->category->tax_id, true, true);
        $entity->item = $this->Proditems->get($entity->item_id, true, true);
        $entity->item->name = $this->decode($entity->item->name, $array);

        $entity->category->discount = $this->Cfgdiscounts->get($entity->item->discount_id, true, true);
        $entity->category->tax = $this->Cfgtaxes->get($entity->item->tax_id, true, true);
        $entity->discount = $this->Cfgdiscounts->get($entity->discount_id, true, true);
        $entity->tax = $this->Cfgtaxes->get($entity->tax_id, true, true);
        $entity->unit = $this->Cfgunits->get($entity->unit_id, true, true);
        $entity->unit->name = $this->decode($entity->unit->name, $array);
        $entity->unit->short = $this->decode($entity->unit->short, $array);
        $entity->course = $this->Prodcourses->get($entity->course_id, true, true);
        $entity->course->name = $this->decode($entity->course->name, $array);
        $entity->supplier = $this->decode($this->Suppliers->get($entity->supplier_id, true)->name, $array);
        $entity->stock = $this->Stockdefines->get($entity->stock_id, true, true);
        $entity->stock->balance = $this->Stockentries->get_by(['stock_id' => $entity->stock_id], true, true, 1, 0, null, ['balance'])->balance;
        $entity->hub_id = !empty($hub_id) ? (int) $hub_id : (int) getallheaders()['hub_id'];
        if (!empty($entity->hub_id)) {
            if ((config_item('store_type') === "multiple" && config_item('stock_type') !== "multiple") || (config_item('store_type') !== "multiple" && config_item('stock_type') == "multiple")) {
                $hubentry = $this->Stockhubentries->get_by(['stock_id' => $entity->stock->id, 'hub_id' => $entity->hub_id], true, true);
                if (!empty($hubentry)) $entity->stock->balance = $hubentry->balance;
                else $entity->stock->balance = 0;
                $hubprice = $this->Stockhubprices->get_by(['stock_id' => $entity->stock->id, 'hub_id' => $entity->hub_id], true, true);
                if (!empty($hubprice) && !$entity->edit) {
                    $entity->base_price = $hubprice->base_price;
                    $entity->in_price = $hubprice->in_price;
                    $entity->out_price = $hubprice->out_price;
                }
            }
        }
        $entity->photos['full'] = !empty($entity->photo) ? site_url('/uploads/product/item/' . $entity->photo) :  site_url('/uploads/product/item/' . $entity->item->photo);
        $entity->photos['thumb'] = !empty($entity->thumb) ? site_url('/uploads/product/item/thumbnail/' . $entity->thumb) :  site_url('/uploads/product/item/thumbnail/' . $entity->item->thumb);
        return $entity;
    }
    public function build_config($base)
    {
        $results = [];
        $results['suppliers'] = $this->Suppliers->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name']);
        foreach ($results['suppliers'] as $index => $obj) $results['suppliers'][$index]->name = $this->decode($obj->name);
        $results['taxes'] = $this->Cfgtaxes->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'value', 'default']);
        $results['discounts'] = $this->Cfgdiscounts->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'value', 'func', 'default']);
        $results['units'] = $this->Cfgunits->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'short', 'default']);
        foreach ($results['units'] as $index => $obj) {
            $results['units'][$index]->name = $this->decode($obj->name);
            $results['units'][$index]->short = $this->decode($obj->short);
        }
        $results['courses'] = $this->Prodcourses->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'default']);
        foreach ($results['courses'] as $index => $obj) $results['courses'][$index]->name = $this->decode($obj->name);
        $results['prodtiming'] = $this->prodtimings->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'default']);
        foreach ($results['prodtiming'] as $index => $obj) $results['prodtiming'][$index]->name = $this->decode($obj->name);

        $item = $this->Proditems->get($base, TRUE, TRUE);
        $results['category'] = $item ? $this->Prodcats->get($item->category_id, true, true) : null;
        if (!empty($results['category'])) {
            $results['category']->name = $this->decode($results['category']->name);
            $results['category']->unit =  $this->Cfgunits->get($results['category']->unit_id, true, true);
            $results['category']->unit->short = $this->decode($results['category']->unit->short);
        }


        return $results;
    }

    public function get_options($id = null, $base = null, $deleted = null, $limit = null, $offset = 0)
    {
        $this->db->select('PO.*,prodbases.name as base,prodcats.name as category,proditems.name as item,cfgtaxes.value as tax,cfgdiscounts.value as discount,cfgdiscounts.func');
        $this->db->from('prodoptions as PO');
        $this->db->join('prodbases as prodbases', 'prodbases.id=PO.base_id');
        $this->db->join('prodcats as prodcats', 'prodcats.id=PO.category_id');
        $this->db->join('proditems as proditems', 'proditems.id=PO.item_id');
        $this->db->join('cfgtaxes as cfgtaxes', 'cfgtaxes.id=PO.tax_id');
        $this->db->join('cfgdiscounts as cfgdiscounts', 'cfgdiscounts.id=PO.discount_id');
        if (!empty($base)) $this->db->where('prodoptions.item_id=', $base);
        if (!empty($id)) $this->db->where('prodoptions.id', $id);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('prodoptions.' . $this->_deleted_at, false);
        if (!empty($this->_order_by)) $this->db->order_by('prodoptions.' . $this->_order_by, $this->_order_type);
        if ($limit) $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }
    public function co_update($option_id, $type = 'undo', $data = [])
    {
        if (!empty($data)) {
            $this->Prodcodes->save_by($data, ['option_id' => $option_id]);
            $this->Prodcustsetoptions->save_by($data, ['option_id' => $option_id]);
            $this->Prodcustsets->save_by($data, ['hook_option_id' => $option_id]);
            $this->Prodfixedsetoptions->save_by($data, ['hook_option_id' => $option_id]);
            $this->Prodfixedsetoptions->save_by($data, ['option_id' => $option_id]);
            $this->Prodfreeitems->save_by($data, ['option_id' => $option_id]);
            $this->Discountmultis->save_by($data, ['option_id' => $option_id]);
        } else {
            $this->Prodcodes->save_by([$this->Prodcodes->deletedAt() => 'undo' === $type ? false : true], ['option_id' => $option_id]);
            $this->Prodcustsetoptions->save_by([$this->Prodcustsetoptions->deletedAt() => 'undo' === $type ? false : true], ['option_id' => $option_id]);
            $this->Prodcustsets->save_by([$this->Prodcustsets->deletedAt() => 'undo' === $type ? false : true], ['hook_option_id' => $option_id]);
            $this->Prodfixedsetoptions->save_by([$this->Prodfixedsetoptions->deletedAt() => 'undo' === $type ? false : true], ['hook_option_id' => $option_id]);
            $this->Prodfixedsetoptions->save_by([$this->Prodfixedsetoptions->deletedAt() => 'undo' === $type ? false : true], ['option_id' => $option_id]);
            $this->Prodfreeitems->save_by([$this->Prodfreeitems->deletedAt() => 'undo' === $type ? false : true], ['option_id' => $option_id]);
            $this->Discountmultis->save_by([$this->Discountmultis->deletedAt() => 'undo' === $type ? false : true], ['option_id' => $option_id]);
        }
    }
    public function define_stock($id, $data)
    {
        $stock_id = $this->Stockdefines->save(['base_id' => $data['base_id'], 'category_id' => $data['category_id'], 'item_id' => $data['item_id'], 'option_id' => $id]);
        $loc = $this->Stocklocations->get_by(['default' => true], true, true);
        $this->Stockentries->save(['stock_id' => $stock_id, 'stockloc_id' => $loc->id]);
        $code_id = $this->Prodcodes->save(['category_id' => $data['category_id'], 'item_id' => $data['item_id'], 'option_id' => $id, 'source' => 'option']);
        return ['stock_id' => $stock_id, 'code_id' => $code_id];
    }
}
