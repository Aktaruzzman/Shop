<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodtop extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodtops');
            $this->load->model('Cfgunits');
            $this->load->model('Suppliers');

            $this->load->model('Stockdefines');
            $this->load->model('Stockentries');
            $this->load->model('Stockhubentries');
            $this->load->model('Stockhubprices');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $deleted = null, $limit = null, $offset = null, $status = null, $hub_id = null)
    {
        if (!empty($config)) {
            $results = $this->build_config();
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodtops->get($id, true, $deleted);
            $results['entity'] = $this->Prodtops->build_entity($entity, $hub_id);
        } else {
            if (!empty($status)) {
                $results['list'] = $this->Prodtops->get_by(['status' => 'active'], false, $deleted, $limit, $offset);
                $results['rows'] = $this->Prodtops->count_rows(['status' => 'active'], $deleted);
            } else {
                $results['list'] = $this->Prodtops->get_by(null, false, $deleted, $limit, $offset);
                $results['rows'] = $this->Prodtops->count_rows(null, $deleted);
            }
            foreach ($results['list'] as $index => $obj) {
                $obj->edit = !empty($edit) ||  !empty($config);
                $results['list'][$index] = $this->Prodtops->build_entity($obj, $hub_id);
                if ($id == $obj->id) {
                    $results['entity'] = $results['list'][$index];
                }
            }
        }
        return $this->response($results);
    }

    private function build_config()
    {
        $results['suppliers'] = $this->Suppliers->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name']);
        foreach ($results['suppliers'] as $index => $obj) {
            $results['suppliers'][$index]->name = json_decode($obj->name);
        }
        $results['units'] = $this->Cfgunits->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'short', 'default']);
        foreach ($results['units'] as $index => $obj) {
            $results['units'][$index]->name = json_decode($obj->name);
            $results['units'][$index]->short = json_decode($obj->short);
        }
        return $results;
    }

    public function index_post()
    {
        if ($this->Prodtops->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodtops->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $entity['in_price'] = !empty($entity['in_price']) ? $entity['in_price'] : $entity['base_price'];
            $entity['out_price'] = !empty($entity['out_price']) ? $entity['out_price'] : $entity['base_price'];
            $order_by = $this->Prodtops->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodtops->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Prodtops->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodtops->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_activity('Topping', 'A topping updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Prodtops->get_max($order_by) + 1;
                }
                $results['status'] = $results['id'] = $id = $this->Prodtops->save($entity);
                $results['defines'] = $this->Prodtops->define_stock($id);
                if (!empty($results['status'])) {
                    add_activity('Topping', 'A new topping created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('saved_failed_msg'), lang('product') . ' ' . lang('topping'));
            if (!empty($results['defines']) && !empty($results['id'])) {
                $this->Prodtops->save($results['defines'], $results['id']);
            }

            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }

    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodtops->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }

            $order_by = $this->Prodtops->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodtops->get($id, true)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodtops->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodtops->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodtops->getTable(), 'update', $id);
                add_activity('Topping', 'A topping updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('topping'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodtops->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodtops->getTable(), 'update', $id);
                add_activity('Topping', 'A topping updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('topping'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Prodtops->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodtops->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Prodtops->defaultBy()]) {
                $this->Prodtops->unset_default(null);
                $results['status'] = $this->Prodtops->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('topping'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodtops->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Prodtops->getTable(), 'delete', $id);
                add_activity('Topping', 'A topping deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('topping'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodtops->save([$this->Prodtops->deletedAt() => false], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodtops->getTable(), 'update', $id);
                add_activity('Topping', 'A topping restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('topping'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodtops->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodtops->getTable(), 'delete', $id);
                add_activity('Topping', 'A topping deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('topping')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('topping'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }

    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Prodtops->get(null, false, true);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name);
            $entity['name_en'] = $name->en;
            $entity['name_bn'] = $name->bn;
            $this->Prodtops->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
