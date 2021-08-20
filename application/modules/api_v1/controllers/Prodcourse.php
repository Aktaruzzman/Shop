<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodcourse extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcourses');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodcourses->get($id, TRUE, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = $this->Prodcourses->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Prodcourses->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $results['list'] = $this->Prodcourses->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name', 'value', 'status']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Prodcourses->entity($this->post());
        $results['list'] = $this->Prodcourses->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Prodcourses->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodcourses->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Prodcourses->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodcourses->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Prodcourses->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodcourses->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcourses->getTable(), 'update', $entity['id']);
                    add_activity('Product Course', 'A Product Course updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Prodcourses->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Prodcourses->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcourses->getTable(), 'create',  $id);
                    add_activity('Product Course', 'A new product course created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('saved_failed_msg'), lang('product') . ' ' . lang('course'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodcourses->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Prodcourses->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodcourses->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodcourses->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodcourses->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcourses->getTable(), 'update',  $id);
                add_activity('Product Course', 'A product course updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('course'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodcourses->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcourses->getTable(), 'update',  $id);
                add_activity('Product Course', 'A product course updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('course'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Prodcourses->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodcourses->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Prodcourses->defaultBy()]) {
                $this->Prodcourses->unset_default(NULL);
                $results['status'] = $this->Prodcourses->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcourses->getTable(), 'update',  $id);
                    add_activity('Product Course', 'Default product course changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('course'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Prodcourses->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Prodcourses->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcourses->getTable(), 'delete',  $id);
                    add_activity('Product Course', 'A product course deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('course'));
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
            $results['status'] = $this->Prodcourses->save([$this->Prodcourses->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcourses->getTable(), 'update',  $id);
                add_activity('Product Course', 'A product course restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('course'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodcourses->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Prodcourses->getTable(), 'trash',  $id);
                add_activity('Product Course', 'A product course deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('course')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('course'));
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
        $all = $this->Prodcourses->get(NULL, FALSE, TRUE);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name, TRUE);
            $entity['slug'] = $this->Prodcourses->slug($name['en']);
            $entity['name_en'] = $name['en'];
            $entity['name_bn'] = $name['bn'];
            $this->Prodcourses->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
