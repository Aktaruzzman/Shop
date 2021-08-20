<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Userhubpacker extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Userhubpackers');
            $this->load->model('Users');
            $this->load->model('Userhubs');
            $this->load->model('Prodbases');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $config = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Userhubpackers->get($id, TRUE, FALSE);
            $results['entity'] = $this->Userhubpackers->build_entity($entity);
        } else {
            $results['list'] = $this->Userhubpackers->get_by(['hub_id' => !empty(getallheaders()['hub_id']) ? getallheaders()['hub_id'] : 1]);
            foreach ($results['list'] as $index => $entity) {
                $results['list'][$index] = $this->Userhubpackers->build_entity($entity);
                if ($id == $entity->id) $results['entity'] =  $results['list'][$index];
            }
        }
        if (!empty($config)) {
            $results['users'] = $this->Users->get(['hub_id' => !empty(getallheaders()['hub_id']) ? getallheaders()['hub_id'] : 1, 'role' => 'packer'], FALSE, FALSE, ['id', 'name']);
            foreach ($results['users'] as $k => $v) $results['users'][$k]->name = $this->Users->decode($v->name);
            $results['hub'] = $this->Userhubs->get(['id' => !empty(getallheaders()['hub_id']) ? getallheaders()['hub_id'] : 1], TRUE, TRUE, ['id', 'name']);
            $results['hub']->name = $this->Userhubs->decode($results['hub']->name);
            $results['bases'] = $this->Prodbases->get(['status' => 'active'], FALSE, FALSE, ['id', 'name']);
            foreach ($results['bases'] as $k => $v) $results['bases'][$k]->name = $this->Prodbases->decode($v->name);
        }
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Userhubpackers->validate()) {
            $entity = $this->Userhubpackers->entity($this->post());
            if (empty($entity['base_id'])) $entity['base_id'] = null;
            if (!empty($entity['id'])) {
                $this->db->trans_start();
                $this->Userhubpackers->save($entity, $entity['id']);
                add_activity('User hub packer', 'A user hub packer updated');
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
                return $this->response($results);
            } else {
                if (empty($this->Userhubpackers->get_by($entity, TRUE))) {
                    $this->db->trans_start();
                    $defaultPacker = $this->Userhubpackers->get(['default' => true, 'hub_id' => $entity['hub_id']], FALSE, TRUE);
                    if (empty($defaultPacker)) $entity['default'] = true;
                    $this->Userhubpackers->save($entity);
                    add_activity('User hub packer', 'A new user hub packer created');
                    $this->db->trans_complete();
                    $results['status'] = $this->db->trans_status();
                    $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
                    return $this->response($results);
                } else return $this->response(['status' => false]);
            }
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Userhubpackers->entity($this->put());
            $id = $this->Userhubpackers->save($entity, $id);
            add_activity('User hub packer', 'A user hub packer updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Userhubpackers->save($this->patch(), $id);
            add_activity('User hub packer', 'A user hub packer updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Userhubpackers->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Userhubpackers->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Userhubpackers->defaultBy()]) {
                $this->Userhubpackers->unset_default(['hub_id' => $obj['hub_id']]);
                $this->Userhubpackers->set_default(['id' => $obj['id']]);
                add_activity('User hub packer', 'default user hub packer changed');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $obj = $this->Userhubpackers->get($id, tRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->Userhubpackers->delete($id);
                add_activity('User hub packer', 'A user hub packer updated');
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
                return $this->response($results);
            }
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Userhubpackers->save([$this->Userhubpackers->deletedAt() => FALSE], $id);
            add_activity('User hub packer', 'A user hub packer updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Userhubpackers->delete($id, TRUE);
            add_activity('User hub packer', 'A user hub packer deleted');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
