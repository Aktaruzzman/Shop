<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Purchasereport extends REST_Controller
{

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Purchases');
            $this->load->model('Purchasedetails');
        }

    }

    public function index_get($id = null, $from = null, $to = null, $hub_id = null)
    {
        $data['rows']  = $this->Purchases->get_rows($from, $to, getallheaders(), $hub_id);
        $data['total'] = ['discount' => 0, 'vat' => 0, 'purchase' => 0, 'payment' => 0, 'Cash' => 0, 'Card' => 0, 'Bikash' => 0, 'Nagad' => 0, 'Rocket' => 0, 'Cheque' => 0, 'Voucher' => 0, 'Due' => 0, 'change' => 0, 'cash_from_till' => 0];
        foreach ($data['rows'] as $index => $row) {
            $data['rows'][$index]->supplier = json_decode($this->db->where('id', $row->supplier_id)->get('suppliers')->row()->name);
            $data['total']['discount'] += $row->discount;
            $data['total']['vat'] += $row->tax;
            $data['total']['purchase'] += $row->grand_total;
            $data['total']['Cash'] += $row->Cash;
            $data['total']['Card'] += $row->Card;
            $data['total']['Bikash'] += $row->Bikash;
            $data['total']['Nagad'] += $row->Nagad;
            $data['total']['Rocket'] += $row->Rocket;
            $data['total']['Cheque'] += $row->Cheque;
            $data['total']['Voucher'] += $row->Voucher;
            $data['total']['Due'] += $row->Due;
            $data['total']['change'] += $row->change;
            $data['total']['cash_from_till'] += $row->cash_from_till;
        }
        return $this->response($data, REST_Controller::HTTP_OK);
    }

    public function get_get($id)
    {
        $data['purchase']           = $this->Purchases->get_single('purchase', array('id' => $id))->row();
        $data['purchase']->supplier = json_decode($this->db->where('id', $data['purchase']->supplier_id)->get('suppliers')->row()->name);
        $data['details']            = $this->Purchases->get_list('purchase_details', array('purchase_id' => $id))->result();
        foreach ($data['details'] as $index => $obj) {
            $data['details'][$index]->name      = json_decode($obj->name);
            $data['details'][$index]->unit_name = json_decode($obj->unit_name);
        }
        return $this->response($data, REST_Controller::HTTP_OK);
    }
}
