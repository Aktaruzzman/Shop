<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodfixedsetoption extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodfixedsetoptions');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
        }
    }

    public function index_get($id = null, $config = null, $edit = null, $hook_item_id = null, $hook_option_id = null, $deleted = null, $source = 'item', $source_id = null, $status = null, $hub_id = null)
    {
        if (!empty($config)) {
            $results = $this->Prodfixedsetoptions->build_config($source, $source_id);
            $results['hook_item'] = $this->Proditems->get($hook_item_id, true, true, ['id', 'name', 'base_id', 'category_id', 'unit_id', 'stock_id', 'base_price', 'in_price', 'out_price']);
            $results['hook_item']->name = $this->Prodfixedsetoptions->decode($results['hook_item']->name);
            $results['hook_category'] = $this->Prodcats->get($results['hook_item']->category_id, true, true, ['id', 'base_id', 'name']);
            $results['hook_category']->name = $this->Prodfixedsetoptions->decode($results['hook_category']->name, true);
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodfixedsetoptions->get($id, true, true);
            $results['entity'] = $this->Prodfixedsetoptions->build_entity($entity, $hub_id);
        } else {
            $conditions = ['hook_item_id' => $hook_item_id];
            if (!empty($hook_option_id))  $conditions['hook_option_id'] = $hook_option_id;
            if (!empty($status)) $conditions['status'] = 'active';
            if (!empty($hook_option_id)) {
                $results['hook_option'] = $this->Prodoptions->get($hook_option_id, true, true, ['id', 'name', 'base_id', 'category_id', 'item_id', 'unit_id', 'stock_id', 'base_price', 'in_price', 'out_price']);
                $results['hook_option']->name = $this->Prodfixedsetoptions->decode($results['hook_option']->name);
            }
            $results['list'] = $this->Prodfixedsetoptions->get_by($conditions, false, $deleted);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Prodfixedsetoptions->build_entity($obj, $hub_id);
                if (intval($id) === intval($obj->id)) $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Prodfixedsetoptions->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodfixedsetoptions->entity($this->post());
            $row = null;
            if (!empty($entity['modify_id'])) {
                $row = !empty($entity['hook_option_id']) ? $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'hook_option_id' => $entity['hook_option_id'], 'modify_id' => $entity['modify_id']], true) : $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'modify_id' => $entity['modify_id']], true);
                $entity['qty'] = 0;
            } else if (!empty($entity['topping_id'])) {
                $row = !empty($entity['hook_option_id']) ? $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'hook_option_id' => $entity['hook_option_id'], 'topping_id' => $entity['topping_id']], true) : $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'topping_id' => $entity['topping_id']], true);
                $entity['qty'] = 1;
            } else if (!empty($entity['option_id'])) {
                $row = !empty($entity['hook_option_id']) ? $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'hook_option_id' => $entity['hook_option_id'], 'option_id' => $entity['option_id']], true) : $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'option_id' => $entity['option_id']], true);
                $entity['qty'] = 1;
            } else {
                $row = !empty($entity['hook_option_id']) ? $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'hook_option_id' => $entity['hook_option_id'], 'item_id' => $entity['item_id']], true) : $this->Prodfixedsetoptions->get_by(['hook_item_id' => $entity['hook_item_id'], 'item_id' => $entity['item_id']], true);
                $entity['qty'] = 1;
            }
            if (!empty($row)) {
                $entity['qty'] = $entity['qty'] * 1 + $row->qty;
                $results['status'] = $this->Prodfixedsetoptions->save($entity, $row->id);
                if (!empty($results['status'])) {
                    add_sync($this->Prodfixedsetoptions->getTable(), 'update', $row->id);
                    add_activity('Fixed set option', 'A fixed set option updated');
                }
            } else {
                $results['status'] = $id = $this->Prodfixedsetoptions->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodfixedsetoptions->getTable(), 'create', $id);
                    add_activity('Fixed set option', 'A fixed set option created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('option')) : sprintf(lang('saved_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodfixedsetoptions->entity($this->put());
            $order_by = $this->Prodfixedsetoptions->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodfixedsetoptions->get($id, true)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodfixedsetoptions->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodfixedsetoptions->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfixedsetoptions->getTable(), 'update', $id);
                add_activity('Fixed set option', 'A fixed set option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('option')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodfixedsetoptions->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfixedsetoptions->getTable(), 'update', $id);
                add_activity('Fixed set option', 'A fixed set option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('option')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Prodfixedsetoptions->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodfixedsetoptions->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Prodfixedsetoptions->defaultBy()]) {
                $this->Prodfixedsetoptions->unset_default(null);
                $results['status'] = $this->Prodfixedsetoptions->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('option')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodfixedsetoptions->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfixedsetoptions->getTable(), 'delete', $id);
                add_activity('Fixed set option', 'A fixed set option deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('option')) : sprintf(lang('delete_failed_msg'), lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodfixedsetoptions->save([$this->Prodfixedsetoptions->deletedAt() => false], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfixedsetoptions->getTable(), 'update', $id);
                add_activity('Fixed set option', 'A fixed set option restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('option')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodfixedsetoptions->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodfixedsetoptions->getTable(), 'trash', $id);
                add_activity('Fixed set option', 'A fixed set option deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('option')) : sprintf(lang('delete_failed_msg'), lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
