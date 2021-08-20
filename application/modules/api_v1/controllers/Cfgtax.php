<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgtax extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgtaxes');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Cfgtaxes->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Cfgtaxes->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Cfgtaxes->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Cfgtaxes->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Cfgtaxes->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgtaxes->arrange_entity($this->Cfgtaxes->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgtaxes->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgtaxes->getTable(), 'update',  $entity['id']);
                    add_activity('Tax Option', 'A tax option updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgtaxes->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgtaxes->getTable(), 'create',  $id);
                    add_activity('Tax Option', 'A new tax option added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('saved_failed_msg'), lang('tax') . ' ' . lang('option'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgtaxes->entity($this->put());
            $results['status'] = $this->Cfgtaxes->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgtaxes->getTable(), 'update',  $id);
                add_activity('Tax Option', 'A tax option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('tax') . ' ' . lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $results['status'] = $this->Cfgtaxes->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgtaxes->getTable(), 'update',  $id);
                add_activity('Tax Option', 'A tax option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('tax') . ' ' . lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Cfgtaxes->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Cfgtaxes->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Cfgtaxes->defaultBy()]) {
                $this->Cfgtaxes->unset_default(NULL);
                $results['status'] = $this->Cfgtaxes->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgtaxes->getTable(), 'update',  $id);
                    add_activity('Tax Option', 'Default tax option changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('tax') . ' ' . lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgtaxes->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgtaxes->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgtaxes->getTable(), 'delete',  $id);
                    add_activity('Tax Option', 'A tax option deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('delete_failed_msg'), lang('tax') . ' ' . lang('option'));
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
            $results['status'] = $this->Cfgtaxes->save([$this->Cfgtaxes->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgtaxes->getTable(), 'update',  $id);
                add_activity('Tax Option', 'A tax option restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('update_failed_msg'), lang('tax') . ' ' . lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgtaxes->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgtaxes->getTable(), 'delete',  $id);
                add_activity('Tax Option', 'A tax option deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('tax') . ' ' . lang('option')) : sprintf(lang('delete_failed_msg'), lang('tax') . ' ' . lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
    public function test_get()
    {
        $results['max'] = $this->Cfgtaxes->get_max($this->Cfgtaxes->orderBy());
        return $this->response($results);
    }
}
