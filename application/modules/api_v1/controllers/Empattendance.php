<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Empattendance extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Empattendances');
            $this->load->model('Employees');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $emp_id = NULL, $hub_id = null, $deleted = NULL, $limit = NULL, $offset = NULL, $from = NULL, $to = NULL)
    {
        if (!empty($config))
            $results = $this->Empattendances->build_config($hub_id);
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Empattendances->get($id, TRUE, $deleted);
            if (!empty($results['entity'])) $results['entity'] = $this->Empattendances->build_entity($results['entity']);
        } else {
            if (!empty($emp_id)) $results['base'] = $this->Employees->get($emp_id, TRUE, TRUE, ['id', 'name']);
            if (!empty($results['base'])) $results['base']->name = $this->Employees->decode($results['base']->name);
            $results['list'] = $this->Empattendances->get_attendance($emp_id, $hub_id, date('Y-m-d', $from / 1000),  date('Y-m-d', $to / 1000), $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Empattendances->build_entity($obj);
                if (intval($id) === intval($obj->id))  $results['entity'] = $results['list'][$index];
            }
            $results['total'] = count($results['list']);
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Empattendances->validate()) {
            $this->db->trans_start();
            $entity = $this->Empattendances->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Empattendances->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Empattendances->getTable(), 'update',  $entity['id']);
                    add_activity('employee attendance', 'A employee attendance updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Empattendances->get_max($order_by) + 1;
                }
                if ($this->Empattendances->check_entry($entity)) {
                    $results['status'] = $id = $this->Empattendances->save($entity);
                    if (!empty($results['status'])) {
                        add_sync($this->Empattendances->getTable(), 'create',  $id);
                        add_activity('employee attendance', 'A new employee attendance added');
                    }
                } else {
                    $results['status'] = FALSE;
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('attendance')) : sprintf(lang('saved_failed_msg'), lang('attendance'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Empattendances->entity($this->put());
            $results['status'] = $this->Empattendances->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Empattendances->getTable(), 'update',  $id);
                add_activity('employee attendance', 'An employee attendance updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('attendance')) : sprintf(lang('update_failed_msg'), lang('attendance'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Empattendances->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Empattendances->getTable(), 'update',  $id);
                add_activity('employee attendance', 'An employee attendance updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('attendance')) : sprintf(lang('update_failed_msg'), lang('attendance'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Empattendances->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Empattendances->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Empattendances->defaultBy()]) {
                $this->Empattendances->unset_default(NULL);
                $results['status'] = $this->Empattendances->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Empattendances->getTable(), 'update',  $id);
                    add_activity('employee attendance', 'An employee attendance updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('attendance')) : sprintf(lang('update_failed_msg'), lang('attendance'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Empattendances->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Empattendances->getTable(), 'delete',  $id);
                add_activity('employee attendance', 'An employee attendance deleted');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('attendance')) : sprintf(lang('delete_failed_msg'), lang('attendance'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Empattendances->save([$this->Empattendances->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Empattendances->getTable(), 'delete',  $id);
                add_activity('employee attendance', 'An employee attendance restored');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('attendance')) : sprintf(lang('update_failed_msg'), lang('attendance'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Empattendances->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Empattendances->getTable(), 'delete',  $id);
                add_activity('employee attendance', 'An employee attendance deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('attendance')) : sprintf(lang('delete_failed_msg'), lang('attendance'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
