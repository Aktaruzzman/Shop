
<?php

class Userhubs extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'userhubs';
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
        $this->load->model('Geoareas');
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en')) ? "|is_unique[userhubs.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn')) ? "|is_unique[userhubs.name_bn]" : "";
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
    public function build_config()
    {
        $results['areas'] = $this->Geoareas->list_areas(null);
        return $results;
    }
    public function arrange_entity($entity)
    {
        $order_by = $this->orderBy();
        if (!empty($entity['id']) && (!empty($entity[$order_by]) && $order_by === 'order')) {
            $old_order = $this->get($entity['id'], TRUE)->order;
            if (intval($old_order) !== $entity[$order_by]) $this->sort($entity['id'], $old_order, $entity[$order_by]);
            unset($entity[$order_by]);
        } else {
            if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->get_max($order_by) + 1;
        }
        return $entity;
    }
    public function build_entity($entity)
    {
        $entity->name = $this->decode($entity->name);
        $entity->house = $this->decode($entity->house);
        $entity->area = $this->Geoareas->formatted($entity->area_id)->formatted;
        return $entity;
    }
}
