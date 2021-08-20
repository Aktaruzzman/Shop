<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Employee extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Employees');
        }
    }
    public function index_get($id = NULL, $config = null, $edit = NULL, $hub_id = null, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($config))
            $results = $this->Employees->build_config($hub_id);
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Employees->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity'] = $this->Employees->build_entity($results['entity']);
        } else {
            $condition = config_item('store_type') === 'multiple' && !empty($hub_id) ? ['hub_id' => $hub_id] : null;
            $results['list'] = $this->Employees->get_by($condition, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Employees->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Employees->count_rows($condition, $deleted);
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Employees->validate()) {
            $this->db->trans_start();
            $entity = $this->Employees->arrange_entity($this->Employees->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Employees->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Employees->getTable(), 'update',  $entity['id']);
                    add_activity('employee', 'An employee data updated');
                }
            } else {
                $results['status'] = $id = $this->Employees->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Employees->getTable(), 'create',  $id);
                    add_activity('employee', 'A new employee added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('customer')) : sprintf(lang('saved_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Employees->entity($this->put());
            $results['status'] = $this->Employees->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Employees->getTable(), 'update',  $id);
                add_activity('employee', 'An employee data updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Employees->entity($this->patch());
            $results['status'] = $this->Employees->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Employees->getTable(), 'update',  $id);
                add_activity('employee', 'An employee data updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Employees->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Employees->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Employees->defaultBy()]) {
                $this->Employees->unset_default(NULL);
                $results['status'] = $this->Employees->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Employees->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Employees->getTable(), 'delete',  $id);
                add_activity('employee', 'An employee deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer')) : sprintf(lang('delete_failed_msg'), lang('customer'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Employees->save([$this->Employees->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Employees->getTable(), 'update',  $id);
                add_activity('employee', 'An employee restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Employees->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Employees->getTable(), 'trash',  $id);
                add_activity('employee', 'An employee deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer')) : sprintf(lang('delete_failed_msg'), lang('customer'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
