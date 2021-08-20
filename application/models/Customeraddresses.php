
<?php

class Customeraddresses extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'customeraddresses';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Customers');
        $this->load->model('Geoareas');
    }
    public function validate()
    {
        $is_label_en_unique = $this->check_unique('label_en', $this->input->post('label_en'), ['area_id' => $this->input->post('area_id'), 'cust_id' => $this->input->post('cust_id')]) ? "|is_unique[customeraddresses.label_en]" : "";
        $is_label_bn_unique = $this->check_unique('label_bn', $this->input->post('label_bn'), ['area_id' => $this->input->post('area_id'), 'cust_id' => $this->input->post('cust_id')]) ? "|is_unique[customeraddresses.label_bn]" : "";
        $is_house_en_unique = $this->check_unique('house_en', $this->input->post('house_en'), ['area_id' => $this->input->post('area_id'), 'cust_id' => $this->input->post('cust_id')]) ? "|is_unique[customeraddresses.house_en]" : "";
        $is_house_bn_unique = $this->check_unique('house_bn', $this->input->post('house_bn'), ['area_id' => $this->input->post('area_id'), 'cust_id' => $this->input->post('cust_id')]) ? "|is_unique[customeraddresses.house_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_label_en_unique = strtolower($this->input->post('label_en')) == strtolower($existing->label_en) ? "" : $is_label_en_unique;
            $is_label_bn_unique = trim($this->input->post('label_bn')) == trim($existing->label_bn) ? "" : $is_label_bn_unique;
            $is_house_en_unique = strtolower($this->input->post('house_en')) == strtolower($existing->house_en) ? "" : $is_label_en_unique;
            $is_house_bn_unique = trim($this->input->post('house_bn')) == trim($existing->house_bn) ? "" : $is_house_bn_unique;
        }
        $this->form_validation->set_rules('label_en', lang('label') . ' ' . lang('english'), 'required' . $is_label_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('label_bn', lang('label') . ' ' . lang('bangla'), 'required' . $is_label_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('house_en', lang('house') . ' ' . lang('english'), 'required' . $is_house_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('house_bn', lang('house') . ' ' . lang('bangla'), 'required' . $is_house_bn_unique, array('required' => lang('field_required_msg')));

        $this->form_validation->set_rules('area_id', lang('area'), 'required|integer', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('cust_id', lang('customer'), 'required|integer', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        $entity['label'] = $this->encode(['en' => $entity['label_en'], 'bn' => $entity['label_bn']]);
        $entity['house'] = $this->encode(['en' => $entity['house_en'], 'bn' => $entity['house_bn']]);
        $order_by = $this->orderBy();
        if (!empty($entity['id'])) {
            if (!empty($entity[$order_by]) && $order_by === 'order') {
                $old_order = $this->get($entity['id'], TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
                unset($entity[$order_by]);
            }
        } else {
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
        }
        return $entity;
    }


    public function build_config($condition = null)
    {
        $results['areas'] = $this->Geoareas->list_areas($condition);
        return $results;
    }
    public function build_entity($entity)
    {
        if (!empty($entity)) {
            $entity->label = $this->decode($entity->label);
            $entity->house = $this->decode($entity->house);
            $entity->customer->name = $this->Customers->decode($entity->customer->name);
            $entity->area = $this->Geoareas->entity_area($this->Geoareas->get($entity->area_id, TRUE));
        }
        return $entity;
    }
}
