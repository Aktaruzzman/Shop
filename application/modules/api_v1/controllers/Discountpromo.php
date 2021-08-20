<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Discountpromo extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Discountpromos');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Discountpromos->get($id, TRUE, $deleted);
            $results['entity'] = $this->Discountpromos->build_entity($entity);
        } else {
            $results['list'] = $this->Discountpromos->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Discountpromos->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] = $results['list'][$index];
            }
            $results['total'] = $this->Discountpromos->count_rows(NULL, $deleted);
        }
        if (!empty($config)) $results = $this->Discountpromos->build_config($results);
        return $this->response($results);
    }



    public function index_post()
    {
        if ($this->Discountpromos->validate()) {
            $this->db->trans_start();
            $entity = $this->Discountpromos->arrange_entity($this->Discountpromos->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Discountpromos->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Discountpromos->getTable(), 'update', $entity['id']);
                    add_activity('discount promo', 'A discount promo updated');
                }
            } else {
                $results['status'] = $id = $this->Discountpromos->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Discountpromos->getTable(), 'create', $id);
                    add_activity('discount promo', 'A discount promo updated');
                }
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
            $entity = $this->Discountpromos->entity($this->put());
            $results['status'] = $this->Discountpromos->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountpromos->getTable(), 'update', $id);
                add_activity('discount promo', 'A discount promo updated');
            }
            if (!empty($results['status'])) add_activity('discount promo', 'A discount promo updated');
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
            $results['status'] = $this->Discountpromos->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountpromos->getTable(), 'update', $id);
                add_activity('discount promo', 'A discount promo updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Discountpromos->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Discountpromos->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Discountpromos->defaultBy()]) {
                $this->Discountpromos->unset_default(NULL);
                $results['status'] = $this->Discountpromos->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Discountpromos->getTable(), 'update', $id);
                    add_activity('discount promo', 'A default discount promo updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Discountpromos->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Discountpromos->getTable(), 'delete', $id);
                add_activity('discount promo', 'a discount promo deleted into bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Discountpromos->save([$this->Discountpromos->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountpromos->getTable(), 'update', $id);
                add_activity('discount promo', 'a discount promo restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Discountpromos->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Discountpromos->getTable(), 'trash', $id);
                add_activity('discount promo', 'a discount promo deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
