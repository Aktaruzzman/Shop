<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Userpvgesmodule extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Userpvgesmodules');
            $this->load->model('Userpvgesactions');
            $this->load->model('Users');
        }
    }
    public function index_get($user_id = NULL, $config = NULL)
    {
        $entity = $this->Userpvgesmodules->get_by(['user_id' => $user_id], TRUE, TRUE);
        $results['list'] = !empty($entity) ? $this->Userpvgesmodules->build_entity($entity) : null;
        if (!empty($config)) {
            $results['base'] = $this->Users->get($user_id, TRUE, TRUE);
            if (!empty($results['base'])) $results['base']->name = $this->Users->decode($results['base']->name);
            $user = $this->Users->get(getallheaders()['user_id']);
            if (!empty($user)) $results['users'] = $this->Users->down_users($user);
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Userpvgesmodules->validate()) {
            $this->db->trans_start();
            $entity = $this->Userpvgesmodules->post_entity($this->post());
            $extractions = $this->Userpvgesmodules->extractions($entity);
            $field = $extractions['module']['name'];
            $value = $this->Userpvgesmodules->encode($extractions['action']);
            $where_id = $extractions['module']['id'];
            $this->Userpvgesactions->save([$field => $value], $where_id);
            $this->Userpvgesmodules->save([$field => $extractions['module']['status']], $where_id);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('item')) : sprintf(lang('saved_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }


    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Userpvgesmodules->entity($this->put());
            $results['status'] = $this->Userpvgesmodules->save($entity, $id);
            if (!empty($results['status'])) add_activity('User privileges', 'User privileges updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
}
