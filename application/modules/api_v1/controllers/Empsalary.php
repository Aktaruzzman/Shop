<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Empsalary extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Empsalaries');
            $this->load->model('Employees');
            $this->load->model('Payments');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $base = null, $hub_id = null, $deleted = null, $limit = null, $offset = null, $from = null, $to = null)
    {
        if (!empty($config))
            $results = $this->Empsalaries->build_config($hub_id);
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Empsalaries->get($id, true, $deleted);
            if (!empty($results['entity'])) $results['entity'] = $this->Empsalaries->build_entity($results['entity']);
        } else {
            if (!empty($base)) $results['base'] = $this->Employees->get($base, true, true, ['id', 'name']);
            if (!empty($results['base'])) $results['base']->name = json_decode($results['base']->name);
            $results['list'] = $this->Empsalaries->get_salary($base, $hub_id, $from, $to, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Empsalaries->build_entity($obj);
                if (intval($id) === intval($obj->id)) $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Empsalaries->validate()) {
            $this->db->trans_start();
            $entity = $this->Empsalaries->arrange_entity($this->Empsalaries->entity($this->post()));
            $results['status'] = false;
            if (!empty($entity['id'])) {
                $results['status'] = $this->Empsalaries->save($entity, $entity['id']);
                $results['status'] ? add_activity('employee salary', 'An employee salary data updated') : true;
            } else {
                if ($this->Empsalaries->check_entry($entity)) {
                    $results['status']   = $this->Empsalaries->save($entity);
                    $results['status'] ? add_activity('employee salary', 'An employee salary data added') : true;
                }
            }
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('salary')) : sprintf(lang('saved_failed_msg'), lang('salary'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Empsalaries->entity($this->put());
            $results['status'] = $this->Empsalaries->save($entity, $id);
            $results['status'] ? add_activity('employee salary', 'An employee salary data updated') : true;
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('salary')) : sprintf(lang('update_failed_msg'), lang('salary'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Empsalaries->save($this->patch(), $id);
            $results['status'] ? add_activity('employee salary', 'An employee salary data updated') : true;
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('salary')) : sprintf(lang('update_failed_msg'), lang('salary'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Empsalaries->delete($id);
            $results['status'] ? add_activity('employee salary', 'An employee salary data deleted into bin') : true;
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('salary')) : sprintf(lang('delete_failed_msg'), lang('salary'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Empsalaries->save([$this->Empsalaries->deletedAt() => false], $id);
            $results['status'] ? add_activity('employee salary', 'An employee salary data restored from bin') : true;
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('salary')) : sprintf(lang('update_failed_msg'), lang('salary'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Empsalaries->delete($id, true);
            $results['status'] ? add_activity('employee salary', 'An employee salary data deleted permanently') : true;
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('salary')) : sprintf(lang('delete_failed_msg'), lang('salary'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
