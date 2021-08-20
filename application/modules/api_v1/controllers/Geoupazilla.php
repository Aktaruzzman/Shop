<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Geoupazilla extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Geodivisions');
            $this->load->model('Geodistricts');
            $this->load->model('Geoupazillas');
            $this->load->model('Geounions');
            $this->load->model('Geoupazillas');
            $this->load->model('Geodefaults');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Geoupazillas->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = json_decode($results['entity']->name);
        } else {
            if (!empty($config)) {
                $results['defaults'] = $this->Geodefaults->get_all();
                $results['base'] = !empty($results['defaults']['district']) ? $results['defaults']['district'] : NULL;
                $results['list'] = !empty($results['base']) ? $this->Geoupazillas->get_by(['district_id' => $results['base']->id], FALSE, $deleted, $limit, $offset) : [];
                $results['total'] = $this->Geoupazillas->count_rows(['district_id' => $results['base']->id], $deleted);
            } else {
                $results['base'] = $base ? $this->Geodistricts->get($base, TRUE, FALSE, ['id', 'name', 'name_en', 'name_bn', 'default', 'status']) : NULL;
                if (!empty($results['base'])) $results['base']->name = json_decode($results['base']->name);
                $results['list'] = $results['base'] ? $this->Geoupazillas->get_by(['district_id' =>  $results['base']->id], FALSE, $deleted, $limit, $offset) : $this->Geoupazillas->get_by(NULL, FALSE, $deleted, $limit, $offset);
                $results['total'] = $this->Geoupazillas->count_rows(NULL, $deleted);
            }
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = json_decode($obj->name);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $base = !empty($this->post('base')) ? trim($this->post('base')) : NULL;
        $results['base'] = !empty($base) ? $this->Geodistricts->get($base, TRUE) : NULL;
        $results['list'] = !empty($base) ? $this->Geoupazillas->get_by(['district_id' => $base], FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name_en', 'name_bn', 'status']) : $this->Geoupazillas->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')),  ['name_en', 'name_bn', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Geoupazillas->entity($this->post());
        $results['list'] = $this->Geoupazillas->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Geoupazillas->validate()) {
            $this->db->trans_start();
            $entity = $this->Geoupazillas->entity($this->post());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucwords($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geoupazillas->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Geoupazillas->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Geoupazillas->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Geoupazillas->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Geoupazillas->getTable(), 'update',  $entity['id']);
                    add_activity('upazilla', 'A upazilla updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Geoupazillas->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Geoupazillas->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Geoupazillas->getTable(), 'create',  $id);
                    add_activity('upazilla', 'A new upazilla added');
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
            $entity = $this->Geoupazillas->entity($this->put());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucwords($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geoupazillas->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Geoupazillas->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Geoupazillas->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Geoupazillas->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Geoupazillas->getTable(), 'update',  $id);
                add_activity('upazilla', 'A upazilla updated');
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
            $entity = $this->Geoupazillas->entity($this->patch());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucwords($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geoupazillas->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Geoupazillas->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Geoupazillas->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Geoupazillas->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Geoupazillas->getTable(), 'update',  $id);
                add_activity('upazilla', 'A upazilla updated');
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
        if (!empty($id) && !empty($this->Geoupazillas->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Geoupazillas->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Geoupazillas->defaultBy()]) {
                $this->Geoupazillas->unset_default(['district_id' => $obj['district_id']]);
                $results['status'] = $this->Geoupazillas->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Geoupazillas->getTable(), 'update',  $id);
                    add_activity('upazilla', 'Default upazilla changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('upazilla')) : sprintf(lang('update_failed_msg'), lang('upazilla'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Geoupazillas->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $this->Geoupazillas->delete_by(['upazilla_id' => $id]);
                $this->Geounions->delete_by(['upazilla_id' => $id]);
                $this->Geoupazillas->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Geoupazillas->getTable(), 'delete',  $id);
                    add_activity('upazilla', 'A upazilla deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('upazilla')) : sprintf(lang('delete_failed_msg'), lang('upazilla'));
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
            $this->Geounions->save_by([$this->Geounions->deletedAt() => FALSE], ['upazilla_id' => $id]);
            $this->Geoupazillas->save_by([$this->Geoupazillas->deletedAt() => FALSE], ['upazilla_id' => $id]);
            $results['status'] = $this->Geoupazillas->save([$this->Geoupazillas->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Geoupazillas->getTable(), 'update',  $id);
                add_activity('upazilla', 'A upazilla restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('upazilla')) : sprintf(lang('update_failed_msg'), lang('upazilla'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Geoupazillas->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Geoupazillas->getTable(), 'trash',  $id);
                add_activity('upazilla', 'A upazilla deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('upazilla')) : sprintf(lang('delete_failed_msg'), lang('upazilla'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
