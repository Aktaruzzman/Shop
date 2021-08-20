<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Tableplan extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Tableplans');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Tableplans->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Tableplans->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Tableplans->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Tableplans->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $results['list'] = $this->Tableplans->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['value', 'func', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Tableplans->entity($this->post());
        $results['list'] = $this->Tableplans->get_by($criteria, FALSE, TRUE, NULL, NULL);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Tableplans->validate()) {
            $this->db->trans_start();
            $entity = $this->Tableplans->entity($this->post());
            $order_by = $this->Tableplans->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Tableplans->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Tableplans->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Tableplans->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Tableplans->getTable(), 'update', $entity['id']);
                    add_activity('Table plan', 'A table plan updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Tableplans->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Tableplans->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Tableplans->getTable(), 'create', $id);
                    add_activity('Table plan', 'A table plan created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Tableplans->entity($this->put());
            $order_by = $this->Tableplans->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Tableplans->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Tableplans->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Tableplans->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Tableplans->getTable(), 'update', $id);
                add_activity('Table plan', 'A table plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Tableplans->entity($this->patch());
            $order_by = $this->Tableplans->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Tableplans->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Tableplans->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Tableplans->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Tableplans->getTable(), 'update', $id);
                add_activity('Table plan', 'A table plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Tableplans->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Tableplans->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Tableplans->defaultBy()]) {
                $this->Tableplans->unset_default(NULL);
                $results['status'] = $this->Tableplans->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Tableplans->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Tableplans->getTable(), 'update', $id);
                add_activity('Table plan', 'A table plan updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Tableplans->save([$this->Tableplans->deletedAt() => FALSE, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) {
                add_sync($this->Tableplans->getTable(), 'update', $id);
                add_activity('Table plan', 'A table plan restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Tableplans->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Tableplans->getTable(), 'trash', $id);
                add_activity('Table plan', 'A table plan deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
