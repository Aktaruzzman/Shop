<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Customer extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customers');
            $this->load->model('Storecustomers');
            $this->load->model('Customeraddresses');
            $this->load->model('Geodistricts');
            $this->load->model('Geoupazillas');
            $this->load->model('Geoareas');
            $this->load->model('Payments');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $search = NULL, $address = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Customers->get($id, TRUE, $deleted);
            $results['entity'] = $this->Customers->build_entity($entity, $address);
        } else {
            if (!empty($search)) {
                $search = str_replace(array('-', '_', ',', '+'), array(' ', ' ', ' ', ' '),  filter_var($search, FILTER_SANITIZE_SPECIAL_CHARS));
                $results['list'] = $this->Customers->get_by(NULL, FALSE, TRUE, NULL, NULL,  $search, ['name_en', 'name_bn', 'phone']);
                $results['rows'] = count($results['list']);
            } else {
                $results['list'] = $this->Customers->get_by(NULL, FALSE, $deleted, $limit, $offset);
                $results['rows'] = $this->Customers->count_rows(NULL, $deleted);
            }
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Customers->build_entity($obj, $address);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['just_areas'] = FALSE;
            if (empty($results['list']) && !empty($search) && !empty($address)) {
                $results['list'] = $this->Geoareas->get_by(NULL, FALSE, TRUE, NULL, NULL,  $search, ['name_en', 'name_bn']);
                $results['rows'] = count($results['list']);
                $results['just_areas'] = TRUE;
                foreach ($results['list'] as $index => $obj) {
                    $results['list'][$index] = $this->Geoareas->formatted($obj->id);
                }
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Customers->validate()) {
            $this->db->trans_start();
            $entity = $this->Customers->arrange_entity($this->Customers->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Customers->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Customers->getTable(), 'update',  $entity['id']);
                    add_activity('Customer', 'A customer updated');
                }
            } else {
                $results['status'] = $id = $this->Customers->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Customers->getTable(), 'create',  $id);
                    add_activity('Customer', 'A new customer added');
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
            $entity = $this->Customers->entity($this->put());
            $results['status'] = $this->Customers->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Customers->getTable(), 'update',  $id);
                add_activity('Customer', 'A customer updated');
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
            $results['status'] = $this->Customers->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Customers->getTable(), 'update',  $id);
                add_activity('Customer', 'A customer updated');
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
        if (!empty($id) && !empty($this->Customers->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Customers->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Customers->defaultBy()]) {
                $this->Customers->unset_default(NULL);
                $results['status'] = $this->Customers->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Customers->getTable(), 'update',  $id);
                    add_activity('Customer', 'A customer updated');
                }
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
            $obj = $this->Customers->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Customers->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Customers->getTable(), 'delete',  $id);
                    add_activity('customer', 'A customer deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer')) : sprintf(lang('delete_failed_msg'), lang('customer'));
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
            $results['status'] = $this->Customers->save([$this->Customers->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Customers->getTable(), 'update',  $id);
                add_activity('customer', 'A customer restored from bin');
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
            $results['status'] = $this->Customers->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Customers->getTable(), 'delete',  $id);
                add_activity('customer', 'A customer deleted permanently');
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
        $results['list'] = $this->Customers->get_by(['due >' => 0], FALSE, TRUE, NULL, NULL, NULL, ['id', 'name', 'phone', 'due'], 'due', 'DESC');
        $results['total_due'] = 0;
        $results['company_total_due'] = 0;
        $is_multi_store = config_item('store_type') === 'multiple' ? true : false;
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = $this->Customers->decode($obj->name);
            $results['list'][$index]->due_in_company = $obj->due;
            $results['company_total_due'] += $obj->due;
            if ($is_multi_store) {
                $customer = $this->Storecustomers->get(['due >' => 0, 'cust_id' => $obj->id, 'hub_id' => getallheaders()['hub_id']], true, true);
                if (!empty($customer)) {
                    $results['list'][$index]->due = $customer->due;
                    $results['total_due'] += $customer->due;
                } else unset($results['list'][$index]);
            } else $results['total_due'] += $obj->due;
            if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
        }
        $results['store_total_due'] = $results['total_due'];
        $results['rows'] = count($results['list']);
        $results['payments'] = $this->Payments->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name', 'value']);
        foreach ($results['payments'] as $index => $obj) {
            $results['payments'][$index]->name = $this->Payments->decode($obj->name);
        }
        return $this->response($results);
    }
}
