<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Customeraddress extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customeraddresses');
            $this->load->model('Geodivisions');
            $this->load->model('Geodistricts');
            $this->load->model('Geoupazillas');
            $this->load->model('Geoareas');
            $this->load->model('Geodefaults');
            $this->load->model('Customers');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($config)) {
            $results = $this->Customeraddresses->build_config();
            $results['base'] = !empty($base) ? $this->Customers->get($base, TRUE, FALSE, ['id', 'name', 'name_en', 'name_bn']) : NULL;
            if (!empty($results['base'])) $results['base']->name = $this->Customeraddresses->decode($results['base']->name);
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Customeraddresses->get($id, TRUE, $deleted);
            $results['entity'] = $this->Customeraddresses->build_entity($entity);
            if ($results['entity']) $results['entity']->name = $this->Customeraddresses->decode($results['entity']->name);
        } else {
            $results['list'] = !empty($base) ? $this->Customeraddresses->get_by(['cust_id' => $base], FALSE, $deleted, $limit, $offset) : [];
            foreach ($results['list'] as $index => $obj) {
                $obj->customer = $this->Customers->get($obj->cust_id);
                $results['list'][$index] = $this->Customeraddresses->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        return $this->response($results);
    }



    public function index_post()
    {
        if ($this->Customeraddresses->validate()) {
            $this->db->trans_start();
            $entity = $this->Customeraddresses->arrange_entity($this->Customeraddresses->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Customeraddresses->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Customeraddresses->getTable(), 'create', $entity['id']);
                    add_activity('Customer address', 'A customer address updated');
                }
            } else {
                $results['status'] = $id = $this->Customeraddresses->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Customeraddresses->getTable(), 'create',  $id);
                    add_activity('Customer address', 'A new customer address added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('customer') . ' ' . lang('customer') . ' ' . lang('address')) : sprintf(lang('saved_failed_msg'), lang('customer') . ' ' . lang('customer') . ' ' . lang('address'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Customeraddresses->entity($this->put());
            $results['status'] = $this->Customeraddresses->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Customeraddresses->getTable(), 'update',  $id);
                add_activity('Customer address', 'A customer address updated');
            }
            if (!empty($results['status']));
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('customer') . ' ' . lang('address')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('customer') . ' ' . lang('address'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customeraddresses->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Customeraddresses->getTable(), 'update',  $id);
                add_activity('Customer address', 'A customer address updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('customer') . ' ' . lang('address')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('customer') . ' ' . lang('address'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Customeraddresses->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Customeraddresses->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Customeraddresses->defaultBy()]) {
                $this->Customeraddresses->unset_default(['cust_id' => $obj['cust_id']]);
                $results['status'] = $this->Customeraddresses->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Customeraddresses->getTable(), 'update',  $id);
                    add_activity('Customer address', 'A customer default address updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('address')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('address'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }


    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgvouchers->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Customeraddresses->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Customeraddresses->getTable(), 'delete',  $id);
                    add_activity('Customer address', 'A customer address deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer') . ' ' . lang('address')) : sprintf(lang('delete_failed_msg'), lang('customer') . ' ' . lang('address'));
                return $this->response($results);
            } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customeraddresses->save([$this->Customeraddresses->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Customeraddresses->getTable(), 'update',  $id);
                add_activity('Customer address', 'A customer address restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('address')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('address'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Customeraddresses->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Customeraddresses->getTable(), 'delete',  $id);
                add_activity('Customer address', 'A customer address deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer') . ' ' . lang('address')) : sprintf(lang('delete_failed_msg'), lang('customer') . ' ' . lang('address'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
