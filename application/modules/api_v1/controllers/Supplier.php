<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Supplier extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Suppliers');
            $this->load->model('Supplieragents');
            $this->load->model('Payments');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Suppliers->get($id, TRUE, $deleted);
            if ($results['entity']) {
                $results['entity']->name = json_decode($results['entity']->name);
                $results['entity']->address = json_decode($results['entity']->address);
            }
        } else {
            $results['list'] = $this->Suppliers->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = json_decode($obj->name);
                $results['list'][$index]->address = json_decode($obj->address);
                if ($id == $obj->id) {
                    $results['entity'] =  $results['list'][$index];
                }
            }
            $results['total'] = $this->Suppliers->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Suppliers->validate()) {
            $this->db->trans_start();
            $entity = $this->Suppliers->entity($this->post());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            if (array_key_exists('address_en', $entity) && array_key_exists('address_bn', $entity)) {
                $entity['address'] = json_encode(array('en' => $entity['address_en'], 'bn' => $entity['address_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Suppliers->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Suppliers->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Suppliers->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Suppliers->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Suppliers->getTable(), 'update', $entity['id']);
                    add_activity('Supplier', 'A supplier updated');
                }
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Suppliers->get_max($order_by) + 1;
                }
                $results['status'] = $id = $this->Suppliers->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Suppliers->getTable(), 'create', $id);
                    add_activity('Supplier', 'A new supplier created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('customer')) : sprintf(lang('saved_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Suppliers->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            if (array_key_exists('address_en', $entity) && array_key_exists('address_bn', $entity)) {
                $entity['address'] = json_encode(array('en' => $entity['address_en'], 'bn' => $entity['address_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Suppliers->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Suppliers->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Suppliers->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Suppliers->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Suppliers->getTable(), 'update', $id);
                add_activity('Supplier', 'A supplier updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Suppliers->entity($this->patch());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            if (array_key_exists('address_en', $entity) && array_key_exists('address_bn', $entity)) {
                $entity['address'] = json_encode(array('en' => $entity['address_en'], 'bn' => $entity['address_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Suppliers->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Suppliers->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Suppliers->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Suppliers->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Suppliers->getTable(), 'update', $id);
                add_activity('Supplier', 'A supplier updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Suppliers->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Suppliers->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Suppliers->defaultBy()]) {
                $this->Suppliers->unset_default(NULL);
                $results['status'] = $this->Suppliers->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Suppliers->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Suppliers->getTable(), 'delete', $id);
                add_activity('Supplier', 'A supplier deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer')) : sprintf(lang('delete_failed_msg'), lang('customer'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Suppliers->save([$this->Suppliers->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Suppliers->getTable(), 'update', $id);
                add_activity('Supplier', 'A supplier restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer')) : sprintf(lang('update_failed_msg'), lang('customer'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Suppliers->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Suppliers->getTable(), 'trash', $id);
                add_activity('Supplier', 'A supplier deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer')) : sprintf(lang('delete_failed_msg'), lang('customer'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }

    public function owed_get($id = NULL)
    {
        $results['list'] = $this->Supplieragents->get_owed_supplieragents(getallheaders()['hub_id']);
        $results['total_due'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = $this->Suppliers->decode($obj->name);
            $results['total_due'] += $obj->due;
            if ($id == $obj->id) $results['entity'] = $results['list'][$index];
        }
        $results['payments'] = $this->Payments->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name', 'value']);
        foreach ($results['payments'] as $index => $obj) {
            $results['payments'][$index]->name = $this->Payments->decode($obj->name);
        }
        return $this->response($results);
    }
}
