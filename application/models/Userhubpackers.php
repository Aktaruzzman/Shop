
<?php

class Userhubpackers extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'userhubpackers';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Users');
        $this->load->model('Userhubs');
        $this->load->model('Prodbases');
    }
    public function validate()
    {
        $this->form_validation->set_rules('hub_id', 'Hub', 'required|numeric');
        $this->form_validation->set_rules('packer_id', 'User', 'required|numeric');
        return $this->form_validation->run();
    }

    public function build_entity($entity)
    {
        if (!empty($entity)) {
            $entity->packer_name = $this->decode($this->Users->get($entity->packer_id, TRUE, TRUE)->name);
            $entity->hub_name = $this->decode($this->Userhubs->get($entity->hub_id, TRUE, TRUE)->name);
            if ($entity->base_id) $entity->base_name = $this->decode($this->Prodbases->get($entity->base_id, TRUE, TRUE)->name);
            else $entity->base_name = null;
        }
        return $entity;
    }
}
