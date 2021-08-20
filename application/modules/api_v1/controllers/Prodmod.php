<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodmod extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodmods');
        }
    }
    public function index_get($id = null, $edit = null, $deleted = null, $limit = null, $offset = null, $status = null)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodmods->get($id, true, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            if (!empty($status)) {
                $results['list'] = $this->Prodmods->get_by(['status' => 'active'], false, $deleted, $limit, $offset);
                $results['rows'] = $this->Prodmods->count_rows(['status' => 'active'], $deleted);
            } else {
                $results['list'] = $this->Prodmods->get_by(null, false, $deleted, $limit, $offset);
                $results['rows'] = $this->Prodmods->count_rows(null, $deleted);
            }
            foreach ($results['list'] as $index => $obj) {
                $obj->edit = !empty($edit);
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id) {
                    $results['entity'] = $results['list'][$index];
                }
            }
        }
        return $this->response($results);
    }
    private function build_entity($entity)
    {
        $entity->name = json_decode($entity->name);
        $entity->photos['full'] = !empty($entity->photo) ? site_url('/uploads/product/item/' . $entity->photo) : './img/no-image.png';
        $entity->photos['thumb'] = !empty($entity->thumb) ? site_url('/uploads/product/item/thumbnail/' . $entity->thumb) : './img/no-image.png';
        return $entity;
    }
    public function index_post()
    {
        if ($this->Prodmods->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodmods->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $entity['in_price'] = $entity['out_price'] = $entity['base_price'];
            $order_by = $this->Prodmods->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodmods->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Prodmods->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodmods->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodmods->getTable(), 'update', $entity['id']);
                    add_activity('Special Modify', 'A product special modify updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Prodmods->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Prodmods->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodmods->getTable(), 'create', $id);
                    add_activity('Special Modify', 'A new product special modify created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('saved_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodmods->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }

            $order_by = $this->Prodmods->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodmods->get($id, true)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodmods->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodmods->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodmods->getTable(), 'update', $id);
                add_activity('Special Modify', 'A product special modify updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodmods->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodmods->getTable(), 'update', $id);
                add_activity('Special Modify', 'A product special modify updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Prodmods->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodmods->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Prodmods->defaultBy()]) {
                $this->Prodmods->unset_default(null);
                $results['status'] = $this->Prodmods->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodmods->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Prodmods->getTable(), 'delete', $id);
                add_activity('Special Modify', 'A product special modify deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodmods->save([$this->Prodmods->deletedAt() => false], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodmods->getTable(), 'update', $id);
                add_activity('Special Modify', 'A product special modify restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodmods->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodmods->getTable(), 'trash', $id);
                add_activity('Special Modify', 'A product special modify deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('modify')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('modify'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }

    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Prodmods->get(null, false, true);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name);
            $entity['name_en'] = $name->en;
            $entity['name_bn'] = $name->bn;
            $this->Prodmods->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
