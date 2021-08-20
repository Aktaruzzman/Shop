
<?php

class Pages extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'pages';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = 'ASC';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
    }
    public function validate()
    {
        $is_title_en_unique = $this->check_unique('title_en', $this->input->post('title_en')) ? "|is_unique[pages.title_en]" : "";
        $is_title_bn_unique = $this->check_unique('title_bn', $this->input->post('title_bn')) ? "|is_unique[pages.title_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), true);
            $is_title_en_unique = strtolower($this->input->post('title_en')) == strtolower($existing->title_en) ? "" : $is_title_en_unique;
            $is_title_bn_unique = trim($this->input->post('title_bn')) == trim($existing->title_bn) ? "" : $is_title_bn_unique;
        }
        $this->form_validation->set_rules('title_en', lang('name') . ' ' . lang('english'), 'required' . $is_title_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('title_bn', lang('name') . ' ' . lang('bangla'), 'required' . $is_title_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('subtitle_en', lang('subtitle') . ' ' . lang('english'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('subtitle_bn', lang('subtitle') . ' ' . lang('bangla'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
}
