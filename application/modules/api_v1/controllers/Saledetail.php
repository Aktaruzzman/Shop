<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Saledetail extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Saledetails');
        }
    }
    public function index_get($base = NULL, $id = NULL, $decoded = NULL)
    {
        if (!empty($base)) {
            $results['base'] = $base ? $this->Sales->get($base, TRUE) : NULL;
            $results['list'] = $this->Saledetails->get_by(['sale_id' => $base], FALSE, TRUE, NULL, NULL, NULL, array_diff($this->Saledetails->listFields(), ['id', 'created_at', 'updated_at', 'deleted_at']));
            if (!empty($decoded)) {
                foreach ($results['list'] as $index => $obj) {
                    $results['list'][$index]->name = json_decode($obj->name, TRUE);
                    $results['list'][$index]->category_name = json_decode($obj->category_name, TRUE);
                    $results['list'][$index]->item_name = json_decode($obj->item_name, TRUE);
                    $results['list'][$index]->option_name = json_decode($obj->option_name, TRUE);
                    $results['list'][$index]->course_name = json_decode($obj->course_name, TRUE);
                    $results['list'][$index]->unit_name = json_decode($obj->unit_name, TRUE);
                    $results['list'][$index]->tax_rule = json_decode($obj->tax_rule, TRUE);
                    $results['list'][$index]->discount_rule = json_decode($obj->discount_rule, TRUE);
                }
            }
        }
        if (!empty($id)) {
            $results['entity'] =  $this->Saledetails->get($id, TRUE, TRUE,  array_diff($this->Saledetails->listFields(), ['id', 'created_at', 'updated_at', 'deleted_at']));
            if (!empty($decoded)) {
                if ($results['entity']) {
                    $results['entity']->name = json_decode($results['entity']->name, TRUE);
                    $results['entity']->category_name = json_decode($results['entity']->category_name, TRUE);
                    $results['entity']->item_name = json_decode($results['entity']->item_name, TRUE);
                    $results['entity']->option_name = json_decode($results['entity']->option_name, TRUE);
                    $results['entity']->course_name = json_decode($results['entity']->course_name, TRUE);
                    $results['entity']->unit_name = json_decode($results['entity']->unit_name, TRUE);
                    $results['entity']->tax_rule = json_decode($results['entity']->tax_rule, TRUE);
                    $results['entity']->discount_rule = json_decode($results['entity']->discount_rule, TRUE);
                }
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Saledetails->validate()) {
            $this->db->trans_start();
            $entity = $this->Saledetails->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Saledetails->save($entity, $entity['id']);
            } else {
                $results['status'] = $this->Saledetails->save($entity);
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
            $entity = $this->Saledetails->entity($this->put());
            $results['status'] = $this->Saledetails->save($entity, $id);
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
            $entity = $this->Saledetails->entity($this->patch());
            $results['status'] = $this->Saledetails->save($entity, $id);
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
            $results['status'] = $this->Saledetails->delete($id);
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
            $results['status'] = $this->Saledetails->save([$this->Saledetails->deletedAt() => FALSE, 'status' => 'inactive'], $id);
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
            $results['status'] = $this->Saledetails->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
