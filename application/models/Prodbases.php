
<?php

class Prodbases extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodbases';
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
        $this->load->model('Users');
        $this->load->model('Userhubs');
        $this->load->model('Prodcats');
        $this->load->model('Proditems');
        $this->load->model('Prodcodes');
        $this->load->model('Prodcustsets');
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Prodfixedsetoptions');
        $this->load->model('Prodfreeitems');
        $this->load->model('Discountmultis');
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en')) ? "|is_unique[prodbases.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn')) ? "|is_unique[prodbases.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('name') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('name') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }

    public function arrange_entity($entity)
    {
        if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity))
            $entity['name'] = $this->encode(['en' => $entity['name_en'], 'bn' => $entity['name_bn']]);
        $order_by = $this->orderBy();
        if (!empty($entity['id'])) {
            if ($this->hasField('slug') && $this->get($entity['id'], true, true)->slug !== url_title($entity['name_en'], '-', true))
                $entity['slug'] = $this->slug($entity['name_en'], true);
            if ((!empty($entity[$order_by]) && $order_by === 'order')) {
                $old_order = $this->get($entity['id'], TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
                unset($entity[$order_by]);
            }
        } else {
            if ($this->hasField('slug')) $entity['slug'] = $this->slug($entity['name_en'], true);
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
        }
        return $entity;
    }

    public function build_entity($entity)
    {
        $entity->name = $this->decode($entity->name);
        if ($entity->packer_id) {
            $user = $this->Users->get($entity->packer_id, TRUE);
            $entity->packer_name = $this->Users->decode($user->name);
            $entity->hub_name = $this->Users->decode($this->Userhubs->get($user->hub_id, TRUE, TRUE, ['name'])->name);
        }
        return $entity;
    }

    public function co_update($base_id, $type = 'undo', $data = [])
    {
        $cats = $this->Prodcats->get_by(['base_id' => $base_id], FALSE, TRUE);
        if (!empty($data)) {
            foreach ($cats as $cat) {
                $this->Prodcodes->save_by($data, ['category_id' => $cat->id]);
                $items = $this->Proditems->get_by(['category_id' => $cat->id]);
                foreach ($items as $item) {
                    $this->Prodcustsetoptions->save_by($data,  ['item_id' => $item->id]);
                    $this->Prodcustsets->save_by($data,  ['hook_item_id' => $item->id]);
                    $this->Prodfixedsetoptions->save_by($data,  ['hook_item_id' => $item->id]);
                    $this->Prodfixedsetoptions->save_by($data,  ['item_id' => $item->id]);
                    $this->Prodfreeitems->save_by($data,  ['item_id' => $item->id]);
                    $this->Discountmultis->save_by($data,  ['item_id' => $item->id]);
                }
            }
        } else {
            foreach ($cats as $cat) {
                $this->Prodcodes->save_by([$this->Prodcodes->deletedAt() => $type === 'undo' ? FALSE : TRUE], ['category_id' => $cat->id]);
                $items = $this->Proditems->get_by(['category_id' => $cat->id]);
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
}
