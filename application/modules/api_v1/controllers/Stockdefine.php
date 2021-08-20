<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Stockdefine extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Stockdefines');
            $this->load->model('Userhubs');
            $this->load->model('Geoareas');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $source = "item", $hub_id = NULL)
    {
        if (!empty($config)) {
            $results = $this->Stockdefines->build_config($base);
            if (empty($base)) $base = $results['base']->id;
            if ($source !== "item") $results['base'] = null;
            $results['hubs'] = $this->Userhubs->get(['status' => 'active'], NULL, NULL, ['id', 'name', 'area_id', 'house_en', 'house_bn']);
            foreach ($results['hubs'] as $k => $v) {
                $results['hubs'][$k]->name = $this->Userhubs->decode($v->name);
                $results['hubs'][$k]->area = $this->Geoareas->formatted($v->area_id)->formatted;
                if ($hub_id == $v->id) $results['hub'] = $results['hubs'][$k];
            }
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Stockdefines->get($id, TRUE, TRUE);
            $results['entity'] = $this->Stockdefines->build_entity($entity, $hub_id);
        } else {
            $results['list'] = [];
            $results['cat_stock'] = ['sales' => 0, 'sale_weight' => 0, 'balance' => 0, 'balance_weight' => 0, 'balance_price' => 0];
            if ($source === "item" && !empty($base)) $results['list'] = $this->Stockdefines->get_by(['category_id' => $base], FALSE, $deleted, $limit, $offset);
            if ($source === "topping") $results['list'] = $this->Stockdefines->get_by(['category_id' => NULL], FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $entity = $this->Stockdefines->build_entity($obj, $hub_id);
                $results['list'][$index] =  $entity;
                if (intval($id) === intval($obj->id)) $results['entity'] = $results['list'][$index];
                $results['cat_stock']['sales'] += $entity->entry->sales;
                $results['cat_stock']['sale_weight'] += $entity->entry->sale_weight;
                $results['cat_stock']['balance'] += $entity->entry->balance;
                $results['cat_stock']['balance_weight'] += $entity->entry->balance_weight;
                $results['cat_stock']['balance_price'] += $entity->entry->balance *  $entity->base_price;
            }
            $results['total'] = $this->Stockdefines->count_rows(['category_id' => $base], $deleted);
        }
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Stockdefines->entity($this->post());
        if (!empty($entity['id'])) {
            $this->Stockdefines->save($entity, $entity['id']);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Stockdefines->entity($this->put());
            $this->Stockdefines->save($entity, $id);
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
            $this->Stockdefines->save($this->patch(), $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Stockdefines->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Stockdefines->save([$this->Stockdefines->deletedAt() => FALSE, 'status' => 'inactive'], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Stockdefines->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
