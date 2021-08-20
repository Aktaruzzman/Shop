
<?php

class Prodcats extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodcats';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
        $this->_order_type = 'ASC';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Prodbases');
        $this->load->model('Cfgtaxes');
        $this->load->model('Cfgdiscounts');
        $this->load->model('Cfgunits');
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en'), ['base_id' => $this->input->post('base_id')]) ? "|is_unique[prodcats.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn'), ['base_id' => $this->input->post('base_id')]) ? "|is_unique[prodcats.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('category') . ' ' . lang('english'), 'trim|required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('category') . ' ' . lang('bangla'), 'trim|required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('base_id', lang('base'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('tax_id', lang('vat'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('discount_id', lang('discount'), 'required|numeric', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function build_config($base)
    {
        $results = [];
        if (!empty($base)) $results['base'] = $this->Prodbases->get($base, true, true, ['id', 'name', 'default', 'status']);
        if (!empty($results['base'])) $results['base']->name = $this->decode($results['base']->name);
        $results['bases'] = $this->Prodbases->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'status', 'default', 'deleted_at']);
        foreach ($results['bases'] as $index => $obj) {
            $results['bases'][$index]->name = $this->decode($obj->name);
        }
        $results['taxes'] = $this->Cfgtaxes->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'value', 'default', 'deleted_at']);
        $results['discounts'] = $this->Cfgdiscounts->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'value', 'func', 'default', 'deleted_at']);

        $results['units'] = $this->Cfgunits->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'short', 'default']);
        foreach ($results['units'] as $index => $obj) {
            $results['units'][$index]->name = $this->decode($obj->name);
            $results['units'][$index]->short = $this->decode($obj->short);
        }
        return $results;
    }

    public function arrange_entity($entity)
    {
        $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
        $entity['description'] = json_encode(array('en' => !empty($entity['description_en']) ? $entity['description_en'] : '', 'bn' => !empty($entity['description_bn']) ? $entity['description_bn'] : ''), JSON_UNESCAPED_UNICODE);
        $entity['show_in'] = !empty($entity['show_in']) ? $entity['show_in'] : false;
        $entity['show_out'] = !empty($entity['show_out']) ? $entity['show_out'] : false;
        $order_by = $this->Prodcats->orderBy();
        if (!empty($entity['id'])) {
            if ($this->hasField('slug') && $this->get($entity['id'], true, true)->slug !== url_title($entity['name_en'], '-', true))
                $entity['slug'] = $this->slug($entity['name_en'], true);
            if (!empty($entity[$order_by]) && 'order' === $order_by) {
                $old_order = $this->Prodcats->get($entity['id'], true)->order;
                if (intval($old_order) !== $entity[$order_by]) $this->Prodcats->sort($entity['id'], $old_order, $entity[$order_by]);
                unset($entity[$order_by]);
            }
        } else {
            if ($this->hasField('slug')) $entity['slug'] = $this->slug($entity['name_en'], true);
            if (!empty($order_by) && 'order' === $order_by) $entity[$order_by] = $this->Prodcats->get_max($order_by) + 1;
        }
        return $entity;
    }
    public function build_entity($entity, $array = false)
    {
        if (!empty($entity)) {
            $entity->name = $this->decode($entity->name, $array);
            $entity->description = $this->decode($entity->description, $array);
            $entity->base = $this->Prodbases->get($entity->base_id, true, true, ['id', 'name', 'default', 'status']);
            $entity->base->name = $this->decode($entity->base->name, $array);
            $entity->discount = $this->Cfgdiscounts->get($entity->discount_id, true, true, ['id', 'value', 'func']);
            $entity->tax = $this->Cfgtaxes->get($entity->tax_id, true, true, ['id', 'value']);
            $entity->photos['full'] = !empty($entity->photo) ? site_url('/uploads/product/category/' . $entity->photo) : './img/no-image.png';
            $entity->photos['thumb'] = !empty($entity->thumb) ? site_url('/uploads/product/category/thumbnail/' . $entity->thumb) : './img/no-image.png';
        }
        return $entity;
    }
    public function get_categories($id = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = 0)
    {
        $this->db->select('prodcats.*,cfgtaxes.value as tax,cfgdiscounts.value as discount,cfgdiscounts.func,prodbases.name as base');
        $this->db->from('prodcats');
        $this->db->join('cfgtaxes', 'cfgtaxes.id=prodcats.tax_id');
        $this->db->join('cfgdiscounts', 'cfgdiscounts.id=prodcats.discount_id');
        $this->db->join('prodbases', 'prodbases.id=prodcats.base_id');
        if (!empty($base)) $this->db->where('prodcats.base_id=', $base);
        if (!empty($id)) $this->db->where('prodcats.id', $id);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where('prodcats.' . $this->_deleted_at, false);
        if (!empty($this->_order_by)) $this->db->order_by('prodcats.' . $this->_order_by, $this->_order_type);
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
    public function co_update($category_id, $type = 'undo', $data = [])
    {
        $this->load->model('Proditems');
        $this->load->model('Prodcodes');
        $this->load->model('Prodcustsets');
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Prodfixedsetoptions');
        $this->load->model('Prodfreeitems');
        $this->load->model('Discountmultis');
        if (!empty($data)) {
            $this->Prodcodes->save_by($data, ['category_id' => $category_id]);
            $items = $this->Proditems->get_by(['category_id' => $category_id]);
            foreach ($items as $item) {
                $this->Prodcustsetoptions->save_by($data,  ['item_id' => $item->id]);
                $this->Prodcustsets->save_by($data,  ['hook_item_id' => $item->id]);
                $this->Prodfixedsetoptions->save_by($data,  ['hook_item_id' => $item->id]);
                $this->Prodfixedsetoptions->save_by($data,  ['item_id' => $item->id]);
                $this->Prodfreeitems->save_by($data,  ['item_id' => $item->id]);
                $this->Discountmultis->save_by($data,  ['item_id' => $item->id]);
            }
        } else {
            $this->Prodcodes->save_by([$this->Prodcodes->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['category_id' => $category_id]);
            $items = $this->Proditems->get_by(['category_id' => $category_id]);
            foreach ($items as $item) {
                $this->Prodcustsetoptions->save_by([$this->Prodcustsetoptions->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['item_id' => $item->id]);
                $this->Prodcustsets->save_by([$this->Prodcustsets->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['hook_item_id' => $item->id]);
                $this->Prodfixedsetoptions->save_by([$this->Prodfixedsetoptions->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['hook_item_id' => $item->id]);
                $this->Prodfixedsetoptions->save_by([$this->Prodfixedsetoptions->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['item_id' => $item->id]);
                $this->Prodfreeitems->save_by([$this->Prodfreeitems->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['item_id' => $item->id]);
                $this->Discountmultis->save_by([$this->Discountmultis->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['item_id' => $item->id]);
            }
        }
    }
}
