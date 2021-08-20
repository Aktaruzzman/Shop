<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgdelplan extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Geoareas');
            $this->load->model('Cfgdelplans');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $base = null, $deleted = null, $limit = null, $offset = null)
    {

        if (!empty($config)) {
            $results = $this->Cfgdelplans->build_config();
            $results['base'] = $base ? $this->Geoareas->get($base, true, $deleted, ['id', 'name', 'name_en', 'name_bn', 'default', 'status', 'deleted_at']) : null;
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Cfgdelplans->get($id, true, $deleted);
            $results['entity'] = $this->Cfgdelplans->build_entity($entity);
        } else {
            $conditions = null;
            if (!empty($base)) $conditions = ['area_id' => $base];
            if (config_item('store_type') == "multiple")  $conditions['hub_id'] = getallheaders()['hub_id'];
            $results['list'] = $this->Cfgdelplans->get_by($conditions, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) $results['list'][$index] = $this->Cfgdelplans->build_entity($obj);
            $results['total'] = !empty($base) ? $this->Cfgdelplans->count_rows(['area_id' => $base], $deleted) : $this->Cfgdelplans->count_rows(null, $deleted);
            $results['entity'] = !empty($id) ? $this->Cfgdelplans->get($id, true, $deleted) : null;
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Cfgdelplans->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgdelplans->arrange_entity($this->Cfgdelplans->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgdelplans->save($entity, $entity['id']);
                if ($results['status']) {
                    add_sync($this->Cfgdelplans->getTable(), 'update', $entity['id']);
                    add_activity('Blog', 'A delivery plan updated');
                }
            } else {
                $entity['hub_id'] = getallheaders()['hub_id'];
                $results['status'] = $id = $this->Cfgdelplans->save($entity);
                if ($results['status']) {
                    add_sync($this->Cfgdelplans->getTable(), 'create', $id);
                    add_activity('Blog', 'A delivery plan updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('saved_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdelplans->entity($this->put());
            $results['status'] = $this->Cfgdelplans->save($entity, $id);
            if ($results['status']) {
                add_sync($this->Cfgdelplans->getTable(), 'update', $entity['id']);
                add_activity('Delivery Plan', 'A delivery plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelplans->save($this->patch(), $id);
            if ($results['status']) {
                add_sync($this->Cfgdelplans->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan updated');
            }

            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Cfgdelplans->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Cfgdelplans->get($id, true);
            if (!empty($obj) && !$obj[$this->Cfgdelplans->defaultBy()]) {
                $this->Cfgdelplans->unset_default(['area_id' => $obj['area_id']]);
                $results['status'] = $this->Cfgdelplans->set_default(['id' => $obj['id']]);
                if ($results['status']) {
                    add_sync($this->Cfgdelplans->getTable(), 'update', $id);
                    add_activity('Delivery Plan', 'A delivery plan updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $obj = $this->Cfgdelplans->get($id, true);
            if (empty($obj->default) || !$obj->default) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgdelplans->delete($id);
                if ($results['status']) {
                    add_sync($this->Cfgdelplans->getTable(), 'update', $id);
                    add_activity('Delivery Plan', 'A delivery plan deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('delivery') . ' ' . lang('plan'));
                return $this->response($results);
            } else return $this->response(['status' => false, 'message' => 'invalid request']);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelplans->save([$this->Cfgdelplans->deletedAt() => false], $id);
            if ($results['status']) {
                add_sync($this->Cfgdelplans->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdelplans->delete($id, true);
            if ($results['status']) {
                add_sync($this->Cfgdelplans->getTable(), 'update', $id);
                add_activity('Delivery Plan', 'A delivery plan deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
