<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Syncupdate extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Syncupdates');
            $this->load->model('Userlogs');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Syncupdates->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Syncupdates->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Syncupdates->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Syncupdates->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $results['list'] = $this->Syncupdates->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['value', 'func', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Syncupdates->entity($this->post());
        $results['list'] = $this->Syncupdates->get_by($criteria, FALSE, TRUE, NULL, NULL);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Syncupdates->validate()) {
            $this->db->trans_start();
            $entity = $this->Syncupdates->entity($this->post());
            $order_by = $this->Syncupdates->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Syncupdates->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Syncupdates->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Syncupdates->save($entity, $entity['id']);
            } else {
                if (!empty($order_by) && $order_by === 'order') {
                    $entity[$order_by] = $this->Syncupdates->get_max($order_by) + 1;
                }
                $results['status'] = $this->Syncupdates->save($entity);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('saved_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Syncupdates->entity($this->put());
            $order_by = $this->Syncupdates->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Syncupdates->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Syncupdates->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Syncupdates->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('update_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Syncupdates->entity($this->patch());
            $order_by = $this->Syncupdates->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Syncupdates->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Syncupdates->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Syncupdates->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('update_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Syncupdates->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Syncupdates->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Syncupdates->defaultBy()]) {
                $this->Syncupdates->unset_default(NULL);
                $results['status'] = $this->Syncupdates->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('update_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Syncupdates->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('delete_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Syncupdates->save([$this->Syncupdates->deletedAt() => FALSE, 'status' => 'inactive'], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('update_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Syncupdates->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('vat') . ' ' . lang('percent')) : sprintf(lang('delete_failed_msg'), lang('vat') . ' ' . lang('percent'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
    public function test_get(){
        $results['devices']=$this->Userlogs->get_device_list();
        return $this->response($results);
    }
    
}

