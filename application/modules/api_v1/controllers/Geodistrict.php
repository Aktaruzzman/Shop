<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Geodistrict extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Geodivisions');
            $this->load->model('Geodistricts');
            $this->load->model('Geodivisions');
            $this->load->model('Geounions');
            $this->load->model('Geoareas');
            $this->load->model('Geodefaults');
        }
    }

    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {

        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Geodistricts->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = json_decode($results['entity']->name);
        } else {
            if (!empty($config)) {
                $results['defaults'] = $this->Geodefaults->get_all();
                $results['base'] = !empty($results['defaults']['division']) ? $results['defaults']['division'] : NULL;
                $results['list'] = !empty($results['base']) ? $this->Geodistricts->get_by(['division_id' => $results['base']->id], FALSE, $deleted, $limit, $offset) : [];
                $results['total'] = $this->Geodistricts->count_rows(['division_id' => $results['base']->id], $deleted);
            } else {
                $results['base'] = $base ? $this->Geodivisions->get($base, TRUE, FALSE, ['id', 'name', 'name_en', 'name_bn', 'default', 'status', 'deleted_at']) : NULL;
                if (!empty($results['base'])) $results['base']->name = json_decode($results['base']->name);
                $results['list'] = $results['base'] ? $this->Geodistricts->get_by(['division_id' =>  $results['base']->id], FALSE, $deleted, $limit, $offset) : $this->Geodistricts->get_by(NULL, FALSE, $deleted, $limit, $offset);
                $results['total'] = $this->Geodistricts->count_rows(NULL, $deleted);
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
        $results['base'] = !empty($base) ? $this->Geodivisions->get($base, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        $results['list'] = !empty($base) ? $this->Geodistricts->get_by(['division_id' => $base], FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name_en', 'name_bn', 'status']) : $this->Geodistricts->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')),  ['name_en', 'name_bn', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Geodistricts->entity($this->post());
        $results['list'] = $this->Geodistricts->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Geodistricts->validate()) {
            $this->db->trans_start();
            $entity = $this->Geodistricts->entity($this->post());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geodistricts->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Geodistricts->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Geodistricts->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Geodistricts->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Geodistricts->getTable(), 'update',  $entity['id']);
                    add_activity('district', 'A district data updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Geodistricts->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Geodistricts->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Geodistricts->getTable(), 'create',  $id);
                    add_activity('district', 'A new district data added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('district') . ' ' . lang('info')) : sprintf(lang('saved_failed_msg'), lang('district') . ' ' . lang('info'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Geodistricts->entity($this->put());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['value'] = strtolower($entity['value']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geodistricts->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Geodistricts->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Geodistricts->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Geodistricts->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Geodistricts->getTable(), 'update',  $id);
                add_activity('district', 'A district data updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('district') . ' ' . lang('info')) : sprintf(lang('update_failed_msg'), lang('district') . ' ' . lang('info'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Geodistricts->entity($this->patch());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['value'] = strtolower($entity['value']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geodistricts->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Geodistricts->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Geodistricts->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Geodistricts->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Geodistricts->getTable(), 'update',  $id);
                add_activity('district', 'A district data updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('district') . ' ' . lang('info')) : sprintf(lang('update_failed_msg'), lang('district') . ' ' . lang('info'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Geodistricts->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Geodistricts->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Geodistricts->defaultBy()]) {
                $this->Geodistricts->unset_default(['division_id' => $obj['division_id']]);
                $results['status'] = $this->Geodistricts->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Geodistricts->getTable(), 'update',  $id);
                    add_activity('district', 'default district changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('district')) : sprintf(lang('update_failed_msg'), lang('district'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $divisions = $this->Geodivisions->get_by(['district_id' => $id], FALSE, TRUE);
            foreach ($divisions as $obj) {
                $this->Geodistricts->delete_by(['division_id' => $obj->id]);
                $this->Geounions->delete_by(['division_id' => $obj->id]);
            }
            $this->Geodivisions->delete_by(['district_id' => $id]);
            $results['status'] = $this->Geodistricts->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Geodistricts->getTable(), 'delete',  $id);
                add_activity('district', 'A district deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('district')) : sprintf(lang('delete_failed_msg'), lang('district'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $divisions = $this->Geodivisions->get_by(['district_id' => $id], FALSE, TRUE);
            foreach ($divisions as $obj) {
                $this->Geounions->save_by([$this->Geounions->deletedAt() => FALSE], ['division_id' => $obj->id]);
                $this->Geodistricts->save_by([$this->Geodistricts->deletedAt() => FALSE], ['division_id' => $obj->id]);
            }
            $this->Geodivisions->save_by([$this->Geodivisions->deletedAt() => FALSE], ['district_id' => $id]);
            $this->Geodistricts->save([$this->Geodistricts->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Geodistricts->getTable(), 'update',  $id);
                add_activity('district', 'A district restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('district')) : sprintf(lang('update_failed_msg'), lang('district'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status']=$this->Geodistricts->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Geodistricts->getTable(), 'trash',  $id);
                add_activity('district', 'A district deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('district')) : sprintf(lang('delete_failed_msg'), lang('district'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
