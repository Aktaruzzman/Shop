<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Collection extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Cfgservices');
            $this->load->model('Tableplans');
        }
    }
    public function index_get($id = NULL)
    {
        if (!empty($id)) $results['entity'] = $this->Sales->get($id, TRUE, TRUE, ['id', 'service', 'cust_id', 'cust_phone', 'cust_area', 'delivery_time', 'cust_meta']);
        if (!empty($results['entity'])) $results['entity']->cust_meta = $this->Sales->decode($results['entity']->cust_meta);
        $results['collection'] = $obj = $this->Cfgservices->get_by(['value' => 'collection'], TRUE, TRUE, NULL, NULL, NULL, ['value', 'delay', 'charge']);
        $results['date_slots'] = date_slot((int) config_item('order_offest_day'), (int) config_item('order_max_day'));
        $results['selected_date'] = array_values($results['date_slots'])[0];
        $opening = $this->Openings->get(['day' => date('D')], true, false);
        $from_now = ((int) config_item('order_max_day') > 0 ? false : true);
        if (!empty($results['entity'])) $from_now = strtotime($results['entity']->cust_meta->delivery_date) <= strtotime(date('Y-m-d'));
        $results['time_slots'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . $opening->start)), date('h:i A', strtotime(date('Y-m-d ') . $opening->end)), $obj->delay, (int) config_item('slot_interval_time'), $from_now);
        array_unshift($results['time_slots'], 'ASAP');
        $results['selected_time'] = array_values($results['time_slots'])[0];
        $results['tillaccount'] = $this->Sales->initial_till_cash(getallheaders());
        return $this->response($results);
    }
    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Customers->post_entity($this->post());
        $entity['name'] = ['en' => $entity['name_en'], 'bn' => $entity['name_bn']];
        $delivery_time_arr = delivery_time('collection', DateTime::createFromFormat('d/m/Y', $entity['delivery_date'])->format('Y-m-d'), $entity['delivery_time']);
        $entity['delivery_date'] = $delivery_time_arr['date'];
        $entity['delivery_time'] = $delivery_time_arr['time'];
        $timestamps = $delivery_time_arr['timestamps'];

        $customer = !empty($entity['phone']) ? $this->Customers->get_by(['phone' => $entity['phone']], TRUE, TRUE) : NULL;
        if (!empty($customer))
            $entity['cust_id'] = $customer->id;
        else {
            $customer_entity = $this->Customers->entity($entity);
            $customer_entity['name'] = $this->Customers->encode($customer_entity['name']);
            if (!empty($entity['phone']))  $entity['cust_id'] = $this->Customers->save($customer_entity);
            else $entity['cust_id'] = NULL;
        }
        $sale_entity = ['service' => $entity['service'], 'delivery_time' => $timestamps, 'lang' => getallheaders()['lang'], 'cust_meta' => $this->Sales->encode($entity)];

        if (!empty($entity['cust_id'])) {
            $sale_entity['cust_id'] = $entity['cust_id'];
            $results = !empty($entity['sale_id']) ? $this->Sales->save($sale_entity, $entity['sale_id']) : $this->Sales->create($sale_entity);
        } else  $results = !empty($entity['sale_id']) ? $this->Sales->save($sale_entity, $entity['sale_id']) : $this->Sales->create($sale_entity);
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status();
        return $this->response($results);
    }
    public function index_put($sale_id = NULL)
    {
        if (!empty($sale_id)) {
            $this->db->trans_start();
            $entity = $this->Customers->post_entity($this->put());

            $delivery_time_arr = delivery_time('collection', $entity['delivery_date'], $entity['delivery_time']);
            $entity['delivery_date'] = $delivery_time_arr['date'];
            $entity['delivery_time'] = $delivery_time_arr['time'];
            $timestamps = $delivery_time_arr['timestamps'];

            $entity['name'] = ['en' => $entity['name_en'], 'bn' => $entity['name_bn']];
            if (empty($entity['cust_id']) && !empty($entity['phone'])) {
                $customer_entity = $this->Customers->entity($entity);
                $customer_entity['name'] = $this->Customers->encode($customer_entity['name']);
                $entity['cust_id'] = $this->Customers->save($customer_entity);
            }
            $cust_meta = $this->Sales->decode($this->Sales->get($sale_id, TRUE)->cust_meta);
            if (!empty($entity['cust_id'])) $cust_meta->cust_id = $entity['cust_id'];
            if (!empty($entity['convert'])) {
                $converter = explode('-', $entity['convert']);
                if ($converter[0] == 'table')  $this->Tableplans->save(['status' => 'inactive'], $converter[1]);
                $sale_entity = ['service' => $entity['service'], 'delivery_time' =>  $timestamps, 'cust_meta' => $this->Sales->encode($entity), 'cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'cust_phone' => !empty($entity['phone']) ? $entity['phone'] : null];
                $this->Sales->save($sale_entity, $sale_id);
                $order = $this->Sales->get($sale_id, TRUE);
                $order->cust_meta = $this->Sales->decode($order->cust_meta);
                $results = $this->Sales->initialize($order);
                $results['order'] = $order;
            } else {
                $cust_meta->phone = !empty($entity['phone']) ? $entity['phone'] : null;
                $cust_meta->delivery_time = $entity['delivery_time'];
                $cust_meta->delivery_date = $entity['delivery_date'];
                $sale_entity = ['service' => $entity['service'], 'delivery_time' =>  $timestamps, 'cust_meta' => $this->Sales->encode($cust_meta), 'cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'cust_phone' => !empty($entity['phone']) ? $entity['phone'] : null];
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
