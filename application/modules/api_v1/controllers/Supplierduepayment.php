<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Supplierduepayment extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Supplierduepayments');
            $this->load->model('Suppliers');
            $this->load->model('Storesupplieragents');
            $this->load->model('Supplieragents');
        }
    }
    public function index_get($supplieragent_id = null, $from = null, $to = null, $hub_id = null)
    {
        if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
        if (empty($to)) $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
        $results['list']  = $this->Supplierduepayments->get_details($supplieragent_id, $from, $to, $hub_id);
        $results['total_payment'] = 0;
        $results['total_cash_in_till'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['total_payment'] += $obj->amount;
            if ("Cash" === $obj->payment && "till" === $obj->source) {
                $results['total_cash_in_till'] += $obj->amount;
            }
            $supplieragent = $this->Supplieragents->get($obj->supplieragent_id, true, true, ['id', 'name', 'phone', 'email']);
            $results['list'][$index]->name  = $this->Suppliers->decode($supplieragent->name);
            $results['list'][$index]->phone = $supplieragent->phone;
        }
        $results['suppliers'] = $this->Supplieragents->get_supplieragents($hub_id);
        foreach ($results['suppliers'] as $k => $v) {
            $results['suppliers'][$k]->name = $this->Supplieragents->decode($v->name);
        }

        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Supplierduepayments->validate()) {
            $this->db->trans_start();
            $entity = $this->Supplierduepayments->entity($this->post());
            $entity['closed_on'] = closed_on();
            if (empty($entity['source']))
                if ("Cash" !== $entity['payment']) $entity['source'] = $entity['payment'];

            $supplieragent = $this->Supplieragents->get($entity['supplieragent_id'], true, true);
            $results['company_total_due'] = $supplieragent->due;
            $this->Supplieragents->save(['due' => doubleval($supplieragent->due) - doubleval($entity['amount'])], $entity['supplieragent_id']);

            $store_supplieragent = $this->Storesupplieragents->get(['supplieragent_id' => $entity['supplieragent_id']], true, true);
            $results['total_due'] = doubleval($store_supplieragent->due);
            $this->Storesupplieragents->save(['due' => doubleval($store_supplieragent->due) - doubleval($entity['amount'])], $store_supplieragent->id);
            $this->Supplierduepayments->save($entity);

            $results['payment']        = $entity;
            $results['supplier']       = $this->Supplieragents->get($entity['supplieragent_id'], true);
            $results['supplier']->name = $this->Suppliers->decode($results['supplier']->name);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }

    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Supplierduepayments->entity($this->put());
            $results['status'] = $this->Supplierduepayments->save($entity, $id);
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
            $this->db->trans_start();
            $results['status'] = $this->Supplierduepayments->save($this->patch(), $id);
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
            $results['status'] = $this->Supplierduepayments->delete($id);
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
            $results['status'] = $this->Supplierduepayments->save([$this->Supplierduepayments->deletedAt() => false], $id);
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
            $results['status'] = $this->Supplierduepayments->delete($id, true);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
