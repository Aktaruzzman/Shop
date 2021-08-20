<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Expensesector extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Expensesectors');
            $this->load->model('Expenses');
        }
    }
    public function index_get($id = null, $config = null, $hub_id = null, $deleted = null, $from = null, $to = null)
    {

        if ($config) $results = $this->Expensesectors->build_config($hub_id);
        $results['list'] = $this->Expensesectors->get(['hub_id' => $hub_id], false, $deleted);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->Expensesectors->build_entity($obj);
            $results['list'][$index]->expense = $this->Expenses->get_sector_expense($obj->id, $from, $to, $hub_id);
            if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
        }
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Expensesectors->validate()) {
            $this->db->trans_start();
            $entity = $this->Expensesectors->arrange_entity($this->Expensesectors->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Expensesectors->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Expensesectors->getTable(), 'update',  $entity['id']);
                    add_activity('expense sector', 'An expense sector updated');
                }
            } else {
                $results['status'] = $id = $this->Expensesectors->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Expensesectors->getTable(), 'create',  $id);
                    add_activity('expense sector', 'A new expense sector added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return  $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Expensesectors->entity($this->put());
            $results['status'] = $this->Expensesectors->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Expensesectors->getTable(), 'update',  $id);
                add_activity('expense sector', 'An expense sector updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Expensesectors->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Expensesectors->getTable(), 'update',  $id);
                add_activity('expense sector', 'An expense sector updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Expensesectors->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Expensesectors->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Expensesectors->defaultBy()]) {
                $this->Expensesectors->unset_default(null);
                $results['status'] = $this->Expensesectors->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Expensesectors->getTable(), 'update',  $id);
                    add_activity('expense sector', 'An expense sector updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Expensesectors->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Expensesectors->getTable(), 'delete',  $id);
                add_activity('expense sector', 'An expense sector deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Expensesectors->save([$this->Expensesectors->deletedAt() => false, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) {
                add_sync($this->Expensesectors->getTable(), 'update',  $id);
                add_activity('expense sector', 'An expense sector restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Expensesectors->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Expensesectors->getTable(), 'update',  $id);
                add_activity('expense sector', 'An expense sector deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
