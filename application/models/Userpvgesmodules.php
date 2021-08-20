
<?php

class Userpvgesmodules extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'userpvgesmodules';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Userpvgesactions');
        $this->load->model('Users');
    }

    public function validate()
    {
        $this->form_validation->set_rules('id', lang('id'), 'numeric|required');
        $this->form_validation->set_rules('user_id', lang('user'), 'numeric|required');
        $this->form_validation->set_rules('name', lang('name'), 'required');
        return $this->form_validation->run();
    }
    function build_entity($entity)
    {
        $mactions = (array) $this->Userpvgesactions->get_by(['user_id' => $entity->user_id], TRUE, TRUE);
        $modules = get_object_vars($entity);
        foreach ($modules as $k => $v) {
            if ($k != 'id' && $k != 'user_id' && $k != 'created_at' &&  $k != 'updated_at' && $k != 'deleted_at') {
                $modules[$k] = new stdClass();
                $modules[$k]->id = $modules['id'];
                $modules[$k]->user_id = $modules['user_id'];
                $modules[$k]->status = $v;
                $modules[$k]->field = $k;
                $modules[$k]->actions = !empty($mactions) && !empty($mactions[$k]) ? $this->Userpvgesactions->decode($mactions[$k], TRUE) : [];
            }
        }
        unset($modules['id']);
        unset($modules['user_id']);
        unset($modules['created_at']);
        unset($modules['updated_at']);
        unset($modules['deleted_at']);
        return $modules;
    }
    function extractions($entity)
    {
        $action = $entity;

        $module['name'] = $entity['name'];
        $module['id'] = $entity['id'];
        $module['user_id'] = $entity['user_id'];
        $module['status'] = $entity['status'];

        unset($action['id']);
        unset($action['user_id']);
        unset($action['name']);
        unset($action['status']);

        return ["action" => $action, "module" => $module];
    }
}
