<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Stockshifting extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Stockhubentries');
            $this->load->model('Stockshiftings');
        }
    }
    public function index_get($from = NULL, $to = NULL, $hub_id)
    {
        if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
        if (empty($to)) $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;
        $results['list'] = $this->Stockshiftings->get_rows($from, $to, $hub_id);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Stockshiftings->validate()) {
            $entity = $this->Stockshiftings->post_entity($this->post());
            $entity['closed_on'] = closed_on();
            $entry_qty = $entity['qty'];
            $this->db->trans_start();
            $fromStockhubentries = $this->Stockhubentries->get_by(['stock_id' => $entity['stock_id'], 'hub_id' => $entity['from_hub']], TRUE);
            $toStockhubentries = $this->Stockhubentries->get_by(['stock_id' => $entity['stock_id'], 'hub_id' => $entity['to_hub']], TRUE);
            $shifting_from = FALSE;
            $shifting_to = FALSE;
            if (!empty($fromStockhubentries)) {
                $from_current_balance = $fromStockhubentries->balance;
                $from_balance_remaning =  $from_current_balance  - (float) $entity['qty'];
                $entry_qty = $from_balance_remaning >= 0 ? (float) $entity['qty'] : $from_current_balance;
                $from_new_balance = $from_current_balance - $entry_qty;
                if ($entry_qty > 0)  $shifting_from = $this->Stockhubentries->save(['balance' => $from_new_balance], $fromStockhubentries->id);
            } else {
                $this->Stockhubentries->save(['hub_id' => $entity['from_hub'], 'stock_id' => $entity['stock_id'], 'sales' => 0, 'balance' => 0]);
            }
            if (!empty($toStockhubentries)) {
                $to_current_balance = $toStockhubentries->balance;
                $to_new_balance = $to_current_balance + (float)$entry_qty;
                $shifting_to = $this->Stockhubentries->save(['balance' =>  $to_new_balance], $toStockhubentries->id);
            } else {
                if ($shifting_from) $shifting_to = $this->Stockhubentries->save(['hub_id' => $entity['from_hub'], 'stock_id' => $entity['stock_id'], 'sales' => 0, 'balance' => $entry_qty]);
                else $this->Stockhubentries->save(['hub_id' => $entity['from_hub'], 'stock_id' => $entity['stock_id'], 'sales' => 0, 'balance' => 0]);
            }
            if ($shifting_from && $shifting_to) {
                unset($entity['id']);
                $this->Stockshiftings->save($entity);
                add_activity('Stock Shifting', 'Some stocks shifted');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('item')) : sprintf(lang('saved_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Stockhubentries->entity($this->put());
            $this->Stockhubentries->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Stockhubentries->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Stockhubentries->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('item')) : sprintf(lang('delete_failed_msg'), lang('item'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Stockhubentries->save([$this->Stockhubentries->deletedAt() => FALSE], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Stockhubentries->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('item')) : sprintf(lang('delete_failed_msg'), lang('item'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
