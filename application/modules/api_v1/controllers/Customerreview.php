<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Customerreview extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customerreviews');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Customerreviews->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Customerreviews->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Customerreviews->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Customerreviews->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Customerreviews->validate()) {
            $this->db->trans_start();
            $entity = $this->Customerreviews->arrange_entity($this->Customerreviews->entity($this->post()));
            if (!empty($entity['id'])) $results['status'] = $this->Customerreviews->save($entity, $entity['id']);
            else $results['status'] = $this->Customerreviews->save($entity);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('saved_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Customerreviews->entity($this->put());
            $results['status'] = $this->Customerreviews->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerreviews->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Customerreviews->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Customerreviews->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Customerreviews->defaultBy()]) {
                $this->Customerreviews->unset_default(NULL);
                $results['status'] = $this->Customerreviews->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerreviews->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('delete_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Customerreviews->save([$this->Customerreviews->deletedAt() => FALSE], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('update_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Customerreviews->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('customer') . ' ' . lang('review')) : sprintf(lang('delete_failed_msg'), lang('customer') . ' ' . lang('review'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
