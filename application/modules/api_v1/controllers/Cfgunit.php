<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgunit extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgunits');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Cfgunits->get($id, TRUE, $deleted);
            $results['entity'] = $this->Cfgunits->build_entity($entity);
        } else {
            $results['list'] = $this->Cfgunits->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Cfgunits->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Cfgunits->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Cfgunits->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgunits->arrange_entity($this->Cfgunits->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgunits->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgunits->getTable(), 'update',  $entity['id']);
                    add_activity('Unit Option', 'A unit option updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgunits->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgunits->getTable(), 'create',  $id);
                    add_activity('Unit Option', 'A new unit option added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('scale_unit')) : sprintf(lang('saved_failed_msg'), lang('scale_unit'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgunits->entity($this->put());
            $results['status'] = $this->Cfgunits->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgunits->getTable(), 'update',  $id);
                add_activity('Unit Option', 'A Unit option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('scale_unit')) : sprintf(lang('update_failed_msg'), lang('scale_unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgunits->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgunits->getTable(), 'update',  $id);
                add_activity('Unit Option', 'A unit option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('scale_unit')) : sprintf(lang('update_failed_msg'), lang('scale_unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Cfgunits->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Cfgunits->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Cfgunits->defaultBy()]) {
                $this->Cfgunits->unset_default(NULL);
                $results['status'] = $this->Cfgunits->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgunits->getTable(), 'update',  $id);
                    add_activity('Unit Option', 'Default unit option changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('scale_unit')) : sprintf(lang('update_failed_msg'), lang('scale_unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgunits->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgunits->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgunits->getTable(), 'delete',  $id);
                    add_activity('Unit Option', 'A Unit option deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('scale_unit')) : sprintf(lang('delete_failed_msg'), lang('scale_unit'));
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
            $results['status'] = $this->Cfgunits->save([$this->Cfgunits->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgunits->getTable(), 'update',  $id);
                add_activity('Tax Option', 'A tax option restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('scale_unit')) : sprintf(lang('update_failed_msg'), lang('scale_unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgunits->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgunits->getTable(), 'delete',  $id);
                add_activity('Unit Option', 'A Unit option deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('scale_unit')) : sprintf(lang('delete_failed_msg'), lang('scale_unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
