
<?php

class Discountmultis extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'discountmulti';
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
        $this->load->model('Cfgservices');
        $this->load->model('Cfgunits');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
    }
    public function validate()
    {
        $is_name_id_unique = $this->check_unique('name_id', $this->input->post('name_id'), ['buy' => $this->input->post('buy'), 'get' => $this->input->post('get')]) ? "|is_unique[discountmulti.name_id]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_id_unique = (float) $existing->name_id === (float)$this->input->post('name_id') ? "" :  $is_name_id_unique;
        }
        $this->form_validation->set_rules('name_id', lang('item'), 'required' . $is_name_id_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('buy', lang('buy'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('get', lang('get'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('date_start', lang('date') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('date_end', lang('date') . ' ' . lang('end'), 'required');
        $this->form_validation->set_rules('time_start', lang('time') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('time_end', lang('time') . ' ' . lang('end'), 'required');
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        $explode = explode('_', $entity['name_id']);
        if (intval($explode[0]) > 0)  $entity['option_id'] = $explode[0];
        $entity['item_id'] = $explode[1];
        $entity['table'] = !empty($entity['table']) ? $entity['table'] : 0;
        $entity['waiting'] = !empty($entity['waiting']) ? $entity['waiting'] : 0;
        $entity['collection'] = !empty($entity['collection']) ? $entity['collection'] : 0;
        $entity['delivery'] = !empty($entity['delivery']) ? $entity['delivery'] : 0;
        $entity['bar'] = !empty($entity['bar']) ? $entity['bar'] : 0;
        $entity['Sun'] = !empty($entity['Sun']) ? $entity['Sun'] : 0;
        $entity['Mon'] = !empty($entity['Mon']) ? $entity['Mon'] : 0;
        $entity['Tue'] = !empty($entity['Tue']) ? $entity['Tue'] : 0;
        $entity['Wed'] = !empty($entity['Wed']) ? $entity['Wed'] : 0;
        $entity['Thu'] = !empty($entity['Thu']) ? $entity['Thu'] : 0;
        $entity['Fri'] = !empty($entity['Fri']) ? $entity['Fri'] : 0;
        $entity['Sat'] = !empty($entity['Sat']) ? $entity['Sat'] : 0;
        $entity['pos'] = !empty($entity['pos']) ? true : false;
        $entity['web'] = !empty($entity['web']) ? true : false;
        $order_by = $this->orderBy();
        if (!empty($entity['id']) && (!empty($entity[$order_by]) && $order_by === 'order')) {
            $old_order = $this->get($entity['id'], TRUE)->order;
            if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
            unset($entity[$order_by]);
        } else {
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
            if (empty($entity['hub_id'])) $entity['hub_id'] = getallheaders()['hub_id'];
        }
        return $entity;
    }


    public function build_entity($entity)
    {
        if (!empty($entity)) {
            $item = $this->Proditems->get($entity->item_id, TRUE, TRUE);
            $entity->unit = $this->decode($this->Cfgunits->get($item->unit_id, TRUE, TRUE)->short);
            $name = array('en' => '', 'bn' => '');
            if (!empty($entity->option_id)) {
                $option = $this->Prodoptions->get($entity->option_id, TRUE, TRUE);
                $name['en'] .= $option->name_en;
                $name['bn'] .= $option->name_bn;
                $entity->unit = $this->decode($this->Cfgunits->get($option->unit_id, TRUE, TRUE)->short);
            }
            $name['en'] = !empty($name['en']) ?   $name['en'] . ' ' . $item->name_en : $item->name_en;
            $name['bn'] = !empty($name['bn']) ?   $name['bn'] . ' ' . $item->name_bn : $item->name_bn;
            $entity->name = $name;
        }
        return $entity;
    }
    public  function build_config($entity)
    {
        $results['services'] = $this->Cfgservices->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'value', 'name', 'name_en', 'name_bn', 'default']);
        foreach ($results['services'] as $index => $obj) {
            $results['services'][$index]->name = json_decode($obj->name);
            if ($obj->value === 'table') $results['services'][$index]->radio =  !empty($entity) ?  $entity->table : 1;
            if ($obj->value === 'waiting') $results['services'][$index]->radio =  !empty($entity) ?  $entity->waiting : 0;
            if ($obj->value === 'collection') $results['services'][$index]->radio =  !empty($entity) ?  $entity->collection : 0;
            if ($obj->value === 'delivery') $results['services'][$index]->radio =  !empty($entity) ?  $entity->delivery : 0;
            if ($obj->value === 'bar') $results['services'][$index]->radio =  !empty($entity) ?  $entity->bar : 0;
        }
        $results['items'] = $this->Proditems->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name', 'name_en', 'name_bn', 'category_id']);
        foreach ($results['items'] as $index => $obj) {
            $results['items'][$index]->name = json_decode($obj->name);
            $results['items'][$index]->options = $this->Prodoptions->get_by(['item_id' => $obj->id, 'status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name', 'name_en', 'name_bn', 'category_id']);
            foreach ($results['items'][$index]->options as $i => $option) {
                $results['items'][$index]->options[$i]->name = json_decode($option->name);
            }
        }
        return $results;
    }
}
