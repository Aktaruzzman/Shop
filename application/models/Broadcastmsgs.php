
<?php

class Broadcastmsgs extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'broadcastmsgs';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
    }
    public function validate()
    {
        $this->form_validation->set_rules('subject', 'Subject', 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('message', 'Message', 'required', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }

    public function sms($phones, $message)
    {
        $message .= '-' . config_item('shop_name_en');
        if (!empty($phones)) {
            foreach ($phones as $obj) {
                sms($obj->phone, $message);
            }
        }
    }
    public function email($emails, $subject, $message)
    {
        if (!empty($emails)) {
            $email_array = [];
            foreach ($emails as $obj) {
                array_push($email_array, $obj->email);
            }
            $data['to'] = $email_array;
            $data['recipent'] = '';
            $data['recipent_type'] = "customer";
            $data['from'] = 'noreply@' . get_domain();
            $data['from_title'] = config_item('shop_name_en');
            $data['subject'] = $subject;
            $data['message'] = $message;
            email($data);
        }
    }
}
