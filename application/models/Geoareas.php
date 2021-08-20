
<?php

class Geoareas extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'geoareas';
        $this->_primary_key = 'id';
        $this->_order_by = 'order';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Geoupazillas');
        $this->load->model('Geodistricts');
        $this->load->model('Geodivisions');
        $this->load->model('Userhubs');
    }
    public function validate()
    {
        $is_name_en_unique = $this->check_unique('name_en', $this->input->post('name_en'), ['upazilla_id' => $this->input->post('upazilla_id')]) ? "|is_unique[geoareas.name_en]" : "";
        $is_name_bn_unique = $this->check_unique('name_bn', $this->input->post('name_bn'), ['upazilla_id' => $this->input->post('upazilla_id')]) ? "|is_unique[geoareas.name_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_name_en_unique = strtolower($this->input->post('name_en')) == strtolower($existing->name_en) ? "" : $is_name_en_unique;
            $is_name_bn_unique = trim($this->input->post('name_bn')) == trim($existing->name_bn) ? "" : $is_name_bn_unique;
        }
        $this->form_validation->set_rules('name_en', lang('union') . ' ' . lang('english'), 'required' . $is_name_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('name_bn', lang('union') . ' ' . lang('bangla'), 'required' . $is_name_bn_unique, array('required' => lang('field_required_msg')));

        $this->form_validation->set_rules('upazilla_id', lang('upazilla'), 'required|integer', array(
            'required' => lang('field_required_msg'),
        ));
        return $this->form_validation->run();
    }
    public function get_areas($upazilla_id = null, $hub_id = null, $status = false)
    {
        $this->db->select();
        $this->db->from($this->Geoareas->getTable());
        if ($upazilla_id) $this->db->where('upazilla_id', $upazilla_id);
        if ($status) $this->db->where('status', 'active');
        if (config_item('store_type') === "multiple" && config_item('cross_area_service') === "no" && !empty($hub_id)) $this->db->where('hub_id', $hub_id);
        $this->db->order_by($this->_order_by, 'ASC');
        return $this->db->get()->result();
    }
    function formatted($id = NULL, $hub_id = null, $active = FALSE)
    {
        if ($id)
            return  $this->entity_area($this->get($id, TRUE, TRUE));
        else {
            $conditions = null;
            $areas = [];
            if (!empty($hub_id) && config_item('store_type') === "multiple") {
                $conditions['hub_id'] = $hub_id;
                $area = $this->Userhubs->get($hub_id, true, true)->area_id;
                $upazilla = $this->get($area, true, true)->upazilla_id;
                $areas = $this->get_areas($upazilla, $hub_id, $active);
            } else {
                if ($active) $conditions['status'] = 'active';
                $areas = $this->get($conditions, FALSE, $active ? FALSE : TRUE);
            }
            foreach ($areas as $k => $v) $areas[$k] = $this->entity_area($v);
            return $areas;
        }
    }
    function entity_area($entity)
    {
        if (!empty($entity)) {
            $formatted = array('en' => '', 'bn' => '');
            $entity->name = $this->decode($entity->name);
            $upazilla = $this->Geoupazillas->get($entity->upazilla_id, TRUE);
            $district = $this->Geodistricts->get($upazilla->district_id, TRUE);
            $division = $this->Geodivisions->get($district->division_id, TRUE);
            $entity->upazilla =  ['id' => $upazilla->id, 'name' =>  $this->decode($upazilla->name)];
            $entity->district =  ['id' => $district->id, 'name' => $this->decode($district->name)];
            $entity->division =  ['id' => $division->id, 'name' => $this->decode($division->name)];
            $formatted['en'] .= $entity->name_en;
            $formatted['bn'] .= $entity->name_bn;
            if (config_item('store_boundary') === 'country') {
                $formatted['en'] .= ', ' . $upazilla->name_en;
                $formatted['bn'] .= ', ' . $upazilla->name_bn;
                $formatted['en'] .= ', ' . $district->name_en;
                $formatted['bn'] .= ', ' . $district->name_bn;
                $formatted['en'] .= ', ' . $division->name_en;
                $formatted['bn'] .= ', ' . $division->name_bn;
            } else if (config_item('store_boundary') === 'division') {
                $formatted['en'] .= ', ' . $upazilla->name_en;
                $formatted['bn'] .= ', ' . $upazilla->name_bn;
                $formatted['en'] .= ', ' . $district->name_en;
                $formatted['bn'] .= ', ' . $district->name_bn;
                $formatted['en'] .= ', ' . $division->name_en;
                $formatted['bn'] .= ', ' . $division->name_bn;
            } else if (config_item('store_boundary') === 'district') {
                $formatted['en'] .= ', ' . $upazilla->name_en;
                $formatted['bn'] .= ', ' . $upazilla->name_bn;
                $formatted['en'] .= ', ' . $district->name_en;
                $formatted['bn'] .= ', ' . $district->name_bn;
            } else if (config_item('store_boundary') === 'upazilla') {
                $formatted['en'] .= ', ' . $upazilla->name_en;
                $formatted['bn'] .= ', ' . $upazilla->name_bn;
            }
            $entity->area_id = $entity->id;
            $entity->formatted = $formatted;
            $entity->formatted['area_id'] = $entity->id;
            $entity->formatted['area'] = ['id' => $entity->id, 'name' =>  $entity->name];
            $entity->formatted['upazilla'] = $entity->upazilla;
            $entity->formatted['district'] = $entity->district;
            $entity->formatted['division'] = $entity->district;
            return $entity;
        }
    }
    function list_areas($condition = null)
    {
        $areas = $this->Geoareas->get_by($condition);
        if (!empty($areas)) {
            foreach ($areas as $key => $entity) {
                $areas[$key] = $this->Geoareas->entity_area($entity);
            }
        }
        return $areas;
    }
}
