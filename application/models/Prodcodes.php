
<?php

class Prodcodes extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'prodcodes';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
    }
    public function validate()
    {
        $existing = $this->get($this->input->post('id'), TRUE);
        if (!empty($this->input->post('shortcode')) && !empty($this->input->post('id'))) {
            $is_shortcode_unique = $this->check_unique('shortcode', str_pad($this->input->post('shortcode'), 4, "0", STR_PAD_LEFT)) ? "|is_unique[prodcodes.shortcode]" : "";
            if (!empty($existing->shortcode) && $existing->shortcode === $this->input->post('shortcode')) $is_shortcode_unique = "";
            $this->form_validation->set_rules('shortcode', lang('shortcode'), 'required' . $is_shortcode_unique, array('required' => lang('field_required_msg')));
        }
        if (!empty($this->input->post('barcode')) && !empty($this->input->post('id'))) {
            $is_barcode_unique = $this->check_unique('barcode', $this->input->post('barcode')) ? "|is_unique[prodcodes.barcode]" : "";
            if (!empty($existing->barcode) && $existing->barcode === $this->input->post('barcode')) $is_barcode_unique = "";
            $this->form_validation->set_rules('barcode', lang('barcode'), 'required' . $is_barcode_unique, array('required' => lang('field_required_msg')));
        }
        return $this->form_validation->run();
    }
}
