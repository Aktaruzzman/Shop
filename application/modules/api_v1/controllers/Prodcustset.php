<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodcustset extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcustsets');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $hook_item_id = null, $hook_option_id = null, $deleted = null, $limit = null, $offset = 0)
    {
        if (!empty($config)) {
            $results = $this->build_config($hook_item_id, $hook_option_id);
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodcustsets->get($id, true, true);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = !empty($hook_option_id) ? $this->Prodcustsets->get_by(['hook_item_id' => $hook_item_id, 'hook_option_id' => $hook_option_id], false, $deleted, $limit, $offset) : $this->Prodcustsets->get_by(['hook_item_id' => $hook_item_id], false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if (intval($id) === intval($obj->id)) {
                    $results['entity'] = $results['list'][$index];
                }
            }
        }
        return $this->response($results);
    }
    private function build_entity($entity)
    {
        if (!empty($entity)) {
            $entity->name = json_decode($entity->name);
        }
        return $entity;
    }
    private function build_config($hook_item_id, $hook_option_id)
    {
        $results['hook_item'] = $this->build_entity($this->Proditems->get($hook_item_id, true, true, ['id', 'name', 'base_id', 'category_id', 'unit_id']));
        $results['hook_category'] = $this->build_entity($this->Prodcats->get($results['hook_item']->category_id, true, true, ['id', 'base_id', 'name']));
        if (!empty($hook_option_id)) {
            $results['hook_option'] = $this->build_entity($this->Prodoptions->get($hook_option_id, true, true, ['id', 'name', 'base_id', 'category_id', 'item_id', 'unit_id']));
        }
        return $results;
    }
    public function index_post()
    {
        if ($this->Prodcustsets->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodcustsets->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Prodcustsets->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodcustsets->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Prodcustsets->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodcustsets->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcustsets->getTable(), 'update', $entity['id']);
                    add_activity('Product Custom Set', 'A Product custom set updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Prodcustsets->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Prodcustsets->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcustsets->getTable(), 'create', $id);
                    add_activity('Product Custom Set', 'A Product custom set created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodcustsets->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Prodcustsets->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodcustsets->get($id, true)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodcustsets->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodcustsets->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsets->getTable(), 'update', $id);
                add_activity('Product Custom Set', 'A Product custom set updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodcustsets->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsets->getTable(), 'update', $id);
                add_activity('Product Custom Set', 'A Product custom set updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Prodcustsets->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodcustsets->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Prodcustsets->defaultBy()]) {
                $this->Prodcustsets->unset_default(null);
                $results['status'] = $this->Prodcustsets->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcustsets->getTable(), 'update', $id);
                    add_activity('Product Custom Set', 'Default Product custom set updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $obj = $this->Prodcustsets->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Prodcustsets->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcustsets->getTable(), 'delete', $id);
                    add_activity('Product Custom Set', 'A Product custom set updated');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
                return $this->response($results);
            } else {
                return $this->response(['status' => false, 'message' => 'invalid request']);
            }
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodcustsets->save([$this->Prodcustsets->deletedAt() => false, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsets->getTable(), 'update', $id);
                add_activity('Product Custom Set', 'A Product custom set restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodcustsets->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsets->getTable(), 'trash', $id);
                add_activity('Product Custom Set', 'A Product custom set deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Prodcustsets->get(null, false, true);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name, true);
            $entity['name_en'] = $name['en'];
            $entity['name_bn'] = $name['bn'];
            $this->Prodcustsets->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
