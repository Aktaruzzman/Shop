<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodcustsetoption extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcustsetoptions');
            $this->load->model('Prodcustsets');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Prodtops');
            $this->load->model('Prodmods');
            $this->load->model('Cfgunits');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $base = null, $deleted = null, $source = 'item', $source_id = null)
    {
        if (!empty($config)) {
            $results = $this->build_config($source, $source_id);
            $results['base'] = $base ? $this->Prodcustsets->get($base, true) : null;
            $results['base']->name = json_decode($results['base']->name);
            $results['hook_item'] = $this->Proditems->get($results['base']->hook_item_id, true, true, ['id', 'category_id', 'name', 'base_price', 'in_price', 'out_price', 'stock_id', 'unit_id']);
            $results['hook_item']->name = json_decode($results['hook_item']->name);
            if (!empty($results['base']->hook_option_id)) {
                $results['hook_option'] = $this->Prodoptions->get($results['base']->hook_option_id, true, true, ['id', 'item_id', 'category_id', 'name', 'base_price', 'in_price', 'out_price', 'stock_id', 'unit_id']);
                $results['hook_option']->name = json_decode($results['hook_option']->name);
            }
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodcustsetoptions->get($id, true, true);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = $this->Prodcustsetoptions->get_by(['set_id' => $base], false, $deleted);
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
        $unit = null;
        $row = null;
        if ((!empty($entity->modify_id) || !is_null($entity->modify_id)) && $entity->modify_id > 0) {
            $row = $this->Prodmods->get($entity->modify_id, true);
            $entity->name = json_decode($row->name);
            $unit = null;
        } else if ((!empty($entity->topping_id) || !is_null($entity->topping_id)) && $entity->topping_id > 0) {
            $row = $this->Prodtops->get($entity->topping_id, true, true);
            $entity->name = json_decode($row->name);
            $unit = $this->Cfgunits->get($row->unit_id, true, true, ['id', 'name', 'short']);
        } else if ((!empty($entity->option_id) || !is_null($entity->option_id)) && $entity->option_id > 0) {
            $row = $this->Prodoptions->get($entity->option_id, true, true);
            $row->name = json_decode($row->name);
            $item = $this->Proditems->get($entity->item_id, true);
            $item->name = json_decode($item->name);
            $entity->name = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
            $unit = $this->Cfgunits->get($row->unit_id, true, true, ['id', 'name', 'short']);
        } else {
            $row = $this->Proditems->get($entity->item_id, true, true);
            $entity->item = $row;
            $entity->name = json_decode($row->name);
            $unit = $this->Cfgunits->get($row->unit_id, true, true, ['id', 'name', 'short']);
        }
        if (!empty($unit)) {
            $unit->name = json_decode($unit->name);
            $unit->short = json_decode($unit->short);
            $entity->unit = $unit;
        }
        $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
        $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
        return $entity;
    }
    private function build_config($source, $source_id)
    {
        $results['categories'] = $this->Prodcats->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name']);
        foreach ($results['categories'] as $index => $obj) {
            $results['categories'][$index]->name = json_decode($obj->name);
            if (intval($source_id) === $obj->id) {
                $results['item_category'] = $results['categories'][$index];
            }
        }
        if ($source === 'item' && !empty($source_id)) {
            $results['items'] = $this->Proditems->get_by(['status' => 'active', 'category_id' => $source_id], false, false, null, null, null, ['id', 'name', 'category_id', 'stock_id', 'unit_id']);
            foreach ($results['items'] as $index => $obj) {
                $results['items'][$index]->name = json_decode($obj->name);
                $results['items'][$index]->options = $this->Prodoptions->get_by(['status' => 'active', 'item_id' => $obj->id], false, false, null, null, null, ['id', 'name', 'item_id', 'category_id', 'stock_id', 'unit_id']);
                foreach ($results['items'][$index]->options as $i => $option) {
                    $results['items'][$index]->options[$i]->name = json_decode($option->name);
                }
            }
        }
        if ($source === 'topping') {
            $results['toppings'] = $this->Prodtops->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'stock_id', 'unit_id']);
            foreach ($results['toppings'] as $index => $top) {
                $results['toppings'][$index]->name = json_decode($top->name);
            }
        }
        if ($source === 'modify') {
            $results['modifys'] = $this->Prodmods->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name']);
            foreach ($results['modifys'] as $index => $mod) {
                $results['modifys'][$index]->name = json_decode($mod->name);
            }
        }
        return $results;
    }


    public function index_post()
    {
        if ($this->Prodcustsetoptions->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodcustsetoptions->entity($this->post());
            $entity['in_price'] = $entity['out_price'] = $entity['base_price'];
            $order_by = $this->Prodcustsetoptions->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodcustsetoptions->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Prodcustsetoptions->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodcustsetoptions->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcustsetoptions->getTable(), 'update', $entity['id']);
                    add_activity('Custom set option', 'A custom set option updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Prodcustsetoptions->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Prodcustsetoptions->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcustsetoptions->getTable(), 'create', $id);
                    add_activity('Custom set option', 'A custom set option created');
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
            $entity = $this->Prodcustsetoptions->entity($this->put());
            $order_by = $this->Prodcustsetoptions->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodcustsetoptions->get($id, true)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodcustsetoptions->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodcustsetoptions->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsetoptions->getTable(), 'update', $id);
                add_activity('Custom set option', 'A custom set option updated');
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
            $results['status'] = $this->Prodcustsetoptions->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsetoptions->getTable(), 'update', $id);
                add_activity('Custom set option', 'A custom set option updated');
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
        if (!empty($id) && !empty($this->Prodcustsetoptions->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodcustsetoptions->get($id, true);
            $results['status'] = true;
            $results['status'] = $obj[$this->Prodcustsetoptions->defaultBy()] ? $this->Prodcustsetoptions->unset_default(['id' => $obj['id']]) : $this->Prodcustsetoptions->set_default(['id' => $obj['id']]);
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
            $results['status'] = $this->Prodcustsetoptions->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsetoptions->getTable(), 'delete', $id);
                add_activity('Custom set option', 'A custom set option deleted into bin');
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
            $results['status'] = $this->Prodcustsetoptions->save([$this->Prodcustsetoptions->deletedAt() => false], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsetoptions->getTable(), 'update', $id);
                add_activity('Custom set option', 'A custom set option restored from bin');
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
            $results['status'] = $this->Prodcustsetoptions->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodcustsetoptions->getTable(), 'trash', $id);
                add_activity('Custom set option', 'A custom set option deleted permanently from bin');
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
