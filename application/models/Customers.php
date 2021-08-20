
<?php

class Customers extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'customers';
        $this->_primary_key = 'id';
        $this->_order_by = 'name_en';
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Subscribers');
    }
    public function validate()
    {
        $is_phone_unique = $this->check_unique('phone', $this->input->post('phone')) ? "|is_unique[customers.phone]" : "";
        $is_email_unique = $this->check_unique('email', $this->input->post('email')) ? "|is_unique[customers.email]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), TRUE);
            $is_phone_unique = trim($this->input->post('phone')) == trim($existing->phone) ? "" : $is_phone_unique;
            $is_email_unique = trim($this->input->post('email')) == trim($existing->email) ? "" : $is_email_unique;
        }
        $this->form_validation->set_rules('name_en', lang('customer') . ' ' . lang('english'), 'required|min_length[4]|max_length[50]', array('required' => sprintf(lang('field_required_msg'), lang('name'))));
        $this->form_validation->set_rules('name_bn', lang('customer') . ' ' . lang('bangla'), 'required|min_length[4]|max_length[50]', array('required' => sprintf(lang('field_required_msg'), lang('name'))));
        $this->form_validation->set_rules('phone', lang('phone'), 'required' . $is_phone_unique, array('required' => sprintf(lang('field_required_msg'), lang('phone'))));
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email' . $is_email_unique, array('required' => sprintf(lang('field_required_msg'), lang('email'))));
        $this->form_validation->set_rules('gender', lang('gender'), 'inlist[male,female]');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) $entity['name'] = $this->encode(['en' => $entity['name_en'], 'bn' => $entity['name_bn']]);
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

    public function validate_signup()
    {
        $this->form_validation->set_rules('name_en', lang('name'), 'required|min_length[4]|max_length[50]', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('phone', lang('phone'), 'required', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email', array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('tos', lang('terms_of_use'), 'required', array('required' => lang('field_required_msg')));
        return $this->form_validation->run();
    }
    public function validate_login()
    {
        $this->form_validation->set_rules('username', lang('phone') . '/' . lang('email'), 'trim|required', array('required' => sprintf(lang('field_required_msg'), lang('phone') . '/' . lang('email'))));
        $this->form_validation->set_rules('password', lang('password'), 'required', array('required' => sprintf(lang('field_required_msg'), lang('password'))));
        return $this->form_validation->run();
    }

    function build_entity($entity, $address)
    {
        if (!empty($entity)) {
            if (!empty($address)) {
                $addresses = $this->Customeraddresses->get_by(['cust_id' => $entity->id, 'status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'label', 'area_id', 'house']);
                foreach ($addresses as $index => $add) {
                    $addresses[$index]->label = $this->decode($add->label);
                    $addresses[$index]->area =  $this->Geoareas->formatted($add->area_id)->formatted;
                    $addresses[$index]->house = $this->decode($add->house);
                }
                $entity->addresses = $addresses;
                unset($entity->password);
            }
            $entity->name = $this->decode($entity->name);
        }
        return $entity;
    }

    public function signup($entity)
    {
        $customer = $this->get(['phone' => $entity['phone']], true);
        $entity['password'] = md5($entity['password']);
        $entity['name_bn'] = $entity['name_en'];
        $entity['name'] = $this->encode(['en' => $entity['name_en'], 'bn' => $entity['name_bn']]);
        $status = false;
        if (!empty($customer)) {
            if (empty($customer->tos) || !$customer->tos) $status = $this->save($entity, $customer->id);
            else $status = 'account_exists';
        } else $status = $this->save($entity);

        if ($status && $status !== 'account_exists') {
            if (!empty($this->input->post('sep'))) {
                $subscriber = $this->Subscribers->get_by(['phone' => $this->input->post('phone')], true, true);
                if (!empty($subscriber)) {
                    $this->Subscribers->save(['phone' => $entity['phone'], 'email' => $entity['email'], 'cust_id' => $status], $subscriber->id);
                } else $this->Subscribers->save(['phone' => $entity['phone'], 'email' => $entity['email'], 'cust_id' => $status], $subscriber->id);
            }
            $this->session->set_userdata('customerId', !empty($customer) ? $customer->id : $status);
            set_cookie("__foodie", $this->encryption->encrypt($this->session->userdata('customerId')), 365 * 50 * 3600 * 24);
        }
        return $status;
    }
    public function login($data)
    {
        $customer = $this->get_by(['status' => 'active', 'password' => md5($data['password'])], true, false, null, null, $data['username'], ['email', 'phone']);
        if (!empty($customer)) {
            $this->session->set_userdata('customerId', $customer->id);
            $this->Cookie_Model->set_foodie();
            $this->session->unset_userdata('username');
        } else {
            $this->session->set_userdata('username', $data['username']);
            $this->session->set_flashdata(array('warning_msg' => lang('invalid_email_password'), 'username' => $data['username']));
        }
    }
    function precheckCustomer($email, $phone)
    {
        $this->db->where('email', $email);
        $this->db->or_where('phone', $phone);
        return $this->db->get('customers')->result();
    }
    function get_by_key($key)
    {
        return $this->db->where('email', $key)->or_where('phone', $key)->get('customers')->row();
    }
    function getMe()
    {
        $customer = $this->db->where('id', $this->session->userdata('customerId'))->get('customers')->row_array();
        $customer['name'] = json_decode($customer['name'], TRUE);
        return $customer;
    }

    function get_subscriber($phone, $email = null)
    {
        $this->db->where('phone', $phone);
        !empty($email) ? $this->db->or_where('email', $email) : true;
        return $this->db->get('subscribers')->row();
    }
}
