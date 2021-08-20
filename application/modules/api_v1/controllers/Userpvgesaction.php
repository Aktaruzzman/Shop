<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Userpvgesaction extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Userpvgesactions');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $status = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Userpvgesactions->get($id, TRUE, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = $this->Userpvgesactions->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        return $this->response($results);
    }
    private function build_entity($entity)
    {
        return $entity;
    }
    public function index_post()
    {
        if ($this->Userpvgesactions->validate()) {
            $this->db->trans_start();
            $entity = $this->Userpvgesactions->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Userpvgesactions->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('User privileges', 'User privileges updated');
            } else {
                $results['status'] = $id = $this->Userpvgesactions->save($entity);
                if (!empty($results['status'])) add_activity('User privileges', 'User privileges added');;
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
            $entity = $this->Userpvgesactions->entity($this->put());
            $results['status'] = $this->Userpvgesactions->save($entity, $id);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Userpvgesactions->save($this->patch(), $id);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Userpvgesactions->delete($id);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('item')) : sprintf(lang('delete_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Userpvgesactions->save([$this->Userpvgesactions->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Userpvgesactions->delete($id, TRUE);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges deleted permanently from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('item')) : sprintf(lang('delete_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
