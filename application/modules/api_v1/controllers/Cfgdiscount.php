<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgdiscount extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgdiscounts');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Cfgdiscounts->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Cfgdiscounts->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Cfgdiscounts->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Cfgdiscounts->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Cfgdiscounts->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgdiscounts->arrange_entity($this->Cfgdiscounts->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgdiscounts->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdiscounts->getTable(), 'update',  $entity['id']);
                    add_activity('Discount option', 'A discount option updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgdiscounts->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdiscounts->getTable(), 'create', $id);
                    add_activity('Discount option', 'A new discount option added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('option'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdiscounts->entity($this->put());
            $results['status'] = $this->Cfgdiscounts->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdiscounts->getTable(), 'update',  $id);
                add_activity('Discount option', 'A discount option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdiscounts->entity($this->patch());
            $results['status'] = $this->Cfgdiscounts->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdiscounts->getTable(), 'update',  $id);
                add_activity('Discount option', 'A discount option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Cfgdiscounts->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Cfgdiscounts->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Cfgdiscounts->defaultBy()]) {
                $this->Cfgdiscounts->unset_default(NULL);
                $results['status'] = $this->Cfgdiscounts->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdiscounts->getTable(), 'update',  $id);
                    add_activity('Discount option', 'A discount option updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgdiscounts->get($id, TRUE);
            if (empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgdiscounts->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdiscounts->getTable(), 'delete',  $id);
                    add_activity('Discount option', 'A discount option deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('option'));
                return $this->response($results);
            } else {
                return $this->response(['status' => FALSE, 'message' => 'invalid request']);
            }
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdiscounts->save([$this->Cfgdiscounts->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdiscounts->getTable(), 'update',  $id);
                add_activity('Discount option', 'A discount option restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdiscounts->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdiscounts->getTable(), 'trash',  $id);
                add_activity('Discount option', 'A discount option deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('option')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
