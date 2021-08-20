<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Opening extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Openings');
        }
    }
    public function index_get($id = null, $edit = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Openings->get($id, true);
        } else {
            $results['list'] = $this->Openings->get_by(null, false);
            foreach ($results['list'] as $index => $obj) {
                if ($obj->temp_closed && strtotime($obj->closed_till) > time()) {
                    $results['list'][$index]->closed_till = date('h:i A', strtotime($obj->closed_till));
                } else {
                    $results['list'][$index]->closed_till = false;
                    $results['list'][$index]->temp_closed = false;
                }
                if (!empty($id) && $id == $obj->id) $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Openings->validate()) {
            $this->db->trans_start();
            $entity = $this->Openings->entity($this->post());
            $entity['delivery'] = !empty($entity['delivery']) ? true : false;
            $entity['collection'] = !empty($entity['collection']) ? true : false;
            $entity['booking'] = !empty($entity['booking']) ? true : false;
            if (!empty($entity['closed_till'])) $entity['closed_till'] = add_time($entity['closed_till'] . 'M', true);
            if (!empty($entity['id'])) {
                $results['status'] = $this->Openings->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('opening time', 'An opening time updated');
            } else {
                $results['status'] = $this->Openings->save($entity);
                if (!empty($results['status'])) add_activity('opening time', 'An opening time created');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('time')) : sprintf(lang('saved_failed_msg'), lang('time'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Openings->entity($this->put());
            $results['status'] = $this->Openings->save($entity, $id);
            if (!empty($results['status'])) add_activity('opening time', 'An opening time updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('time')) : sprintf(lang('update_failed_msg'), lang('time'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Openings->save($this->patch(), $id);
            if (!empty($results['status'])) add_activity('opening time', 'An opening time updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('time')) : sprintf(lang('update_failed_msg'), lang('time'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
}
