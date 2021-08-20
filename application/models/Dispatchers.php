
<?php

class Dispatchers extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'dispatchers';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "ASC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Packers');
    }
    public function validate()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('hub_id', lang('hub'), 'required|numeric');
            $this->form_validation->set_rules('sale_id', lang('sale'), 'required');
            $this->form_validation->set_rules('event', lang('event'), 'required|in_list[driver,packer,printer,online]');
            $this->form_validation->set_rules('document', lang('document'), 'required|in_list[bill,invoice,packer]');
            $this->form_validation->set_rules('sent_type', lang('sent') . ' ' . lang('type'), 'in_list[all,new,resend]');
            return $this->form_validation->run();
        }
        return true;
    }
    public function dispatch($data = [])
    {
        if ($data['event'] === "printer") null;
        if ($data['event'] === "packer")  return $this->Packers->disperse($data);
        if ($data['event'] === "driver") null;
        if ($data['event'] === "online")  null;
    }
}
