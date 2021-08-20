<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgdelarea extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgdelareas');
            $this->load->model('Users');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $hub_id = null, $deleted = null, $limit = null, $offset = null)
    {

        if (!empty($config))
            $results = $this->Cfgdelareas->build_config($hub_id);
        if (!empty($id) && empty($edit)) {
            $entity = $this->Cfgdelareas->get($id, true, $deleted);
            $results['entity'] = $this->Cfgdelareas->build_entity($entity);
        } else {
            $conditions = null;
            if (config_item('store_type') == "multiple" && !empty($hub_id)) $conditions['hub_id'] = $hub_id;
            $results['list'] = $this->Cfgdelareas->get_by($conditions, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Cfgdelareas->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Cfgdelareas->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgdelareas->arrange_entity($this->Cfgdelareas->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgdelareas->save($entity, $entity['id']);
                if ($results['status']) {
                    add_sync($this->Cfgdelareas->getTable(), 'update', $entity['id']);
                    add_activity('Delivery area', 'A delivery plan updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgdelareas->save($entity);
                if ($results['status']) {
                    add_sync($this->Cfgdelareas->getTable(), 'create', $id);
                    add_activity('Delivery area', 'A delivery plan updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('saved_failed_msg'), lang('delivery') . ' ' . lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdelareas->entity($this->put());
            $results['status'] = $this->Cfgdelareas->save($entity, $id);
            if ($results['status']) {
                add_sync($this->Cfgdelareas->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelareas->save($this->patch(), $id);
            if ($results['status']) {
                add_sync($this->Cfgdelareas->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan updated');
            }

            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Cfgdelareas->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Cfgdelareas->get($id, true);
            if (!empty($obj) && !$obj[$this->Cfgdelareas->defaultBy()]) {
                $this->Cfgdelareas->unset_default(['area_id' => $obj['area_id']]);
                $results['status'] = $this->Cfgdelareas->set_default(['id' => $obj['id']]);
                if ($results['status']) {
                    add_sync($this->Cfgdelareas->getTable(), 'update', $id);
                    add_activity('Delivery Plan', 'A delivery plan updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $obj = $this->Cfgdelareas->get($id, true);
            if (empty($obj->default) || !$obj->default) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgdelareas->delete($id);
                if ($results['status']) {
                    add_sync($this->Cfgdelareas->getTable(), 'update', $id);
                    add_activity('Delivery Plan', 'A delivery plan deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('delete_failed_msg'), lang('delivery') . ' ' . lang('area'));
                return $this->response($results);
            } else return $this->response(['status' => false, 'message' => 'invalid request']);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelareas->save([$this->Cfgdelareas->deletedAt() => false], $id);
            if ($results['status']) {
                add_sync($this->Cfgdelareas->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('area'));
            return $this->response($results);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelareas->delete($id, true);
            if ($results['status']) {
                add_sync($this->Cfgdelareas->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('delivery') . ' ' . lang('area')) : sprintf(lang('delete_failed_msg'), lang('delivery') . ' ' . lang('area'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
