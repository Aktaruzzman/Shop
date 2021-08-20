<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Incomesector extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Incomesectors');
            $this->load->model('Incomes');
        }
    }
    public function index_get($id = null, $config = null, $hub_id = null, $deleted = null, $from = null, $to = null)
    {
        if ($config) $results = $this->Incomesectors->build_config($hub_id);
        $results['list'] = $this->Incomesectors->get(['hub_id' => $hub_id], false, $deleted);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->Incomesectors->build_entity($obj);
            $results['list'][$index]->income = $this->Incomes->get_sector_income($obj->id, $from, $to, $hub_id);
            if ($id == $obj->id)  $results['entity'] =  $results['list'][$index];
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Incomesectors->validate()) {
            $this->db->trans_start();
            $entity = $this->Incomesectors->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Incomesectors->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('income sector', 'An income sector updated');
            } else {
                if (!empty($results['status'])) add_activity('income sector', 'A new income sector added');
                $this->Incomesectors->save($entity);
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
            $entity = $this->Incomesectors->entity($this->put());
            $this->Incomesectors->save($entity, $id);
            if (!empty($results['status'])) add_activity('income sector', 'An income sector updated');
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
            $this->Incomesectors->save($this->patch(), $id);
            add_activity('income sector', 'An income sector updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Incomesectors->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Incomesectors->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Incomesectors->defaultBy()]) {
                $this->Incomesectors->unset_default(null);
                $results['status'] = $this->Incomesectors->set_default(['id' => $obj['id']]);
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
            $results['status'] = $this->Incomesectors->delete($id);
            add_activity('income sector', 'An income sector deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Incomesectors->save([$this->Incomesectors->deletedAt() => false, 'status' => 'inactive'], $id);
            add_activity('income sector', 'An income sector restored from bin');
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
            $results['status'] = $this->Incomesectors->delete($id, true);
            add_activity('income sector', 'An income sector deleted permanently from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
