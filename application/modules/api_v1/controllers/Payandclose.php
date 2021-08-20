<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Payandclose extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Saledetails');
            $this->load->model('Cfgconfigs');
            $this->load->model('Cfgvouchers');
            $this->load->model('Tableplans');
            $this->load->model('Salediscountdetails');
            $this->load->model('Salepaymentdetails');
            $this->load->model('Customers');
            $this->load->model('Customerdues');
            $this->load->model('Storecustomers');
        }
    }
    public function index_get()
    {
        $results['config'] = $this->Cfgconfigs->get_all();
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $headers = getallheaders();
        $entity = $this->Sales->post_entity($this->post());
        $sale_entity = $this->Sales->entity($entity['sale']);
        //UPDATE SALES TABLE
        if ($sale_entity['service'] === "table") $this->Tableplans->save(['status' => 'inactive'], $sale_entity['cust_meta']['table_id']);
        $sale_entity['cust_meta'] = $this->Sales->encode($sale_entity['cust_meta']);
        $sale_entity['updated_by'] = $headers['user_id'];
        $sale_entity['closed_on'] = closed_on();
        $this->Sales->save($sale_entity, $sale_entity['id']);
        //INSERT/RE-INSERT SALE ITEMS
        $this->Saledetails->delete_by(['sale_id' => $sale_entity['id']], TRUE);
        $this->Saledetails->save_details($entity['sale_details'], TRUE);
        //INSERT/RE-INSERT SALE DISCOUNTS DETAILS
        $this->Salediscountdetails->delete_by(['sale_id' => $sale_entity['id']], TRUE);
        $sale_discount_detail_entity = $this->Salediscountdetails->entity($entity['sale_discount_details']);
        $this->Salediscountdetails->save($sale_discount_detail_entity);
        //INSERT/RE-INSERT SALE PAYMENT DETAILS
        $this->Salepaymentdetails->delete_by(['sale_id' => $sale_entity['id']], TRUE);
        if ($sale_entity['Voucher'] > 0) $this->update_voucher_payment($entity['sale_payment_details']);
        $payment_details['details'] = $this->Salepaymentdetails->encode($entity['sale_payment_details']);
        $payment_details['sale_id'] = $sale_entity['id'];
        $this->Salepaymentdetails->save($payment_details);
        //MANAGE CUSTOMER SALES DUE 
        if (!empty($entity['customerdues'])) $this->udpate_customerdues($entity['customerdues']);
        if (!empty($sale_entity['bill_on_payment']) && ($sale_entity['bill_on_payment'] !== 'sms' || $sale_entity['bill_on_payment'] === "email")) {
            if ($sale_entity['bill_on_payment'] !== 'sms') {
                $this->Sales->send_sms($sale_entity['id']);
                $this->Sales->send_email($sale_entity['id']);
            } else  $this->Sales->send_email($sale_entity['id']);
        }
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status();
        return $this->response($results);
    }

    private function update_voucher_payment($payments)
    {
        foreach ($payments as $payment) {
            if ($payment['value'] == "Voucher" && $payment['amount'] > 0) {
                $voucher = $this->Cfgvouchers->get_by(['code' => $payment['code']], TRUE, TRUE);
                $this->Cfgvouchers->save(['balance' => $voucher->balance - doubleval($payment['amount'])], $voucher->id);
            }
        }
    }

    private function udpate_customerdues($customerdues)
    {

        if (config_item('store_type') === "multiple") {
            $customer = $this->Storecustomers->get(['hub_id' => $customerdues['hub_id'], 'cust_id' => $customerdues['cust_id']], true, true);
            if (!empty($customer)) {
                $total = $customer->due + doubleval($customerdues['amount']);
                $this->Storecustomers->save(['due' => $total], $customer->id);
            } else {
                $entity = ['hub_id' => $customerdues['hub_id'], 'cust_id' => $customerdues['cust_id'], 'due' => $customerdues['amount']];
                $this->Storecustomers->save($entity);
            }
        }
        $customer = $this->Customers->get($customerdues['cust_id'], TRUE);
        $total = $customer->due + doubleval($customerdues['amount']);
        $this->Customers->save(['due' => $total], $customer->id);
        $this->Customerdues->save($customerdues);
    }

    public function voucher_get($code)
    {
        $results = false;
        $row = $this->Cfgvouchers->get_by(['code' => $code], TRUE);
        if (!empty($row)) $results = (time() * 1000) >= $row->start && (time() * 1000) <= $row->end && $row->balance > 0 ? $row : false;
        return $this->response($results);
    }
    public function customer_get($phone)
    {
        return $this->response($this->Customers->get_by(['phone' => $phone], TRUE, TRUE));
    }
}
