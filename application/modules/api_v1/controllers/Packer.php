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
            $this->load->model('Packers');
            $this->load->model('Sales');
            $this->load->model('Saledetails');
            $this->load->model('Users');
            $this->load->model('Dispatchers');
            $this->load->model('Prodbases');
        }
    }
    public function index_get($packer_id = NULL)
    {
        $results['list'] = $this->Dispatchers->get_by(['status' => 'await', 'event' => 'packer', 'listener' => $packer_id]);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->base_name = $this->Packers->decode($this->Prodbases->get(['value' => $obj->document], TRUE)->name);
            $order = $this->Sales->get($obj->sale_id, TRUE, TRUE, ['id', 'invoice', 'service', 'grand_total', 'cust_meta']);
            $order->disperse_id = $obj->id;
            $order->cust_meta = $this->Sales->decode($order->cust_meta);
            $order->details = $this->Packers->decode($obj->meta_data);
            $results['list'][$index]->order = $order;
            unset($results['list'][$index]->meta_data);
        }
        $results['counter'] = $this->count_items($results['list']);
        return $this->response($results);
    }

    public function index_put($disperse_id = NULL)
    {
        if (!empty($disperse_id)) {
            $this->db->trans_start();
            $obj = $this->Dispatchers->get($disperse_id, TRUE);
            $this->Dispatchers->save_by(['status' => 'done'], ['id' => $obj->id]);
            $this->Saledetails->save_by(['new_qty' => 0, 'is_sent' => 0], ['sale_id' => $obj->sale_id, 'base_name' => $obj->document]);
            $jobs = $this->Dispatchers->count_rows(['sale_id' => $obj->sale_id, 'event' => 'packer', 'status' => 'await']);
            if ($jobs <= 0) $this->Sales->save(['stage' => 3, 'status' => 'confirmed'], $obj->sale_id);
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

    public function test_get()
    {
        return $this->response(['packers' => $this->Packers->get_packer()]);
    }
}
