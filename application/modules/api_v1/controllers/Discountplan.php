<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Discountplan extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Discountplans');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Discountplans->get($id, TRUE, $deleted);
            $results['entity'] = $this->Discountplans->build_entity($entity);
        } else {
            $results['list'] = $this->Discountplans->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Discountplans->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] = $results['list'][$index];
            }
            $results['total'] = $this->Discountplans->count_rows(NULL, $deleted);
        }
        if (!empty($config)) $results = $this->Discountplans->build_config($results);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Discountplans->validate()) {
            $this->db->trans_start();
            $entity = $this->Discountplans->arrange_entity($this->Discountplans->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Discountplans->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'update', $entity['id']);
                    add_activity('discount plan', 'A discount plan updated');
                }
            } else {
                $results['status'] = $id = $this->Discountplans->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'create', $id);
                    add_activity('discount plan', 'A new discount plan added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Discountplans->entity($this->put());
            $results['status'] = $this->Discountplans->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'update', $id);
                add_activity('discount plan', 'A discount plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Discountplans->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'update', $id);
                add_activity('discount plan', 'A discount plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Discountplans->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Discountplans->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Discountplans->defaultBy()]) {
                $this->Discountplans->unset_default(NULL);
                $results['status'] = $this->Discountplans->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'update', $id);
                    add_activity('discount plan', 'A discount plan updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgvouchers->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Discountplans->delete($id);
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'delete', $id);
                    add_activity('discount plan', 'A discount plan deleted into bin');
                }
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('plan'));
                return $this->response($results);
            } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Discountplans->save([$this->Discountplans->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'update', $id);
                add_activity('discount plan', 'A discount plan restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('plan'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Discountplans->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'trash', $id);
                add_activity('discount plan', 'A discount plan deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('plan'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
