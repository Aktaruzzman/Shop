<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Dispatcher extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Dispatchers');
            $this->load->model('Tablebookings');
            $this->load->model('Customers');
        }
    }
    public function index_get($hub = NULL, $event = NULL, $document = NULL, $sent_type = NULL)
    {
        if ($hub && $event && in_array($event, ['driver', 'packer', 'printer', 'online', 'booking', 'p', 'o', 'b', 'po', 'pb', 'ob', 'pob'])) {
            $this->db->trans_start();
            $conditions = ['hub_id' => $hub, 'event' => $event, 'status' => 'await'];
            if ($document) $conditions['document'] = $document;
            if ($sent_type) $conditions['sent_type'] = $sent_type;
            if ($event === 'p' || $event === 'po' || $event === 'pb' || $event === 'pob' || $event === 'printer') {
                $conditions['event'] = "printer";
                $results['entity'] = $this->Dispatchers->get_by($conditions, TRUE);
                if (!empty($results['entity'])) {
                    $results['entity']->meta_data = $this->Dispatchers->decode($results['entity']->meta_data);
                    return $this->response($results);
                }
            }
            if (empty($results['entity']) && ($event === 'po' || $event === 'o' || $event === 'ob' || $event === 'pob' || $event === 'online')) {
                $conditions['event'] = "online";
                $results['entity'] = $this->Dispatchers->get_by($conditions, TRUE);
                if (!empty($results['entity'])) {
                    $results['entity']->meta_data = $this->Dispatchers->decode($results['entity']->meta_data);
                    $results['entity']->sale = $this->__online_sale($results['entity']->sale_id);
                    return $this->response($results);
                }
            }
            if (empty($results['entity']) && ($event === 'pob' || $event === 'pb' || $event === 'ob' || $event === 'b' || $event === 'booking')) {
                $conditions['event'] = "booking";
                $results['entity'] = $this->Dispatchers->get_by($conditions, TRUE);
                if (!empty($results['entity'])) {
                    $results['entity']->booking = $this->Tablebookings->get($results['entity']->booking_id, true);
                    $results['entity']->booking->customer = $this->Customers->get($results['entity']->booking->cust_id, true, true, ['id', 'name', 'phone', 'email']);
                    $results['entity']->booking->customer->name = $this->Customers->decode($results['entity']->booking->customer->name);
                    return $this->response($results);
                }
            }
            $this->db->trans_complete();
            return $this->response(['status' => $this->db->trans_status()]);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }



    private function __online_sale($id)
    {
        $this->load->model('Sales');
        $this->load->model('Saledetails');
        $order = $this->Sales->get($id, true, true);
        $order->cust_meta = $this->Sales->decode($order->cust_meta);
        if (!empty($order->promo_meta)) $this->Sales->decode($order->promo_meta);
        $sale_details = $this->Saledetails->get_by(['sale_id' => $id]);
        foreach ($sale_details as $index => $datum) {
            $datum->name = $this->Saledetails->decode($datum->name);
            $datum->category_name = $this->Saledetails->decode($datum->category_name);
            $datum->item_name = $this->Saledetails->decode($datum->item_name);
            $datum->option_name = $this->Saledetails->decode($datum->option_name);
            $datum->course_name = $this->Saledetails->decode($datum->course_name);
            $datum->unit_name = $this->Saledetails->decode($datum->unit_name);
            $datum->tax_rule = $this->Saledetails->decode($datum->tax_rule);
            $datum->discount_rule = $this->Saledetails->decode($datum->discount_rule);
            $datum->sides = !empty($datum->sides) ? $this->Saledetails->decode($datum->sides) : [];
            $sale_details[$index] = $datum;
        }
        $order->details = $sale_details;
        return $order;
    }

    public function index_post()
    {
        if ($this->Dispatchers->validate()) {
            $this->db->trans_start();
            $entity = $this->Dispatchers->entity($this->post());
            if (!empty($entity['meta_data'])) $entity['meta_data'] = $this->Dispatchers->encode($entity['meta_data']);
            if (!empty($entity['id'])) $results['status'] = $this->Dispatchers->save($entity, $entity['id']);
            else $results['status'] = $this->Dispatchers->save($entity);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('event')) : sprintf(lang('saved_failed_msg'), lang('event'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Dispatchers->entity($this->put());
            $results['status'] = $this->Dispatchers->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('event')) : sprintf(lang('update_failed_msg'), lang('event'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Dispatchers->entity($this->patch());
            $results['status'] = $this->Dispatchers->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('event')) : sprintf(lang('update_failed_msg'), lang('event'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Dispatchers->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('event')) : sprintf(lang('delete_failed_msg'), lang('event'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Dispatchers->save([$this->Dispatchers->deletedAt() => FALSE], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('event')) : sprintf(lang('update_failed_msg'), lang('event'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Dispatchers->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('event')) : sprintf(lang('delete_failed_msg'), lang('event'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
