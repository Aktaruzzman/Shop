<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodfreeitem extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodfreeplans');
            $this->load->model('Prodfreeitems');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Prodtops');
            $this->load->model('Prodmods');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $base = null, $deleted = null, $limit = null, $offset = null, $category = null)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodfreeitems->get($id, true, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = !empty($base) ? $this->Prodfreeitems->get_by(['plan_id' => $base], false, $deleted, $limit, $offset) : $this->Prodfreeitems->get_by(null, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id) {
                    $results['entity'] = $results['list'][$index];
                }

            }
            $results['total'] = !empty($base) ? $this->Prodfreeitems->count_rows(['plan_id' => $base], $deleted) : $this->Prodfreeitems->count_rows(null, $deleted);
        }
        if (!empty($config)) {
            if (!empty($base)) {
                $results['base'] = $base ? $this->Prodfreeplans->get($base, true, false, ['id', 'name', 'name_en', 'name_bn']) : null;
                if ($results['base']) {
                    $results['base']->name = json_decode($results['base']->name);
                }

            }
            $results = $this->build_config($results, $category);
        }
        return $this->response($results);
    }
    private function build_entity($entity)
    {
        if (!empty($entity)) {
            $item = null;
            $option = null;
            if (!empty($entity->modify_id)) {
                $item = $this->Prodmods->get($entity->modify_id, true, true);
                $entity->photos['full'] = !empty($item->photo) ? site_url('/uploads/product/item/' . $item->photo) : './img/no-image.png';
                $entity->photos['thumb'] = !empty($item->thumb) ? site_url('/uploads/product/item/thumbnail/' . $item->thumb) : './img/no-image.png';
            }
            if (!empty($entity->topping_id)) {
                $item = $this->Prodtops->get($entity->topping_id, true, true);
                $entity->photos['full'] = !empty($item->photo) ? site_url('/uploads/product/item/' . $item->photo) : './img/no-image.png';
                $entity->photos['thumb'] = !empty($item->thumb) ? site_url('/uploads/product/item/thumbnail/' . $item->thumb) : './img/no-image.png';
            }
            if (!empty($entity->option_id)) {
                $option = $this->Prodoptions->get($entity->option_id, true, true);
                $entity->photos['full'] = !empty($option->photo) ? site_url('/uploads/product/item/' . $option->photo) : './img/no-image.png';
                $entity->photos['thumb'] = !empty($option->thumb) ? site_url('/uploads/product/item/thumbnail/' . $option->thumb) : './img/no-image.png';
            }
            if (!empty($entity->item_id)) {
                $item = $this->Proditems->get($entity->item_id, true, true);
                $entity->photos['full'] = !empty($item->photo) ? site_url('/uploads/product/item/' . $item->photo) : './img/no-image.png';
                $entity->photos['thumb'] = !empty($item->thumb) ? site_url('/uploads/product/item/thumbnail/' . $item->thumb) : './img/no-image.png';
            }
            if (!empty($option)) {
                $entity->name = array('en' => $option->name_en . ' ' . $item->name_en, 'bn' => $option->name_bn . ' ' . $item->name_bn);
            } else {
                $entity->name = array('en' => $item->name_en, 'bn' => $item->name_bn);
            }

        }
        return $entity;
    }
    private function build_config($results, $category)
    {
        $results['categories'] = $this->Prodcats->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name', 'name_en', 'name_bn', 'default']);
        foreach ($results['categories'] as $index => $obj) {
            $results['categories'][$index]->name = json_decode($obj->name);
            if (intval($category) == $obj->id) {
                $results['item_category'] = $results['categories'][$index];
            }

        }
        $results['items'] = $this->Proditems->get_by(['status' => 'active', 'category_id' => $category], false, false, null, null, null, ['id', 'name', 'name_en', 'name_bn', 'category_id']);
        foreach ($results['items'] as $index => $obj) {
            $results['items'][$index]->name = json_decode($obj->name);
            $results['items'][$index]->options = $this->Prodoptions->get_by(['status' => 'active', 'item_id' => $obj->id], false, false, null, null, null, ['id', 'name', 'name_en', 'name_bn', 'category_id']);
            foreach ($results['items'][$index]->options as $i => $option) {
                $results['items'][$index]->options[$i]->name = json_decode($option->name);
            }
        }
        return $results;
    }

    public function search_post()
    {
        $results['criteria'] = $this->post();
        $base = !empty($this->post('base')) ? trim($this->post('base')) : null;
        $results['base'] = !empty($base) ? $this->Prodfreeplans->get($base, true) : null;
        if ($results['base']) {
            $results['base']->name = json_decode($results['base']->name);
        }

        $results['list'] = !empty($base) ? $this->Prodfreeitems->get_by(['plan_id' => $base], false, true, null, null, trim($this->post('search'))) : $this->Prodfreeitems->get_by(null, false, true, null, null, trim($this->post('search')), ['upazilla_id', 'plan_id', 'base', 'status']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total'] = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Prodfreeitems->entity($this->post());
        $results['list'] = $this->Prodfreeitems->get_by($criteria, false, true, null, null);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total'] = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Prodfreeitems->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodfreeitems->entity($this->post());
            $order_by = $this->Prodfreeitems->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodfreeitems->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Prodfreeitems->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodfreeitems->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodfreeitems->getTable(), 'update', $entity['id']);
                    add_activity('Free item offer', 'A free item updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Prodfreeitems->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Prodfreeitems->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodfreeitems->getTable(), 'create', $id);
                    add_activity('Free item offer', 'A new free item added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('item')) : sprintf(lang('saved_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodfreeitems->entity($this->put());
            $order_by = $this->Prodfreeitems->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodfreeitems->get($id, true)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodfreeitems->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodfreeitems->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfreeitems->getTable(), 'update', $id);
                add_activity('Free item offer', 'A free item updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodfreeitems->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfreeitems->getTable(), 'update', $id);
                add_activity('Free item offer', 'A free item updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Prodfreeitems->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodfreeitems->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Prodfreeitems->defaultBy()]) {
                $this->Prodfreeitems->unset_default(null);
                $results['status'] = $this->Prodfreeitems->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Prodfreeitems->getTable(), 'update', $id);
                    add_activity('Free item offer', 'Default free item changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $obj = $this->Prodfreeitems->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $results['status'] = $this->Prodfreeitems->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Prodfreeitems->getTable(), 'delete', $id);
                    add_activity('Free item offer', 'A free item deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('item')) : sprintf(lang('delete_failed_msg'), lang('option'));
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
            $results['status'] = $this->Prodfreeitems->save([$this->Prodfreeitems->deletedAt() => false, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodfreeitems->getTable(), 'update', $id);
                add_activity('Free item offer', 'A free item restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodfreeitems->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodfreeitems->getTable(), 'trash', $id);
                add_activity('Free item offer', 'A free item deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('item')) : sprintf(lang('delete_failed_msg'), lang('option'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
