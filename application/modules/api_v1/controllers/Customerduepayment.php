<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Customerduepayment extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customerduepayments');
            $this->load->model('Customers');
            $this->load->model('Storecustomers');
        }
    }

    public function index_get($cust_id = null, $from = null, $to = null, $hub_id = null)
    {
        if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
        if (empty($to))  $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
        $results['list'] = $this->Customerduepayments->get_details($cust_id, $from, $to, $hub_id);
        $results['total_payment'] = 0;
        $results['total_cash_in_till'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['total_payment'] += $obj->amount;
            if ("Cash" === $obj->payment && "till" === $obj->source) {
                $results['total_cash_in_till'] += $obj->amount;
            }
            $custoemr                       = $this->Customers->get($obj->cust_id, true, true, ['name', 'phone']);
            $results['list'][$index]->name  = $this->Customers->decode($custoemr->name);
            $results['list'][$index]->phone = $custoemr->phone;
        }
        $results['customers'] = $this->Customers->get(null, null, null, ['id', 'name', 'phone']);
        foreach ($results['customers'] as $k => $v) {
            $results['customers'][$k]->name = $this->Customers->decode($v->name);
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Customerduepayments->validate()) {
            $this->db->trans_start();
            $entity = $this->Customerduepayments->entity($this->post());
            $entity['closed_on'] = closed_on();
            $entity['hub_id'] = getallheaders()['hub_id'];
            if (empty($entity['source']))
                if ("Cash" !== $entity['payment'])  $entity['source'] = $entity['payment'];
            $customer = $this->Customers->get($entity['cust_id'], true);
            $results['total_due'] = doubleval($customer->due);
            $this->Customers->save(['due' => doubleval($customer->due) - doubleval($entity['amount'])], $entity['cust_id']);
            if (!empty($entity['id'])) $this->Customerduepayments->save($entity, $entity['id']);
            else $this->Customerduepayments->save($entity);

            if (config_item('store_type') === 'multiple') {
                $store_customer = $this->Storecustomers->get(['cust_id' => $entity['cust_id'], 'hub_id' => $entity['hub_id']], true, true);
                $results['total_due'] = doubleval($store_customer->due);
                if (!empty($store_customer)) $this->Storecustomers->save(['due' => doubleval($store_customer->due) - doubleval($entity['amount'])], $store_customer->id);
            }
            $results['payment'] = $entity;
            $results['customer'] = $this->Customers->get($entity['cust_id'], true);
            $results['customer']->name = $this->Customers->decode($results['customer']->name);

            if (config_item('store_type') === 'multiple') {
                $store_customer = $this->Storecustomers->get(['cust_id' => $entity['cust_id'], 'hub_id' => $entity['hub_id']], true, true);
                $results['customer']->company_due = $results['customer']->due;
                $results['customer']->due = $store_customer->due;
            }

            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('customer') . ' ' . lang('due')) : sprintf(lang('saved_failed_msg'), lang('customer') . ' ' . lang('due'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Customerduepayments->entity($this->put());
            $results['status'] = $this->Customerduepayments->save($entity, $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('due')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('due'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerduepayments->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('due')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('due'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerduepayments->delete($id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer') . ' ' . lang('due')) : sprintf(lang('delete_failed_msg'), lang('customer') . ' ' . lang('due'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerduepayments->save([$this->Customerduepayments->deletedAt() => false], $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('due')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('due'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Customerduepayments->delete($id, true);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer') . ' ' . lang('due')) : sprintf(lang('delete_failed_msg'), lang('customer') . ' ' . lang('due'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
