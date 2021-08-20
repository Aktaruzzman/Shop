<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Stockentry extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Stockentries');
            $this->load->model('Stocklogs');
            $this->load->model('Stockhubentries');
            $this->load->model('Userhubs');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $hub_id = NULL)
    {
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], NULL, NULL, ['id', 'name']);
        foreach ($results['hubs'] as $k => $v) {
            $results['hubs'][$k]->name = $this->Userhubs->decode($v->name);
            if ($hub_id == $v->id) $results['hub'] = $results['hubs'][$k];
        }
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Stockentries->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Stockentries->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Stockentries->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Stockentries->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Stockentries->validate()) {
            $entity = $this->Stockentries->post_entity($this->post());
            if (!empty($entity['id'])) {
                $this->db->trans_start();
                $obj = $this->Stockentries->get($entity['id'], TRUE, TRUE);
                $hub_id = !empty($entity['hub_id']) && $entity['hub_id'] > 0 ? $entity['hub_id'] : getallheaders()['hub_id'];
                $stockhub = $this->Stockhubentries->get_by(['stock_id' => $obj->stock_id, 'hub_id' => $hub_id], TRUE);
                $this->Stockentries->save(['balance' => $obj->balance + $entity['quantity']], $entity['id']);
                if (!empty($stockhub)) $this->Stockhubentries->save(['balance' => $stockhub->balance + $entity['quantity']], $stockhub->id);
                else $this->Stockhubentries->save(['stock_id' => $obj->stock_id, 'hub_id' => $hub_id, 'balance' => $entity['quantity']]);
                $this->Stocklogs->save(["stock_id" => $entity['stock_id'], 'stockloc_id' => $entity['stockloc_id'], 'hub_id' => $hub_id, 'item_in' => $entity['quantity'], 'closed_on' => closed_on()]);
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
                return $this->response($results);
            }
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Stockentries->entity($this->put());
            $this->Stockentries->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Stockentries->save($this->patch(), $id);
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
            $results['status'] = $this->Stockentries->delete($id);
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
            $results['status'] = $this->Stockentries->save([$this->Stockentries->deletedAt() => FALSE], $id);
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
            $results['status'] = $this->Stockentries->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
