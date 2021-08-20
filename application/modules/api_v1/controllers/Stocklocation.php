<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Stocklocation extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Stocklocations');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Stocklocations->get($id, TRUE, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = $this->Stocklocations->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Stocklocations->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $results['list'] = $this->Stocklocations->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name', 'value', 'status']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Stocklocations->entity($this->post());
        $results['list'] = $this->Stocklocations->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Stocklocations->validate()) {
            $this->db->trans_start();
            $entity = $this->Stocklocations->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Stocklocations->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Stocklocations->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Stocklocations->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Stocklocations->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Stocklocations->getTable(), 'update', $entity['id']);
                    add_activity('Stock location', 'A stock location updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Stocklocations->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Stocklocations->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Stocklocations->getTable(), 'create', $id);
                    add_activity('Stock location', 'A stock location created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Stocklocations->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity))  $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Stocklocations->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Stocklocations->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Stocklocations->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Stocklocations->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Stocklocations->getTable(), 'update', $id);
                add_activity('Stock location', 'A stock location updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Stocklocations->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Stocklocations->getTable(), 'update', $id);
                add_activity('Stock location', 'A stock location updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Stocklocations->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Stocklocations->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Stocklocations->defaultBy()]) {
                $this->Stocklocations->unset_default(NULL);
                $results['status'] = $this->Stocklocations->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Stocklocations->getTable(), 'update', $id);
                    add_activity('Stock location', 'Default stock location changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Stocklocations->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Stocklocations->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Stocklocations->getTable(), 'delete', $id);
                    add_activity('Stock location', 'A stock location deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
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
            $results['status'] = $this->Stocklocations->save([$this->Stocklocations->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Stocklocations->getTable(), 'update', $id);
                add_activity('Stock location', 'A stock location restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Stocklocations->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Stocklocations->getTable(), 'trash', $id);
                add_activity('Stock location', 'A stock location deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
    private function build_entity($entity)
    {
        $entity->name = json_decode($entity->name);
        return $entity;
    }
    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Stocklocations->get(NULL, FALSE, TRUE);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name);
            $entity['name_en'] = $name->en;
            $entity['name_bn'] = $name->bn;
            $this->Stocklocations->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
