
<?php

class Discountpromos extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'discountpromos';
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
        $this->load->model('Cfgdiscounts');
        $this->load->model('Cfgservices');
        $this->load->model('Prodbases');
    }
    public function validate()
    {
        $is_code_unique = $this->check_unique('code', $this->input->post('min_order')) ? "|is_unique[discountpromos.code]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_code_unique = $existing->code == $this->input->post('code') ? "" :  $is_code_unique;
        }
        $this->form_validation->set_rules('code', lang('promo') . ' ' . lang('code'), 'required|numeric' . $is_code_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('min_order', lang('minimum') . ' ' . lang('order'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('discount_id', lang('discount'), 'required|numeric', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('date_start', lang('date') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('date_end', lang('date') . ' ' . lang('end'), 'required');
        $this->form_validation->set_rules('time_start', lang('time') . ' ' . lang('start'), 'required');
        $this->form_validation->set_rules('time_end', lang('time') . ' ' . lang('end'), 'required');
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        $entity['table'] = !empty($entity['table']) ? $entity['table'] : 0;
        $entity['waiting'] = !empty($entity['waiting']) ? $entity['waiting'] : 0;
        $entity['collection'] = !empty($entity['collection']) ? $entity['collection'] : 0;
        $entity['delivery'] = !empty($entity['delivery']) ? $entity['delivery'] : 0;
        $entity['bar'] = !empty($entity['bar']) ? $entity['bar'] : 0;
        $entity['kitchen'] = !empty($entity['kitchen']) ? $entity['kitchen'] : 0;
        $entity['drinks'] = !empty($entity['drinks']) ? $entity['drinks'] : 0;
        $entity['desserts'] = !empty($entity['desserts']) ? $entity['desserts'] : 0;
        $entity['beverage'] = !empty($entity['beverage']) ? $entity['beverage'] : 0;
        $entity['alcohol'] = !empty($entity['alcohol']) ? $entity['alcohol'] : 0;
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
            $old_order = $this->get($entity['id'], TRUE, TRUE)->order;
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
        if (!empty($entity)) $entity->discount = $this->Cfgdiscounts->get($entity->discount_id, TRUE, FALSE, ['id', 'value', 'func']);
        return $entity;
    }
    public function build_config($results)
    {
        $results['discounts'] = $this->Cfgdiscounts->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'value', 'func', 'default']);
        $results['services'] = $this->Cfgservices->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'value', 'name', 'name_en', 'name_bn', 'default']);
        $results['bases'] = $this->Prodbases->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'value', 'name', 'name_en', 'name_bn', 'default']);
        foreach ($results['services'] as $index => $obj) {
            $results['services'][$index]->name = json_decode($obj->name);
            if ($obj->value === 'table') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->table : 1;
            if ($obj->value === 'waiting') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->waiting : 0;
            if ($obj->value === 'collection') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->collection : 0;
            if ($obj->value === 'delivery') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->delivery : 0;
            if ($obj->value === 'bar') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->bar : 0;
        }
        foreach ($results['bases'] as $index => $obj) {
            $results['bases'][$index]->name = json_decode($obj->name);
            if ($obj->value == 'kitchen') $results['bases'][$index]->radio =  !empty($results['entity']) ? $results['entity']->kitchen : 1;
            if ($obj->value == 'drinks') $results['bases'][$index]->radio =  !empty($results['entity']) ? $results['entity']->drinks : 0;
            if ($obj->value == 'desserts') $results['bases'][$index]->radio =  !empty($results['entity']) ? $results['entity']->desserts : 0;
            if ($obj->value == 'beverage') $results['bases'][$index]->radio =  !empty($results['entity']) ? $results['entity']->beverage : 0;
            if ($obj->value == 'alcohol') $results['bases'][$index]->radio =  !empty($results['entity']) ? $results['entity']->alcohol : 0;
        }
        return $results;
    }
}
