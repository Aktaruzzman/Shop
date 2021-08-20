<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Expense extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Expenses');
        }
    }

    public function index_get($id = null, $sector = null, $config = null, $hub_id = null, $deleted = 0, $from = null, $to = null)
    {
        if ($config) $results = $this->Expenses->build_config($hub_id);
        $results['list'] = $this->Expenses->get_expenses($sector, $from, $to, $deleted, $hub_id);
        $results['cash'] = 0;
        $results['other'] = 0;
        $results['cash_in_till'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->sector_name = $this->Expenses->decode($obj->sector_name);
            if ($results['list'][$index]->payment === "Cash") {
                $results['cash'] += $results['list'][$index]->amount;
                $results['list'][$index]->source == "till" ? $results['cash_in_till'] += $results['list'][$index]->amount : true;
            } else  $results['other'] += $results['list'][$index]->amount;
            if (intval($id) === intval($obj->id)) $results['entity'] = $results['list'][$index];
        }
        $results['total'] = $results['cash'] + $results['other'];
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Expenses->validate()) {
            $this->db->trans_start();
            $entity = $this->Expenses->arrange_entity($this->Expenses->entity($this->post()));
            if (!empty($entity['id'])) {
                $this->Expenses->save($entity, $entity['id']);
                add_activity('expense', 'An expense updated');
            } else {
                $this->Expenses->save($entity);
                add_activity('expense', 'A new expense added');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('expense')) : sprintf(lang('saved_failed_msg'), lang('expense'));
            return $this->response($results);
        }
        return  $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Expenses->entity($this->put());
            $this->Expenses->save($entity, $id);
            add_activity('expense', 'An expense updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('expense')) : sprintf(lang('update_failed_msg'), lang('expense'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Expenses->save($this->patch(), $id);
            add_activity('expense', 'An expense updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('expense')) : sprintf(lang('update_failed_msg'), lang('expense'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Expenses->delete($id);
            add_activity('expense', 'An expense deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('expense')) : sprintf(lang('delete_failed_msg'), lang('expense'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Expenses->save([$this->Expenses->deletedAt() => false], $id);
            add_activity('expense', 'An expense restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('expense')) : sprintf(lang('update_failed_msg'), lang('expense'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Expenses->delete($id, true);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('expense')) : sprintf(lang('delete_failed_msg'), lang('expense'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
