<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Payment extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Payments');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Payments->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = json_decode($results['entity']->name);
        } else {
            $results['list'] = $this->Payments->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = json_decode($obj->name);
                if ($id == $obj->id) {
                    $results['entity'] =  $results['list'][$index];
                }
            }
            $results['total'] = $this->Payments->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $results['list'] = $this->Payments->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name', 'value', 'status']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Payments->entity($this->post());
        $results['list'] = $this->Payments->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Payments->validate()) {
            $this->db->trans_start();
            $entity = $this->Payments->entity($this->post());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Payments->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Payments->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Payments->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Payments->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Payments->getTable(), 'update',  $entity['id']);
                    add_activity('Payment Type', 'A payment type updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Payments->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Payments->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Payments->getTable(), 'create',  $id);
                    add_activity('Payment Type', 'A payment type created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('saved_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Payments->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['value'] = ucwords($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Payments->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Payments->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Payments->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Payments->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Payments->getTable(), 'update',  $id);
                add_activity('Payment Type', 'A payment type updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('update_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Payments->entity($this->patch());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['value'] = ucwords($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Payments->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Payments->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Payments->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Payments->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Payments->getTable(), 'update',  $id);
                add_activity('Payment Type', 'A payment type updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('update_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Payments->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Payments->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Payments->defaultBy()]) {
                $this->Payments->unset_default(NULL);
                $results['status'] = $this->Payments->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Payments->getTable(), 'update',  $id);
                    add_activity('Payment Type', 'Default payment type changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('update_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Payments->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Payments->getTable(), 'delete',  $id);
                add_activity('Payment Type', 'A payment type deleted');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('delete_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Payments->save([$this->Payments->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Payments->getTable(), 'update',  $id);
                add_activity('Payment Type', 'A payment type restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('update_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Payments->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Payments->getTable(), 'trash',  $id);
                add_activity('Payment Type', 'A payment type deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('payment') . ' ' . lang('type')) : sprintf(lang('delete_failed_msg'), lang('payment') . ' ' . lang('type'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Payments->get(NULL, FALSE, TRUE);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name, TRUE);
            $desription = json_decode($a->description, TRUE);
            $entity['name_en'] = $name['en'];
            $entity['name_bn'] = $name['bn'];
            $entity['description_en'] = $desription['en'];
            $entity['description_bn'] = $desription['bn'];
            $this->Payments->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
