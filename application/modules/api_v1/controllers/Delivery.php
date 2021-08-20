<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Delivery extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customers');
            $this->load->model('Customeraddresses');
            $this->load->model('Sales');
            $this->load->model('Cfgservices');
            $this->load->model('Tableplans');
        }
    }
    public function index_get($id = NULL)
    {
        if (!empty($id)) $results['entity'] = $this->Sales->get($id, TRUE, TRUE, ['id', 'service', 'cust_id', 'cust_phone', 'cust_area', 'delivery_time', 'cust_meta']);
        if (!empty($results['entity'])) $results['entity']->cust_meta = $this->Sales->decode($results['entity']->cust_meta);
        $results['delivery'] = $obj = $this->Cfgservices->get_by(['value' => 'delivery'], TRUE, TRUE, NULL, NULL, NULL, ['value', 'delay', 'charge']);
        $results['date_slots'] = date_slot((int) config_item('order_offest_day'), (int) config_item('order_max_day'));
        $results['selected_date'] = $results['date_slots'][0];
        $opening = $this->Openings->get(['day' => date('D')], true, false);
        $from_now = ((int) config_item('order_max_day') > 0 ? false : true);
        if (!empty($results['entity'])) $from_now = strtotime($results['entity']->cust_meta->delivery_date) <= strtotime(date('Y-m-d'));
        $results['time_slots'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . $opening->start)), date('h:i A', strtotime(date('Y-m-d ') . $opening->end)), $obj->delay, (int) config_item('slot_interval_time'), $from_now);
        array_unshift($results['time_slots'], 'ASAP');
        $results['selected_time'] = $results['time_slots'][0];
        $results['tillaccount'] = $this->Sales->initial_till_cash(getallheaders());
        return $this->response($results);
    }
    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Customers->post_entity($this->post());
        $entity['name'] = ['en' => $entity['name_en'], 'bn' => $entity['name_bn']];
        $delivery_time_arr = delivery_time('delivery', DateTime::createFromFormat('d/m/Y', $entity['delivery_date'])->format('Y-m-d'), $entity['delivery_time']);
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

        if (!empty($entity['cust_id'])) {
            $customer_address_entity = ['cust_id' => $entity['cust_id'], 'label_en' => 'Labelless', 'label_bn' => 'লেবেলহীন', 'house_en' => $entity['house_en'], 'house_bn' => $entity['house_bn'], 'area_id' => $entity['area_id']];
            $address_en = $this->Customeraddresses->get_by(['cust_id' => $entity['cust_id'], 'area_id' => $entity['area_id']], FALSE, FALSE, NULL, NULL, $entity['house_en'], ['house_en', 'house_bn']);
            $address_bn = $this->Customeraddresses->get_by(['cust_id' => $entity['cust_id'], 'area_id' => $entity['area_id']], FALSE, FALSE, NULL, NULL, $entity['house_bn'], ['house_en', 'house_bn']);
            if (empty($address_en) || empty($address_bn)) {
                $customer_address_entity['house'] = $this->Customeraddresses->encode(["en" => $customer_address_entity['house_en'], "bn" => $customer_address_entity['house_bn']]);
                $customer_address_entity['label'] = $this->Customers->encode(["en" => $customer_address_entity['label_en'], "bn" => $customer_address_entity['label_bn']]);
                $this->Customeraddresses->save($customer_address_entity);
            }
        }
        $entity['area'] = ['en' => $entity['area_en'], 'bn' => $entity['area_bn']];
        if (empty($entity['house_en'])) $entity['house_en'] = $entity['house_bn'];
        if (empty($entity['house_bn'])) $entity['house_bn'] = $entity['house_en'];
        $entity['house'] = ['en' => $entity['house_en'], 'bn' => $entity['house_bn']];

        $sale_entity = ['cust_area' => $entity['area_id'], 'service' => $entity['service'], 'delivery_time' => $timestamps, 'lang' => getallheaders()['lang'],  'cust_meta' => $this->Sales->encode($entity)];

        if (!empty($entity['cust_id'])) {
            $sale_entity['cust_id'] = $entity['cust_id'];
            $results = !empty($entity['sale_id']) ? $this->Sales->save($sale_entity, $entity['sale_id']) : $this->Sales->create($sale_entity);
        } else $results = !empty($entity['sale_id']) ? $this->Sales->save($sale_entity, $entity['sale_id']) : $this->Sales->create($sale_entity);

        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status();
        return $this->response($results);
    }

    public function index_put($sale_id = NULL)
    {
        if (!empty($sale_id)) {
            $this->db->trans_start();
            $entity = $this->Customers->post_entity($this->put());

            $delivery_time_arr = delivery_time('delivery', $entity['delivery_date'], $entity['delivery_time']);
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
                $sale_entity = ['service' => $entity['service'], 'delivery_time' => $timestamps, 'cust_meta' => $this->Sales->encode($entity), 'cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'cust_phone' => !empty($entity['phone']) ? $entity['phone'] : null];
                $this->Sales->save($sale_entity, $sale_id);
                $order = $this->Sales->get($sale_id, TRUE);
                $order->cust_meta = $this->Sales->decode($order->cust_meta);
                $results = $this->Sales->initialize($order);
                $results['order'] = $order;
            } else {
                $cust_meta->delivery_time = $entity['delivery_time'];
                $cust_meta->delivery_date = $entity['delivery_date'];
                $cust_meta->phone = !empty($entity['phone']) ? $entity['phone'] : null;
                $cust_meta->house_en = $entity['house_en'];
                $cust_meta->house_bn = $entity['house_bn'];
                $cust_meta->house = ['en' => $cust_meta->house_en, 'bn' => $cust_meta->house_bn];
                $cust_meta->area_en = $entity['area_en'];
                $cust_meta->area_bn = $entity['area_bn'];
                $cust_meta->area = ['en' => $cust_meta->area_en, 'bn' => $cust_meta->area_bn];
                $sale_entity = ['service' => $entity['service'], 'delivery_time' => $timestamps, 'cust_meta' => $this->Sales->encode($cust_meta), 'cust_id' => !empty($entity['cust_id']) ? $entity['cust_id'] : null, 'cust_phone' => !empty($entity['phone']) ? $entity['phone'] : null];
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
