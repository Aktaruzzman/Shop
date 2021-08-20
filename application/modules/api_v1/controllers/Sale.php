<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Sale extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Users');
            $this->load->model('Sales');
            $this->load->model('Saledetails');
            $this->load->model('Tableplans');
            $this->load->model('Openings');
            $this->load->model('Dispatchers');
            $this->load->model('Cfgservices');
            
        }
    }

    public function index_get($id = null, $service = null, $stage = null)
    {
        if (!empty($id)) {
            $order = $this->Sales->get($id, true, true);
            $order->user =  $this->Sales->decode($this->Users->get($order->updated_by, true)->name);
            $order->cust_meta = $this->Sales->decode($order->cust_meta);
            if (!empty($order->promo_meta)) $order->promo_meta = $this->Sales->decode($order->promo_meta);
            $sale_details = $this->Saledetails->get_by(['sale_id' => $id]);
            foreach ($sale_details as $index => $datum) {
                $datum->name = $this->Saledetails->decode($datum->name);
                $datum->category_name = $this->Saledetails->decode($datum->category_name);
                $datum->base_name = $this->Saledetails->decode($datum->base_name);
                $datum->item_name = $this->Saledetails->decode($datum->item_name);
                $datum->option_name = $this->Saledetails->decode($datum->option_name);
                $datum->course_name = $this->Saledetails->decode($datum->course_name);
                $datum->unit_name = $this->Saledetails->decode($datum->unit_name);
                $datum->tax_rule = $this->Saledetails->decode($datum->tax_rule);
                $datum->discount_rule = $this->Saledetails->decode($datum->discount_rule);
                $datum->sides = !empty($datum->sides) ? $this->Saledetails->decode($datum->sides) : [];
                $sale_details[$index] = $datum;
            }
            $results = $this->Sales->initialize($order);
            $results['order'] = $order;
            $results['details'] = $sale_details;
        } else {
            $headers = getallheaders();
            $results['tillaccount'] = $this->Sales->initial_till_cash($headers);
            $results['list'] = $this->Sales->list_ongoings($service, $stage, $headers);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->cust_meta = json_decode($obj->cust_meta);
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
                $results['list'][$index]->user =  $this->Sales->decode($this->Users->get($obj->updated_by, true)->name);
            }
            $results['services'] = $this->Cfgservices->get_by(['value!=' => 'caller', 'status' => 'active'], FALSE);
            foreach ($results['services'] as $index => $service) {
                $results['services'][$index]->name = $this->Cfgservices->decode($service->name);
            }
        }
        $openingTime = $this->Openings->get(['day' => date('D')], true);
        if (($openingTime->temp_closed && strtotime($openingTime->closed_till) > time()) || !$openingTime->opened) {
            $results['webstore_temp_closed'] = true;
        }
        $results['status'] = !empty($results) ? true : false;
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Sales->entity($this->post());
        if (!empty($entity['id'])) {
            if (!empty($entity['cust_meta'])) $entity['cust_meta'] = $this->Sales->encode($entity['cust_meta']);
            $results['status'] = $id = $this->Sales->save($entity, $entity['id']);
            if (!empty($results['status']))  add_activity('Sale', 'A sale updated');
        }
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status() && $results['status'];
        $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('order')) : sprintf(lang('saved_failed_msg'), lang('order'));
        return $this->response($results);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Sales->post_entity($this->put());
            if (!empty($entity['sale']) || $entity['sale_details']) {
                $sale_entity = $entity['sale'];
                $sale_details = $entity['sale_details'];
                foreach ($sale_details as $datum) {
                    $datum['name'] = $this->Saledetails->encode($datum['name']);
                    $datum['category_name'] = $this->Saledetails->encode($datum['category_name']);
                    $datum['item_name'] = $this->Saledetails->encode($datum['item_name']);
                    $datum['option_name'] = $this->Saledetails->encode($datum['option_name']);
                    $datum['course_name'] = $this->Saledetails->encode($datum['course_name']);
                    $datum['unit_name'] = $this->Saledetails->encode($datum['unit_name']);
                    $datum['tax_rule'] = $this->Saledetails->encode($datum['tax_rule']);
                    $datum['discount_rule'] = $this->Saledetails->encode($datum['discount_rule']);
                    $datum['sides'] = $this->Saledetails->encode($datum['sides']);
                    $datum = $this->Saledetails->entity($datum);
                    $this->Saledetails->save($datum);
                }
                if (!empty($sale_entity)) {
                    if (!empty($sale_entity['cust_meta'])) $sale_entity['cust_meta'] = $this->Sales->encode($sale_entity['cust_meta']);
                    $this->Sales->save($sale_entity, $id);
                    add_activity('Sale', 'A sale released to admin');
                }
            } else {
                if (!empty($entity['cust_meta'])) $entity['cust_meta'] = $this->Sales->encode($entity['cust_meta']);
                $this->Sales->save($entity, $id);
                add_activity('Sale', 'A sale updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('order')) : sprintf(lang('update_failed_msg'), lang('order'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->patch();
            if (!empty($entity['cust_meta'])) $entity['cust_meta'] = $this->Sales->encode($entity['cust_meta']);
            $results['status'] = $this->Sales->save($entity, $id);
            if (!empty($results['status'])) add_activity('Sale', 'A sale updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('order')) : sprintf(lang('update_failed_msg'), lang('order'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Sales->delete($id);
            if (!empty($results['status'])) add_activity('Sale', 'A sale deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('order')) : sprintf(lang('delete_failed_msg'), lang('order'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Sales->save([$this->Sales->deletedAt() => false], $id);
            if (!empty($results['status'])) add_activity('Sale', 'A sale restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('order')) : sprintf(lang('update_failed_msg'), lang('order'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null, $service = null)
    {
        if (!empty($id) && (config_item('lock_permanent_delete') === 'no' || !empty($service))) {
            $this->db->trans_start();
            if ($service === 'table') {
                $obj = $this->Sales->get($id, true);
                if (!empty($obj)) {
                    $cust_meta = $this->Sales->decode($obj->cust_meta);
                    $this->Tableplans->save(['status' => 'inactive'], $cust_meta->table_id);
                }
            }
            $results['status'] = $this->Sales->delete($id, true);
            if (!empty($results['status'])) add_activity('Sale', 'A sale deleted permanently');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('order')) : sprintf(lang('delete_failed_msg'), lang('order'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request']);
    }
    public function send_email($sale_id)
    {
        return true;
    }
    public function send_sms($sale_id)
    {
        return true;
    }
    public function confirm_post()
    {
        $headers = getallheaders();
        $sale_id = $this->post('sale_id');
        $status = $this->post('status');
        $this->db->trans_start();
        $this->Dispatchers->save_by(['status' => 'done'], ['sale_id' => $sale_id, 'event' => 'online']);
        $this->Sales->save(['status' => $status, 'source' => 'pos', 'device_code' => $headers['device_code'], 'created_by' => $headers['user_id'], 'updated_by' => $headers['user_id']], $sale_id);
        $status === 'rejected' ? $this->__reject($sale_id) : $this->__accept($sale_id);
        $this->db->trans_complete();
        return $this->response(['status' => $this->db->trans_status()]);
    }
    private function __accept($sale_id)
    {
        //process confirmed email and sms to customer
    }
    private function __reject($sale_id)
    {
        //process reject email and sms to customer
    }
}
