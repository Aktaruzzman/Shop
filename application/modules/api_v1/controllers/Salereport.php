<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Salereport extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('Salereports');
        $this->load->model('Sales');
        $this->load->model('Saledetails');
        $this->load->model('Salediscountdetails');
        $this->load->model('Salepaymentdetails');
        $this->load->model('Users');
        $this->load->model('Userhubs');
        $this->load->model('Geoareas');
        $this->load->model('Cfgservices');
    }

    public function index_get($id = null, $from = null, $to = null, $search = null, $limit = null, $offset = null, $hub_id = null, $deleted = false)
    {
        if (!empty($id)) {
            $data = $this->get_entity($id);
            $data['show_details'] = false;
            return $this->response($data);
        } else {
            if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
            if (empty($to))  $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
            $data['show_details'] = !empty($search) ? false : true;
            $data['rows'] = $this->Salereports->get_sales($from, $to, $search, ['invoice'], $limit, $offset, $hub_id, $deleted);
            $data['total'] = ['previous_cash_in_till' => 0, 'discount' => 0, 'vat' => 0, 'service' => 0, 'delivery' => 0, 'admin' => 0, 'sale' => 0, 'sale_by_payment' => 0, 'cost' => 0, 'payment_fee' => 0, 'payment' => 0, 'Cash' => 0, 'Card' => 0, 'Bikash' => 0, 'Nagad' => 0, 'Rocket' => 0, 'Cheque' => 0, 'Voucher' => 0, 'Due' => 0, 'change' => 0, 'tips' => 0, 'cash_tips' => 0, 'eft_tips' => 0, 'profit' => 0, 'due_sale_cash_in' => 0, 'purchase_return_cash_in' => 0, 'other_income_cash_in' => 0, 'voucher_sale_cash_in' => 0, 'cash_in' => 0, 'purchase_cash_out' => 0, 'due_purchase_cash_out' => 0, 'sale_return_cash_out' => 0, 'other_expense_cash_out' => 0, 'employee_salary_cash_out' => 0, 'cash_out' => 0, 'cash_in_till' => 0];
            $data['tips'] = ['Cash' => 0, 'Card' => 0, 'Bikash' => 0, 'Rocket' => 0, 'Nagad' => 0, 'Cheque' => 0, 'Voucher' => 0, 'Due' => 0];
            $data['summary'] = [
                'table' => ['served' => 0, 'guest' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0],
                'express' => ['served' => 0, 'guest' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0],
                'waiting' => ['served' => 0, 'guest' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0],
                'delivery' => ['served' => 0, 'guest' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0],
                'collection' => ['served' => 0, 'guest' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0],
                'bar' => ['served' => 0, 'guest' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0],
                'total' => ['served' => 0, 'sale' => 0, 'payment' => 0, 'payment_fee' => 0, 'tips' => 0, 'due' => 0, 'change' => 0, 'charge' => 0, 'discount' => 0]
            ];
            foreach ($data['rows'] as $index => $row) {
                $row->updator = $this->Salereports->decode($this->Users->get($row->updated_by, true)->name);
                $row->creator = $this->Salereports->decode($this->Users->get($row->created_by, true)->name);
                $row->cust_meta = $row->cust_meta = $this->Salereports->decode($row->cust_meta);
                $data['rows'][$index] = $row;
                $data = $this->sales_total($data, $row);
                $data = $this->sales_summary($data, $row);
            }
            $data = $this->format_summary($data);
            $data = $this->format_total($data, $from, $to, $hub_id);
            $data['hubs'] = $this->Userhubs->get(['status' => 'active'], null, null, ['id', 'name', 'area_id', 'house_en', 'house_bn']);
            foreach ($data['hubs'] as $k => $v) {
                $data['hubs'][$k]->name = $this->Userhubs->decode($v->name);
                $data['hubs'][$k]->area = $this->Geoareas->formatted($v->area_id)->formatted;
                if ($hub_id == $v->id) $data['hub'] = $data['hubs'][$k];
            }
            $data['services'] = $this->Cfgservices->get_by(['value!=' => 'caller', 'status' => 'active'], false);
            foreach ($data['services'] as $index => $obj) {
                $data['services'][$index]->name = $this->Cfgservices->decode($obj->name);
            }
            return $this->response($data);
        }
    }

    private function get_entity($id)
    {
        $data['sale']            = $this->Sales->get($id, true, true);
        $data['sale']->cust_meta = $this->Sales->decode($data['sale']->cust_meta);
        if ("delivery" === $data['sale']->service) {
            $data['sale']->cust_meta->area  = ['en' => $data['sale']->cust_meta->area->en, 'bn' => $data['sale']->cust_meta->area->bn];
            $data['sale']->cust_meta->house = ['en' => $data['sale']->cust_meta->house->en, 'bn' => $data['sale']->cust_meta->house->bn];
        }
        $data['sale']->updator = $this->Sales->decode($this->Users->get($data['sale']->updated_by, true)->name);
        $data['sale']->creator = $this->Sales->decode($this->Users->get($data['sale']->created_by, true)->name);
        $calTotal = ($data['sale']->subtotal + $data['sale']->tax + $data['sale']->service_charge + $data['sale']->delivery_charge + $data['sale']->payment_fee) - $data['sale']->discount;
        $calTotal += ($data['sale']->admin_fee > 0 && config_item('admin_fee_per') === "order_excluded") ? $data['sale']->admin_fee : 0;
        $data['details'] = $this->Saledetails->get_by(['sale_id' => $id]);
        foreach ($data['details'] as $index => $obj) {
            $data['details'][$index]->sides = $this->Saledetails->decode($obj->sides);
            $data['details'][$index]->name = $this->Saledetails->decode($obj->name);
            $data['details'][$index]->category_name = $this->Saledetails->decode($obj->category_name);
            $data['details'][$index]->item_name = $this->Saledetails->decode($obj->item_name);
            $data['details'][$index]->option_name = $this->Saledetails->decode($obj->option_name);
            $data['details'][$index]->course_name = $this->Saledetails->decode($obj->course_name);
            $data['details'][$index]->unit_name = $this->Saledetails->decode($obj->unit_name);
            $data['details'][$index]->tax_rule = $this->Saledetails->decode($obj->tax_rule);
            $data['details'][$index]->discount_rule = $this->Saledetails->decode($obj->discount_rule);
        }
        $data['discount_details'] = $this->Salediscountdetails->get_by(['sale_id' => $id], true);
        $data['payment_details']  = $this->Salepaymentdetails->get_by(['sale_id' => $id], true);
        if (!empty($data['payment_details'])) {
            $data['payment_details']->details = $this->Salepaymentdetails->decode($data['payment_details']->details);
        }
        return $data;
    }

    private function sales_total($data, $row)
    {
        $data['total']['discount'] += $row->discount;
        $data['total']['vat'] += $row->tax;
        $data['total']['service'] += $row->service_charge;
        $data['total']['delivery'] += $row->delivery_charge;
        $data['total']['admin'] += $row->admin_fee;
        $data['total']['sale'] += $row->grand_total;
        $data['total']['cost'] += $row->cost_total;
        $data['total']['payment_fee'] += $row->payment_fee;
        $data['total']['Cash'] += $row->Cash;
        $data['total']['Card'] += $row->Card;
        $data['total']['Bikash'] += $row->Bikash;
        $data['total']['Nagad'] += $row->Nagad;
        $data['total']['Rocket'] += $row->Rocket;
        $data['total']['Cheque'] += $row->Cheque;
        $data['total']['Voucher'] += $row->Voucher;
        $data['total']['Due'] += $row->Due;
        $data['total']['change'] += $row->change;
        $data['total']['tips'] += $row->tips;
        $data = $this->sale_tips($data, $row);
        return $data;
    }

    private function sale_tips($data, $row)
    {
        if ($row->tips > 0) {
            if ($row->Cash >= $row->tips) {
                $data['tips']['Cash'] += $row->tips;
                $data['total']['cash_tips'] += $row->tips;
            } else {
                $data['total']['eft_tips'] += $row->tips;
                if ($row->Card >= $row->tips) {
                    $data['tips']['Card'] += $row->tips;
                } elseif ($row->Bikash >= $row->tips) {
                    $data['tips']['Bikash'] += $row->tips;
                } elseif ($row->Rocket >= $row->tips) {
                    $data['tips']['Rocket'] += $row->tips;
                } elseif ($row->Nagad >= $row->tips) {
                    $data['tips']['Nagad'] += $row->tips;
                } elseif ($row->Cheque >= $row->tips) {
                    $data['tips']['Cheque'] += $row->tips;
                } elseif ($row->Voucher >= $row->tips) {
                    $data['tips']['Voucher'] += $row->tips;
                } else {
                    $data['tips']['Due'] += $row->tips;
                }
            }
        }
        return $data;
    }
    private function sales_summary($data, $row)
    {
        if ('table' === $row->service) {
            $data['summary']['table']['served'] += 1;
            $data['summary']['table']['guest'] += $row->cust_meta->guest;
            $data['summary']['table']['sale'] += $row->grand_total;
            $data['summary']['table']['payment'] += ($row->Cash + $row->Card + $row->Bikash + $row->Nagad + $row->Rocket + $row->Cheque + $row->Voucher) - ($row->tips + $row->change);
            $data['summary']['table']['payment_fee'] += $row->payment_fee;
            $data['summary']['table']['tips'] += $row->tips;
            $data['summary']['table']['change'] += $row->change;
            $data['summary']['table']['charge'] += $row->service_charge;
            $data['summary']['table']['due'] += $row->Due;
            $data['summary']['table']['discount'] += $row->discount;
        }
        if ('express' === $row->service) {
            $data['summary']['express']['served'] += 1;
            $data['summary']['express']['sale'] += $row->grand_total;
            $data['summary']['express']['payment'] += ($row->Cash + $row->Card + $row->Bikash + $row->Nagad + $row->Rocket + $row->Cheque + $row->Voucher) - ($row->tips + $row->change);
            $data['summary']['express']['payment_fee'] += $row->payment_fee;
            $data['summary']['express']['tips'] += $row->tips;
            $data['summary']['express']['change'] += $row->change;
            $data['summary']['express']['charge'] += $row->service_charge;
            $data['summary']['express']['due'] += $row->Due;
            $data['summary']['express']['discount'] += $row->discount;
        }
        if ('waiting' === $row->service) {
            $data['summary']['waiting']['served'] += 1;
            $data['summary']['waiting']['sale'] += $row->grand_total;
            $data['summary']['waiting']['payment'] += ($row->Cash + $row->Card + $row->Bikash + $row->Nagad + $row->Rocket + $row->Cheque + $row->Voucher) - ($row->tips + $row->change);
            $data['summary']['waiting']['payment_fee'] += $row->payment_fee;
            $data['summary']['waiting']['tips'] += $row->tips;
            $data['summary']['waiting']['change'] += $row->change;
            $data['summary']['waiting']['charge'] += $row->service_charge;
            $data['summary']['waiting']['due'] += $row->Due;
            $data['summary']['waiting']['discount'] += $row->discount;
        }
        if ('delivery' === $row->service) {
            $data['summary']['delivery']['served'] += 1;
            $data['summary']['delivery']['sale'] += $row->grand_total;
            $data['summary']['delivery']['payment'] += ($row->Cash + $row->Card + $row->Bikash + $row->Nagad + $row->Rocket + $row->Cheque + $row->Voucher) - ($row->tips + $row->change);
            $data['summary']['delivery']['payment_fee'] += $row->payment_fee;
            $data['summary']['delivery']['tips'] += $row->tips;
            $data['summary']['delivery']['change'] += $row->change;
            $data['summary']['delivery']['charge'] += $row->service_charge + $row->delivery_charge;
            $data['summary']['delivery']['due'] += $row->Due;
            $data['summary']['delivery']['discount'] += $row->discount;
        }
        if ('collection' === $row->service) {
            $data['summary']['collection']['served'] += 1;
            $data['summary']['collection']['sale'] += $row->grand_total;
            $data['summary']['collection']['payment'] += ($row->Cash + $row->Card + $row->Bikash + $row->Nagad + $row->Rocket + $row->Cheque + $row->Voucher) - ($row->tips + $row->change);
            $data['summary']['collection']['payment_fee'] += $row->payment_fee;
            $data['summary']['collection']['tips'] += $row->tips;
            $data['summary']['collection']['change'] += $row->change;
            $data['summary']['collection']['charge'] += $row->service_charge;
            $data['summary']['collection']['due'] += $row->Due;
            $data['summary']['collection']['discount'] += $row->discount;
        }
        if ('bar' === $row->service) {
            $data['summary']['bar']['served'] += 1;
            $data['summary']['bar']['sale'] += $row->grand_total;
            $data['summary']['bar']['payment'] += ($row->Cash + $row->Card + $row->Bikash + $row->Nagad + $row->Rocket + $row->Cheque + $row->Voucher) - ($row->tips + $row->change);
            $data['summary']['bar']['payment_fee'] += $row->payment_fee;
            $data['summary']['bar']['tips'] += $row->tips;
            $data['summary']['bar']['change'] += $row->change;
            $data['summary']['bar']['charge'] += $row->service_charge;
            $data['summary']['bar']['due'] += $row->Due;
            $data['summary']['bar']['discount'] += $row->discount;
        }
        return $data;
    }

    private function format_summary($data)
    {
        $data['summary']['total']['served']      = $data['summary']['express']['served'] + $data['summary']['table']['served'] + $data['summary']['waiting']['served'] + $data['summary']['delivery']['served'] + $data['summary']['collection']['served'] + $data['summary']['bar']['served'];
        $data['summary']['total']['sale']        = $data['summary']['express']['sale'] + $data['summary']['table']['sale'] + $data['summary']['waiting']['sale'] + $data['summary']['delivery']['sale'] + $data['summary']['collection']['sale'] + $data['summary']['bar']['sale'];
        $data['summary']['total']['payment']     = $data['summary']['express']['payment'] + $data['summary']['table']['payment'] + $data['summary']['waiting']['payment'] + $data['summary']['delivery']['payment'] + $data['summary']['collection']['payment'] + $data['summary']['bar']['payment'];
        $data['summary']['total']['tips']        = $data['summary']['express']['tips'] + $data['summary']['table']['tips'] + $data['summary']['waiting']['tips'] + $data['summary']['delivery']['tips'] + $data['summary']['collection']['tips'] + $data['summary']['bar']['tips'];
        $data['summary']['total']['change']      = $data['summary']['express']['change'] + $data['summary']['table']['change'] + $data['summary']['waiting']['change'] + $data['summary']['delivery']['change'] + $data['summary']['collection']['change'] + $data['summary']['bar']['change'];
        $data['summary']['total']['charge']      = $data['summary']['express']['charge'] + $data['summary']['table']['charge'] + $data['summary']['waiting']['charge'] + $data['summary']['delivery']['charge'] + $data['summary']['collection']['charge'] + $data['summary']['bar']['charge'];
        $data['summary']['total']['due']         = $data['summary']['express']['due'] + $data['summary']['table']['due'] + $data['summary']['waiting']['due'] + $data['summary']['delivery']['due'] + $data['summary']['collection']['due'] + $data['summary']['bar']['due'];
        $data['summary']['total']['discount']    = $data['summary']['express']['discount'] + $data['summary']['table']['discount'] + $data['summary']['waiting']['discount'] + $data['summary']['delivery']['discount'] + $data['summary']['collection']['discount'] + $data['summary']['bar']['discount'];
        $data['summary']['total']['payment_fee'] = $data['summary']['express']['payment_fee'] + $data['summary']['table']['payment_fee'] + $data['summary']['waiting']['payment_fee'] + $data['summary']['delivery']['payment_fee'] + $data['summary']['collection']['payment_fee'] + $data['summary']['bar']['payment_fee'];
        return $data;
    }
    private function format_total($data, $from, $to, $hub_id)
    {
        $previous_cash_in_till = $this->Salereports->initial_till_cash($from, $to, $hub_id);
        $data['total']['payment'] += $data['total']['Cash'] + $data['total']['Card'] + $data['total']['Bikash'] + $data['total']['Nagad'] + $data['total']['Rocket'] + $data['total']['Cheque'] + $data['total']['Voucher'];
        $data['total']['sale_by_payment'] = ($data['total']['payment'] + $data['total']['Due']) - ($data['total']['tips'] + $data['total']['change']);
        $data['total']['Cash'] -= ($data['tips']['Cash'] + $data['total']['change']);
        $data['total']['Card'] -= $data['tips']['Card'];
        $data['total']['Bikash'] -= $data['tips']['Bikash'];
        $data['total']['Nagad'] -= $data['tips']['Nagad'];
        $data['total']['Rocket'] -= $data['tips']['Rocket'];
        $data['total']['Cheque'] -= $data['tips']['Cheque'];
        $data['total']['Voucher'] -= $data['tips']['Voucher'];
        $data['total']['Due'] -= $data['tips']['Due'];
        $data['total']['sale_by_payment_plus_tips'] = $data['total']['sale_by_payment'] + $data['total']['tips'];
        $data['total']['profit']                    = $data['total']['sale'] + $data['total']['tips'] - $data['total']['cost'];
        $data['total']['due_sale_cash_in']          = $this->Salereports->due_sale_cash_in($from, $to, $hub_id);
        $data['total']['purchase_return_cash_in']   = $this->Salereports->purchase_return_cash_in($from, $to, $hub_id);
        $data['total']['voucher_sale_cash_in']      = $this->Salereports->voucher_sale_cash_in($from, $to, $hub_id);
        $data['total']['other_income_cash_in']      = $this->Salereports->other_income_cash_in($from, $to, $hub_id);
        $data['total']['purchase_cash_out']         = $this->Salereports->purchase_cash_out($from, $to, $hub_id);
        $data['total']['due_purchase_cash_out']     = $this->Salereports->due_purchase_cash_out($from, $to, $hub_id);
        $data['total']['sale_return_cash_out']      = $this->Salereports->sale_return_cash_out($from, $to, $hub_id);
        $data['total']['other_expense_cash_out']    = $this->Salereports->other_expense_cash_out($from, $to, $hub_id);
        $data['total']['employee_salary_cash_out']  = $this->Salereports->employee_salary_cash_out($from, $to, $hub_id);
        $data['total']['cash_in']                   = $data['total']['Cash'] + $data['total']['due_sale_cash_in'] + $data['total']['purchase_return_cash_in'] + $data['total']['voucher_sale_cash_in'] + $data['total']['other_income_cash_in'] + $data['total']['tips'];
        $data['total']['cash_out']                  = $data['total']['purchase_cash_out'] + $data['total']['due_purchase_cash_out'] + $data['total']['sale_return_cash_out'] + $data['total']['other_expense_cash_out'] + $data['total']['employee_salary_cash_out'];
        $data['total']['previous_cash_in_till']     = $previous_cash_in_till;
        $data['total']['cash_in_till']              = ($data['total']['previous_cash_in_till'] + $data['total']['cash_in']) - $data['total']['cash_out'];
        return $data;
    }
}
