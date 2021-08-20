<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Supplierdue extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Suppliers');
            $this->load->model('Supplieragents');
            $this->load->model('Supplierdues');
            $this->load->model('Purchases');
        }
    }
    public function index_get($supplieragent_id = null, $from = null, $to = null, $hub_id = null)
    {
        if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
        if (empty($to)) $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
        $results['list'] = $this->Supplierdues->get_due_details($supplieragent_id, $from, $to, $hub_id ? $hub_id : getallheaders()['hub_id']);
        $results['total_due'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['total_due'] += $obj->amount;
            $supplier = $this->Suppliers->get($obj->supplier_id, true, true, ['id', 'name', 'phone', 'email']);

            $results['list'][$index]->supplier_name = $this->Suppliers->decode($supplier->name);
            $results['list'][$index]->supplier_phone = $supplier->phone;

            $supplieragent = $this->Supplieragents->get($obj->supplieragent_id, true, true, ['id', 'name', 'phone', 'email']);
            $results['list'][$index]->agent_name = $this->Suppliers->decode($supplieragent->name);
            $results['list'][$index]->agent_phone = $supplieragent->phone;

            $results['list'][$index]->invoice = $this->Purchases->get($obj->purchase_id, true, true, ['invoice'])->invoice;
        }
        $results['suppliers'] = $this->Supplieragents->get_supplieragents($hub_id ? $hub_id : getallheaders()['hub_id']);
        foreach ($results['suppliers'] as $k => $v) {
            $results['suppliers'][$k]->name = $this->Suppliers->decode($v->name);
        }

        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Supplierdues->validate()) {
            $this->db->trans_start();
            $entity = $this->Supplierdues->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Supplierdues->save($entity, $entity['id']);
            } else {
                $results['status'] = $this->Supplierdues->save($entity);
            }
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity            = $this->Supplierdues->entity($this->put());
            $results['status'] = $this->Supplierdues->save($entity, $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $results['status'] = $this->Supplierdues->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Supplierdues->delete($id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Supplierdues->save([$this->Supplierdues->deletedAt() => false], $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Supplierdues->delete($id, true);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
