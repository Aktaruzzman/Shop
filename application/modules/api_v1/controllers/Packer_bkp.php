<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Packer extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Saledetails');
            $this->load->model('Users');
            $this->load->model('Dispatchers');
        }
    }
    public function index_get($packer_id = NULL)
    {
        $results['list'] = $this->Dispatchers->get_by(['status' => 'await', 'event' => 'packer']);
        foreach ($results['list'] as $index => $obj) {
            $order = $this->Sales->get($obj->sale_id, TRUE, TRUE, ['id', 'invoice', 'service', 'grand_total', 'cust_meta']);
            $order->cust_meta = $this->Sales->decode($order->cust_meta);
            $order->details = $this->Saledetails->get(['sale_id' => $order->id], FALSE, FALSE, ['sale_id', 'name', 'unit_name', 'course_name', 'category_name', 'sides', 'qty', 'new_qty', 'is_sent', 'item_id', 'option_id']);
            foreach ($order->details as $k => $v) {
                $order->details[$k]->name = $this->Sales->decode($v->name);
                $order->details[$k]->unit_name = $this->Sales->decode($v->unit_name);
                $order->details[$k]->category_name = $this->Sales->decode($v->category_name);
                $order->details[$k]->course_name = $this->Sales->decode($v->course_name);
                $order->details[$k]->sides = $this->Sales->decode($v->sides);
            }
            $results['list'][$index]->order = $order;
        }
        $results['counter'] = $this->count_items($results['list']);
        return $this->response($results);
    }

    public function index_put($sale_id = NULL)
    {
        if (!empty($sale_id)) {
            $this->db->trans_start();
            $this->Dispatchers->save_by(['status' => 'done'], ['sale_id' => $sale_id]);
            $this->Saledetails->save_by(['new_qty' => 0, 'is_sent' => 0], ['sale_id' => $sale_id]);
            $this->Sales->save(['stage' => 3, 'status' => 'confirmed'], $sale_id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    private function count_items($list)
    {
        $items = [];
        foreach ($list as $k => $v) {
            $items = array_merge($items, $v->order->details);
        }
        return $items;
    }
}
