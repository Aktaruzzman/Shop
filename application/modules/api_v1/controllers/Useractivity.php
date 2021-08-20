<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Useractivity extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Users');
            $this->load->model('Useractivities');
        }
    }
    
    public function index_get($id = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $from = NULL, $to = NULL)
    {
        $results['base'] = $base ? $this->Users->get($base, TRUE, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Useractivities->get($id, TRUE, $deleted);
        } else {
            $results['list'] = !empty($base) ? $this->Useractivities->get_activities($id, $base, $deleted, $limit, $offset, date('Y-m-d H:i:s', $from / 1000), date('Y-m-d H:i:s', $to / 1000)) : $this->Useractivities->get_activities(NULL, FALSE, $deleted, $limit, $offset, date('Y-m-d H:i:s', $from / 1000), date('Y-m-d H:i:s', $to / 1000));
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = json_decode($obj->name);
                $results['list'][$index]->hub_name = json_decode($obj->hub_name);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        $user = $this->Users->get(getallheaders()['user_id']);
        if (!empty($user)) $results['users'] = $this->Users->down_users($user);
        return $this->response($results);
    }
    public function search_post()
    {
        $results['criteria'] = $this->post();
        $base = !empty($this->post('base')) ? trim($this->post('base')) : NULL;
        $results['base'] = !empty($base) ? $this->Users->get($base, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        $results['list'] = !empty($base) ? $this->Useractivities->get_by(['user_id' => $base], FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name_en', 'name_bn', 'status']) : $this->Useractivities->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')),  ['name_en', 'name_bn', 'status']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Useractivities->entity($this->post());
        $results['list'] = $this->Useractivities->get_by($criteria, FALSE, TRUE, NULL, NULL);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->name = json_decode($obj->name);
        }
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Useractivities->validate()) {
            $this->db->trans_start();
            $entity = $this->Useractivities->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Useractivities->save($entity, $entity['id']);
            } else {
                $results['status'] = $this->Useractivities->save($entity);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('user') . ' ' . lang('activity')) : sprintf(lang('saved_failed_msg'), lang('user') . ' ' . lang('activity'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Useractivities->entity($this->put());
            $results['status'] = $this->Useractivities->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('user') . ' ' . lang('activity')) : sprintf(lang('update_failed_msg'), lang('user') . ' ' . lang('activity'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Useractivities->entity($this->patch());
            $results['status'] = $this->Useractivities->save($entity, $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('user') . ' ' . lang('activity')) : sprintf(lang('update_failed_msg'), lang('user') . ' ' . lang('activity'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Useractivities->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Useractivities->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Useractivities->defaultBy()]) {
                $this->Useractivities->unset_default(['user_id' => $obj['user_id']]);
                $this->Useractivities->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }


    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Useractivities->delete($id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Useractivities->save([$this->Useractivities->deletedAt() => FALSE, 'status' => 'inactive'], $id);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Useractivities->delete($id, TRUE);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
