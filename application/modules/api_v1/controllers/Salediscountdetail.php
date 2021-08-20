<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Salediscountdetail extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Salediscountdetails');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Salediscountdetails->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Salediscountdetails->get_by(NULL, FALSE, $deleted, $limit, $offset);
            if(!empty($id)){
                $results['entity'] = $this->Salediscountdetails->get($id, TRUE, $deleted);
            }
            $results['total'] = $this->Salediscountdetails->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $results['list'] = $this->Salediscountdetails->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['value', 'func', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Salediscountdetails->entity($this->post());
        $results['list'] = $this->Salediscountdetails->get_by($criteria, FALSE, TRUE, NULL, NULL);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Salediscountdetails->validate()) {
            $this->db->trans_start();
            $entity = $this->Salediscountdetails->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Salediscountdetails->save($entity, $entity['id']);
            } else {
                $results['status'] = $this->Salediscountdetails->save($entity);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Salediscountdetails->entity($this->put());
            $results['status'] = $this->Salediscountdetails->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Salediscountdetails->entity($this->patch());
            $results['status'] = $this->Salediscountdetails->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Salediscountdetails->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Salediscountdetails->save([$this->Salediscountdetails->deletedAt() => FALSE, 'status' => 'inactive'], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Salediscountdetails->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
