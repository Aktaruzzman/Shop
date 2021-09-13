<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Purchase extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Purchases');
            $this->load->model('Suppliers');
            $this->load->model('Payments');
            $this->load->model('Userhubs');
            $this->load->model('Geoareas');
            $this->load->model('Purchasedetails');
            $this->load->model('Supplierdues');
            $this->load->model('Supplieragents');
            $this->load->model('Storesupplieragents');
        }
    }


    public function index_get($id = null, $from = null, $to = null, $search = null, $limit = null, $offset = null, $hub_id = null, $deleted = false)
    {
        if (!empty($id)) {
            $results = $this->Purchases->build_entity($this->Purchases->get($id, true, false));
            return $this->response($results, REST_Controller::HTTP_OK);
        } else {
            if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
            if (empty($to))  $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
            $results['rows']  = $this->Purchases->get_purchases($from, $to, $search, ['invoice'], $limit, $offset, $hub_id, $deleted);
            $results['total'] = ['discount' => 0, 'vat' => 0, 'purchase' => 0, 'payment' => 0, 'Cash' => 0, 'Card' => 0, 'Bikash' => 0, 'Nagad' => 0, 'Rocket' => 0, 'Cheque' => 0, 'Voucher' => 0, 'Due' => 0, 'change' => 0, 'cash_from_till' => 0];
            foreach ($results['rows'] as $index => $row) {
                $results['rows'][$index] = $this->Purchases->build_entity($row);
                $results['total']['discount'] += $row->discount;
                $results['total']['vat'] += $row->tax;
                $results['total']['purchase'] += $row->grand_total;
                $results['total']['Cash'] += $row->Cash;
                $results['total']['Card'] += $row->Card;
                $results['total']['Bikash'] += $row->Bikash;
                $results['total']['Nagad'] += $row->Nagad;
                $results['total']['Rocket'] += $row->Rocket;
                $results['total']['Cheque'] += $row->Cheque;
                $results['total']['Voucher'] += $row->Voucher;
                $results['total']['Due'] += $row->Due;
                $results['total']['change'] += $row->change;
                $results['total']['cash_from_till'] += $row->cash_from_till;
            }
            $results['hubs'] = $this->Userhubs->get(['status' => 'active'], null, null, ['id', 'name', 'area_id', 'house_en', 'house_bn']);
            foreach ($results['hubs'] as $k => $v) {
                $results['hubs'][$k]->name = $this->Userhubs->decode($v->name);
                $results['hubs'][$k]->area = $this->Geoareas->formatted($v->area_id)->formatted;
                if ($hub_id == $v->id) $results['hub'] = $results['hubs'][$k];
            }
            return $this->response($results, REST_Controller::HTTP_OK);
        }
    }
    public function index_post($id = null)
    {
        if (!empty($id)) {
            $post = $this->post();
            $this->db->trans_start();
            if (!empty($post['supplierDue'])) {
                $this->supplierdue($this->Supplierdues->entity($post['supplierDue']));
            }
            $this->Purchases->save($this->Purchases->entity($post['purchase']), $id);
            $this->Purchasedetails->save_details($post['purchase_details']);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    private function supplierdue($supplierdues)
    {
        $this->db->trans_start();
        $supplieragent = $this->Supplieragents->get($supplierdues['supplieragent_id'], true, true);
        $supplieragent_total = $supplieragent->due + doubleval($supplierdues['amount']);
        $this->Supplieragents->save(['due' => $supplieragent_total], $supplieragent->id);

        $store_supplieragent = $this->Storesupplieragents->get(['hub_id' => $supplierdues['hub_id'], 'supplieragent_id' => $supplierdues['supplieragent_id']], true, true);
        if (!empty($store_supplieragent)) {
            $store_supplieragent_total = $store_supplieragent->due + doubleval($supplierdues['amount']);
            $this->Storesupplieragents->save(['due' => $store_supplieragent_total], $store_supplieragent->id);
        } else $this->Storesupplieragents->save(['supplieragent_id' => $supplierdues['supplieragent_id'], 'hub_id' => $supplierdues['hub_id'], 'due' => $supplierdues['amount']]);

        $this->Supplierdues->save($supplierdues);
        $this->db->trans_complete();
    }

    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Purchases->entity($this->put());
            $this->Purchases->save($entity, $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('purchase')) : sprintf(lang('update_failed_msg'), lang('purchase'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Purchases->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('purchase')) : sprintf(lang('update_failed_msg'), lang('purchase'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Purchases->delete($id, true);
            add_activity('Purchase', 'A purchase deleted');
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('purchase')) : sprintf(lang('delete_failed_msg'), lang('purchase'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function new_get()
    {
        //get_supplieragents($hub_id = null, $deleted = null, $limit = null, $offset = null, $status = null)


        $results['list'] = $this->Suppliers->get(null, false, false, ['id', 'name']);
        foreach ($results['list'] as $k => $v) {
            $results['list'][$k]->name = $this->Suppliers->decode($v->name);
        }
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], false, false, ['id', 'name', 'house', 'area_id']);
        foreach ($results['hubs'] as $index => $obj) {
            $results['hubs'][$index]->name  = $this->Userhubs->decode($obj->name);
            $results['hubs'][$index]->house = $this->Userhubs->decode($obj->house);
            $results['hubs'][$index]->area  = $this->Geoareas->formatted($obj->area_id)->formatted;
        }
        $results['agents'] = $this->Supplieragents->get_supplieragents(getallheaders()['hub_id'], null, null, null, 'active');
        foreach ($results['agents'] as $index => $obj) {
            $results['agents'][$index]->name = $this->Supplieragents->decode($obj->name);
        }
        return $this->response($results);
    }

    public function new_post()
    {
        $headers = getallheaders();
        $post = $this->post();
        $post['created_by'] = $headers['user_id'];
        $post['hub_id'] = $post['hub_id'] ? $post['hub_id'] : $headers['hub_id'];
        $post['device_code'] = $headers['device_code'];
        $post['closed_on'] = closed_on();
        $post['status'] = "pending";
        $id = $this->Purchases->save($post);
        $invoice = $this->Purchases->invoice($post['hub_id'], $headers['user_id'], $id);
        $this->Purchases->save(['invoice' => $invoice], $id);
        $results['order'] = $this->Purchases->get($id, true);
        $results['supplier'] = $this->Suppliers->get($results['order']->supplier_id, true);
        $results['supplier']->name = $this->Suppliers->decode($results['supplier']->name);
        $results['supplieragent'] = $this->Supplieragents->get($results['order']->supplieragent_id, true);
        $results['supplieragent']->name = $this->Supplieragents->decode($results['supplieragent']->name);
        $results['payments'] = $this->Payments->get(['status' => 'active'], false, false, ['id', 'value', 'name', 'fee', 'order']);
        foreach ($results['payments'] as $index => $payment) {
            $results['payments'][$index]->name      = $this->Payments->decode($payment->name);
            $results['payments'][$index]->amount    = 0;
            $results['payments'][$index]->reference = null;
        }
        return $this->response($results);
    }
}
