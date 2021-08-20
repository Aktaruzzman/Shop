<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgdelauto extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgdelautos');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $hub_id = null, $deleted = null, $limit = null, $offset = null)
    {

        if (!empty($config))
            $results = $this->Cfgdelautos->build_config($hub_id);
        if (!empty($id) && empty($edit)) {
            $entity = $this->Cfgdelautos->get($id, true, $deleted);
            $results['entity'] = $this->Cfgdelautos->build_entity($entity);
        } else {
            $conditions = null;
            if (config_item('store_type') == "multiple" && !empty($hub_id)) $conditions['hub_id'] = $hub_id;
            $results['list'] = $this->Cfgdelautos->get_by($conditions, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Cfgdelautos->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Cfgdelautos->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgdelautos->arrange_entity($this->Cfgdelautos->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgdelautos->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdelautos->getTable(), 'update',  $entity['id']);
                    add_activity('Delivery auto plan', 'A Delivery auto plan updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgdelautos->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdelautos->getTable(), 'create',  $id);
                    add_activity('Delivery auto plan', 'A new Delivery auto plan added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('Delivery') . ' ' . lang('option')) : sprintf(lang('saved_failed_msg'), lang('Delivery') . ' ' . lang('option'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdelautos->entity($this->put());
            $results['status'] = $this->Cfgdelautos->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdelautos->getTable(), 'update',  $id);
                add_activity('Delivery auto plan', 'A Delivery auto plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('Delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('Delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $results['status'] = $this->Cfgdelautos->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdelautos->getTable(), 'update',  $id);
                add_activity('Delivery auto plan', 'A Delivery auto plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('Delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('Delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Cfgdelautos->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Cfgdelautos->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Cfgdelautos->defaultBy()]) {
                $this->Cfgdelautos->unset_default(NULL);
                $results['status'] = $this->Cfgdelautos->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdelautos->getTable(), 'update',  $id);
                    add_activity('Delivery auto plan', 'Default Delivery auto plan changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('Delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('Delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgdelautos->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgdelautos->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgdelautos->getTable(), 'delete',  $id);
                    add_activity('Delivery auto plan', 'A Delivery auto plan deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('Delivery') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('Delivery') . ' ' . lang('plan'));
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
            $results['status'] = $this->Cfgdelautos->save([$this->Cfgdelautos->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdelautos->getTable(), 'update',  $id);
                add_activity('Delivery auto plan', 'A Delivery auto plan restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('Delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('Delivery') . ' ' . lang('plan'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelautos->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdelautos->getTable(), 'delete',  $id);
                add_activity('Delivery auto plan', 'A Delivery auto plan deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('Delivery') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('Delivery') . ' ' . lang('plan'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
