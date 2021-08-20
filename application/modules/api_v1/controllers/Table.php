<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Table extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Tableplans');
            $this->load->model('Cfgservices');
        }
    }
    public function index_get($id = NULL)
    {
        if (!empty($id)) $results['entity'] = $this->Sales->get($id, TRUE, TRUE, ['id', 'service', 'cust_id', 'cust_phone', 'cust_area', 'delivery_time', 'cust_meta']);
        if (!empty($results['entity'])) $results['entity']->cust_meta = $this->Sales->decode($results['entity']->cust_meta);
        $results['table'] = $this->Cfgservices->get_by(['value' => 'table'], TRUE, TRUE, NULL, NULL, NULL, ['value', 'delay', 'charge']);
        $results['tillaccount'] = $this->Sales->initial_till_cash(getallheaders());
        return $this->response($results);
    }
    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Tableplans->post_entity($this->post());

        $delivery_time_arr = delivery_time('waiting', '', '');
        $entity['delivery_date'] = $delivery_time_arr['date'];
        $entity['delivery_time'] = $delivery_time_arr['time'];
        $timestamps = $delivery_time_arr['timestamps'];

        if (array_key_exists('cust_id', $entity) && empty($entity['cust_id'])) unset($entity['cust_id']);
        $table = $this->Tableplans->get_by(['name' => $entity['name']], TRUE, TRUE);
        if (!empty($table)) {
            if ($table->status === "active")
                return $this->response(['status' => FALSE, 'msg' => 'table_not_vacant']);
            else {
                $this->Tableplans->save(['status' => 'active'], $table->id);
                $entity['table_id'] = $table->id;
            }
        } else  $entity['table_id'] = $this->Tableplans->save(['name' => $entity['name'], 'status' => 'active']);
        $results = $this->Sales->create(['cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'service' => $entity['service'], 'delivery_time' => $timestamps, 'lang' => getallheaders()['lang'], 'cust_meta' => $this->Sales->encode($entity)]);
        $results['status'] = $this->db->trans_complete();
        $this->db->trans_status();
        return $this->response($results);
    }

    public function index_put($sale_id = NULL)
    {
        if (!empty($sale_id)) {
            $this->db->trans_start();
            $entity = $this->Tableplans->post_entity($this->put());
            $delivery_time_arr = delivery_time('waiting', '', '');
            $entity['delivery_date'] = $delivery_time_arr['date'];
            $entity['delivery_time'] = $delivery_time_arr['time'];
            $timestamps = $delivery_time_arr['timestamps'];
            $cust_meta = $this->Sales->decode($this->Sales->get($sale_id, TRUE)->cust_meta);
            if ($cust_meta->name === $entity['name']) {
                $cust_meta->guest = $entity['guest'];
            } else {
                $table = $this->Tableplans->get_by(['name' => $entity['name']], TRUE, TRUE);
                if (!empty($table)) {
                    $entity['table_id'] = $table->id;
                    if ($table->status === "active") return $this->response(['status' => FALSE, 'msg' => 'table_not_vacant']);
                    else  $this->Tableplans->save(['status' => 'active'], $table->id);
                } else  $entity['table_id'] = $this->Tableplans->save(['name' => $entity['name'], 'status' => 'active']);
                if (!empty($cust_meta->table_id)) $this->Tableplans->save(['status' => 'inactive'], $cust_meta->table_id);
            }
            if (!empty($entity['convert'])) {
                $sale_entity = ['service' => $entity['service'], 'delivery_time' => $timestamps, 'cust_meta' => $this->Sales->encode($cust_meta->name == $entity['name'] ? $cust_meta :  $entity), 'cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'cust_phone' => !empty($entity['phone']) ? $entity['phone'] : null];
                $this->Sales->save($sale_entity, $sale_id);
                $order = $this->Sales->get($sale_id, TRUE);
                $order->cust_meta = $this->Sales->decode($order->cust_meta);
                $results = $this->Sales->initialize($order);
                $results['order'] = $order;
            } else {
                $cust_meta->delivery_time = $entity['delivery_time'];
                $cust_meta->delivery_date = $entity['delivery_date'];
                $cust_meta->phone = !empty($entity['phone']) ? $entity['phone'] : null;
                $sale_entity = ['service' => $entity['service'], 'delivery_time' => $timestamps, 'cust_meta' => $this->Sales->encode($cust_meta->name == $entity['name'] ? $cust_meta :  $entity), 'cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'cust_phone' => !empty($entity['phone']) ? $entity['phone'] : null];
                $this->Sales->save($sale_entity, $sale_id);
                $order = $this->Sales->get($sale_id, TRUE);
                $order->cust_meta = $this->Sales->decode($order->cust_meta);
                $results['order'] = $order;
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
}
