<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Geounion extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Geoupazillas');
            $this->load->model('Geounions');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        $results['base'] = $base ? $this->Geoupazillas->get($base, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Geounions->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = json_decode($results['entity']->name);
        } else {
            $results['list'] = !empty($base) ? $this->Geounions->get_by(['upazilla_id' => $base], FALSE, $deleted, $limit, $offset) : $this->Geounions->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = json_decode($obj->name);
                if ($id == $obj->id) {
                    $results['entity'] =  $results['list'][$index];
                }
            }
            $results['total'] = !empty($base) ? $this->Geounions->count_rows(['upazilla_id' => $base], $deleted) : $this->Geounions->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }

    public function search_post()
    {
        $results['criteria'] = $this->post();
        $base = !empty($this->post('base')) ? trim($this->post('base')) : NULL;
        $results['base'] = !empty($base) ? $this->Geoupazillas->get($base, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        $results['list'] = !empty($base) ? $this->Geounions->get_by(['upazilla_id' => $base], FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['upazilla_id', 'upazilla_id', 'base', 'status']) : $this->Geounions->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')),  ['upazilla_id', 'upazilla_id', 'base', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Geounions->entity($this->post());
        $results['list'] = $this->Geounions->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Geounions->validate()) {
            $this->db->trans_start();
            $entity = $this->Geounions->entity($this->post());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucfirst($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geounions->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Geounions->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Geounions->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Geounions->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Geounions->getTable(), 'update',  $entity['id']);
                    add_activity('union', 'A union data updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Geounions->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Geounions->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Geounions->getTable(), 'create',  $id);
                    add_activity('union', 'A new union added');
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
            $entity = $this->Geounions->entity($this->put());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucfirst($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geounions->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Geounions->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Geounions->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Geounions->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Geounions->getTable(), 'update',  $id);
                add_activity('union', 'A union data updated');
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
            $entity = $this->Geounions->entity($this->patch());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucfirst($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geounions->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Geounions->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Geounions->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Geounions->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Geounions->getTable(), 'update',  $id);
                add_activity('union', 'A union data updated');
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
        if (!empty($id) && !empty($this->Geounions->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Geounions->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Geounions->defaultBy()]) {
                $this->Geounions->unset_default(['upazilla_id' => $obj['upazilla_id']]);
                $results['status'] = $this->Geounions->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Geounions->getTable(), 'update',  $id);
                    add_activity('union', 'Default union changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('union')) : sprintf(lang('update_failed_msg'), lang('union'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }


    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Geounions->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Geounions->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Geounions->getTable(), 'delete',  $id);
                    add_activity('union', 'A union deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('union')) : sprintf(lang('delete_failed_msg'), lang('union'));
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
            $results['status'] =  $this->Geounions->save([$this->Geounions->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Geounions->getTable(), 'update',  $id);
                add_activity('union', 'A union restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('union')) : sprintf(lang('update_failed_msg'), lang('union'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Geounions->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Geounions->getTable(), 'trash',  $id);
                add_activity('union', 'A union deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('union')) : sprintf(lang('delete_failed_msg'), lang('union'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
