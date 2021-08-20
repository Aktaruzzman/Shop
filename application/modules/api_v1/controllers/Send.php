<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Send extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Saledetails');
            $this->load->model('Cfgconfigs');
            $this->load->model('Cfgvouchers');
            $this->load->model('Salediscountdetails');
            $this->load->model('Customers');
            $this->load->model('Dispatchers');
        }
    }
    public function index_post()
    {
        $this->db->trans_start();
        $headers = getallheaders();
        $entity = $this->Sales->post_entity($this->post());
        $dispatcher_entity = array_key_exists('dispatcher', $entity['sale']) ? $entity['sale']['dispatcher'] : null;
        //UPDATE SALES TABLE
        $sale_entity = $this->Sales->entity($entity['sale']);
        $sale_entity['cust_meta'] = $this->Sales->encode($sale_entity['cust_meta']);
        $sale_entity['updated_by'] = $headers['user_id'];
        $this->Sales->save($sale_entity, $sale_entity['id']);
        //INSERT/RE-INSERT SALE ITEMS
        if (!empty($entity['sale_details'])) {
            $this->Saledetails->delete_by(['sale_id' => $sale_entity['id']], TRUE);
            $this->Saledetails->save_details($entity['sale_details'], FALSE);
        }
        //INSERT/RE-INSERT SALE DISCOUNTS DETAILS
        if (!empty($entity['sale_discount_details'])) {
            $this->Salediscountdetails->delete_by(['sale_id' => $sale_entity['id']], TRUE);
            $sale_discount_detail_entity = $this->Salediscountdetails->entity($entity['sale_discount_details']);
            $this->Salediscountdetails->save($sale_discount_detail_entity);
        }
        if (!empty($dispatcher_entity) && $entity['sale_details']) {
            $dispatcher_entity['sale_details'] = $entity['sale_details'];
            $results['return'] = $this->Dispatchers->dispatch($dispatcher_entity);
        }
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status();
        return $this->response($results);
    }
}
