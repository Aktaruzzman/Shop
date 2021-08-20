
<?php

class Employees extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'employees';
        $this->_primary_key = 'id';
        $this->_order_by = 'name_en';
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Userhubs');
    }
    public function validate()
    {
        $is_phone_unique = $this->check_unique('phone', $this->input->post('phone'), ['hub_id' => $this->input->post('hub_id')]) ? "|is_unique[employees.phone]" : "";
        $is_email_unique = $this->check_unique('email', $this->input->post('email'), ['hub_id' => $this->input->post('hub_id')]) ? "|is_unique[employees.email]" : "";
        if (!empty($this->input->post('nid'))) {
            $is_nid_unique = $this->check_unique('nid', $this->input->post('nid')) ? "|is_unique[employees.nid]" : "";
        }
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_phone_unique = trim($this->input->post('phone')) == trim($existing->phone) ? "" : $is_phone_unique;
            $is_email_unique = trim($this->input->post('email')) == trim($existing->email) ? "" : $is_email_unique;
            if (!empty($this->input->post('nid'))) {
                $is_nid_unique = trim($this->input->post('nid')) == trim($existing->nid) ? "" : $is_nid_unique;
            }
        }
        $this->form_validation->set_rules('name_en', lang('name') . ' ' . lang('english'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('name') . ' ' . lang('bangla'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('phone', lang('phone'), 'required' . $is_phone_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email' . $is_email_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('gender', lang('gender'), 'inlist[male,female]');
        $this->form_validation->set_rules('address_en', lang('address') . ' ' . lang('english'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('address_bn', lang('address') . ' ' . lang('bangla'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('salary', lang('salary'), 'required|numeric');
        $this->form_validation->set_rules('post', lang('post'), 'required');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) $entity['name'] = $this->encode(['en' => $entity['name_en'], 'bn' => $entity['name_bn']]);
        if (array_key_exists('address_en', $entity) && array_key_exists('address_bn', $entity)) $entity['address'] = $this->encode(['en' => $entity['address_en'], 'bn' => $entity['address_bn']]);
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
        $entity->name = $this->decode($entity->name);
        $entity->address = $this->decode($entity->address);
        return $entity;
    }
    public function build_config($hub_id)
    {
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false);
        foreach ($results['hubs'] as $index => $hub) {
            $results['hubs'][$index] = $this->Userhubs->build_entity($hub);
            if ($hub_id == $hub->id) $results['active_hub'] = $results['hubs'][$index];
        }
        return $results;
    }
}
