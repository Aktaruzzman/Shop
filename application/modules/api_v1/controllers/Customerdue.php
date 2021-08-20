<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Customerdue extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customerdues');
            $this->load->model('Customers');
            $this->load->model('Sales');
        }
    }
    public function index_get($cust_id = NULL, $from = NULL, $to = NULL, $hub_id = NULL)
    {
        if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
        if (empty($to)) $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
        $results['list'] = $this->Customerdues->get_due_details($cust_id, $from, $to, $hub_id);
        $results['total_due'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['total_due'] += $obj->amount;
            $custoemr = $this->Customers->get($obj->cust_id, TRUE, TRUE, ['name', 'phone']);
            $results['list'][$index]->name = $this->Customers->decode($custoemr->name);
            $results['list'][$index]->phone = $custoemr->phone;
            $results['list'][$index]->invoice = $this->Sales->get($obj->sale_id, TRUE, TRUE, ['invoice'])->invoice;
        }
        $results['customers'] = $this->Customers->get(null, null, null, ['id', 'name', 'phone']);
        foreach ($results['customers'] as $k => $v) $results['customers'][$k]->name = $this->Customers->decode($v->name);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Customerdues->validate()) {
            $this->db->trans_start();
            $entity = $this->Customerdues->entity($this->post());
            if (!empty($entity['id'])) $results['status'] = $this->Customerdues->save($entity, $entity['id']);
            else  $results['status'] = $this->Customerdues->save($entity);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Customerdues->entity($this->put());
            $results['status'] = $this->Customerdues->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $results['status'] = $this->Customerdues->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerdues->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerdues->save([$this->Customerdues->deletedAt() => FALSE], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Customerdues->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
