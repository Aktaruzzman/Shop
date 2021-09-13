<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class User extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Userhubs');
            $this->load->model('Users');
            $this->load->model('Userlogs');
            $this->load->model('Userpvgesmodules');
            $this->load->model('Userpvgesactions');
            $this->load->model('Geoareas');
            $this->load->model('Cfgconfigs');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        $results['base'] = $base ? $this->Userhubs->get($base, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Users->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = $this->Users->decode($results['entity']->name);
        } else {
            $results['list'] = !empty($config) ? $this->Users->get_users(0, $base, $deleted, $limit, $offset) : $this->Users->get_users($id, $base, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = $this->Users->decode($obj->name);
                $results['list'][$index]->hub_name =  $this->Userhubs->decode($obj->hub_name);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = !empty($base) ? $this->Users->count_rows(['hub_id' => $base], $deleted) : $this->Users->count_rows(NULL, $deleted);
        }
        if (!empty($config)) {
            $results['hubs'] = $this->Userhubs->get_by(NULL, FALSE);
            foreach ($results['hubs'] as $index => $obj) {
                $results['hubs'][$index]->name =  $this->Userhubs->decode($obj->name);
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Users->validate()) {
            $this->db->trans_start();
            $entity = $this->Users->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            if (!empty($entity['password'])) $entity['password'] = md5($entity['password']);
            else unset($entity['password']);
            $roles = ['superadmin' => 1, 'owner' => 2, 'admin' => 3, "salesman" => 4, "handler" => 4, "packer" => 5, "driver" => 5];
            if (!empty($entity['role'])) $entity['role_value'] = $roles[$entity['role']];
            if (!empty($entity['gender'])) $entity['photo'] = $entity['gender'] . '.png';
            if (!empty($entity['id'])) {
                $this->Users->save($entity, $entity['id']);
                add_activity('User', 'A user information updated');
            } else {
                $sample_user = $this->Users->get_by(['role' => $entity['role']], TRUE, TRUE, 1, 0);
                $user_modules = $this->Userpvgesmodules->get_by(['user_id' => $sample_user->id], TRUE);
                $user_actions = $this->Userpvgesactions->get_by(['user_id' => $sample_user->id], TRUE, TRUE);
                if (!empty($user_modules) && !empty($user_actions)) {
                    unset($user_modules->id);
                    unset($user_modules->created_at);
                    unset($user_modules->updated_at);
                    unset($user_modules->deleted_at);
                    unset($user_actions->id);
                    unset($user_actions->created_at);
                    unset($user_actions->updated_at);
                    unset($user_actions->deleted_at);
                }
                $id = $this->Users->save($entity);
                if (!empty($user_modules) && !empty($user_actions)) {
                    $user_modules->user_id = $id;
                    $user_actions->user_id = $id;
                    $user_modules_entity = get_object_vars($user_modules);
                    $user_actions_entity = get_object_vars($user_actions);
                    $this->Userpvgesmodules->save($this->Userpvgesmodules->entity($user_modules_entity));
                    $this->Userpvgesactions->save($this->Userpvgesactions->entity($user_actions_entity));
                }
                add_activity('User', 'A new user created');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('user')) : sprintf(lang('saved_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }

    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Users->entity($this->put());
            $this->Users->save($entity, $id);
            add_activity('User', 'A user information updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('user')) : sprintf(lang('update_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Users->save($this->patch(), $id);
            add_activity('User', 'A user information updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('user')) : sprintf(lang('update_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Users->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Users->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Users->defaultBy()]) {
                $this->Users->unset_default(NULL);
                $this->Users->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('user')) : sprintf(lang('update_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Users->delete($id);
            add_activity('User', 'A user information updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('user')) : sprintf(lang('delete_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Users->save([$this->Users->deletedAt() => FALSE], $id);
            add_activity('User', 'A user restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('user')) : sprintf(lang('update_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Users->delete($id, TRUE);
            add_activity('User', 'A user deleted permanently from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('user')) : sprintf(lang('delete_failed_msg'), lang('user'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }

    public function login_get($hub_id = null, $lang = null)
    {
        $results = $this->Cfgconfigs->setup();
        $this->db->select('name,username,role');
        $this->db->from('users');
        $this->db->where('status', 'active');
        $this->db->where('deleted_at', false);
        $this->db->where('role_value >', 2);
        if ($hub_id)  $this->db->where('hub_id', $hub_id);
        $results['users'] = $this->db->get()->result();
        foreach ($results['users'] as $index => $obj) {
            $results['users'][$index]->name = $this->Users->decode($obj->name);
        }
        $results['store_info'] = store_info(config_item('store_type') === 'multiple' && $hub_id ? $hub_id : null, $lang);

        return $this->response($results);
    }
    public function login_post()
    {
        $data = $this->post();
        if (!empty($data['username']) && !empty($data['password'])) {
            $user = $this->db->where('email', $data['username'])->or_where('phone', $data['username'])->or_where('username', $data['username'])->get('users')->row();
            if ($user->password === md5($data['password']) && $user->status === 'active' && !$user->deleted_at) {
                $results['status'] = TRUE;
                unset($user->password);
                unset($user->created_at);
                unset($user->updated_at);
                unset($user->deleted_at);
                $user->name = json_decode($user->name);
                $login = time() * 1000;
                $data = ['user_id' => $user->id, 'login' => $login, 'device_code' => $data['device_code']];
                $user->log_id = $this->Userlogs->save($data);
                $user->login_at = $login;
                $results['user'] = $user;
                $results['permissions'] = $this->Userpvgesmodules->build_entity($this->Userpvgesmodules->get_by(['user_id' => $data['user_id']], TRUE, TRUE));
                $results['salehub'] = $this->Userhubs->get($user->hub_id, TRUE);
                $results['salehub']->name = $this->Userhubs->decode($results['salehub']->name);
                $results['salehub']->area = $this->Geoareas->formatted($results['salehub']->area_id)->formatted;
            } else $results['status'] = FALSE;
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function logout_post()
    {
        $result['status'] = $this->Userlogs->save(array('logout' => time() * 1000), getallheaders()['log_id']);
        return $this->response($result);
    }
}
