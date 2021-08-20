<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodbase extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodbases');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Users');
            $this->load->model('Userhubs');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $status = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Prodbases->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = json_decode($results['entity']->name);
        } else {
            $user = $this->Users->get(getallheaders()['user_id'], TRUE);
            $packer_conditions = ['role' => 'packer'];
            if ($user->role_value > 2) $packer_conditions['hub_id'] = $user->hub_id;
            $results['packers'] = $this->Users->get($packer_conditions, FALSE, FALSE, ['id', 'name', 'hub_id']);
            foreach ($results['packers'] as $k => $v) {
                $results['packers'][$k]->name = $this->Users->decode($v->name);
                $results['packers'][$k]->hub_name = $this->Users->decode($this->Userhubs->get($v->hub_id, TRUE, TRUE, ['name'])->name);
            }
            $results['list'] = empty($status) ? $this->Prodbases->get_by(NULL, FALSE, $deleted, $limit, $offset) : $this->Prodbases->get_by(['status' => 'active'], FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Prodbases->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Prodbases->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodbases->arrange_entity($this->Prodbases->entity($this->post()));
            if (!empty($entity['id'])) {
                $obj = $this->Prodbases->get($entity['id'], true, true);
                if ($obj->status !== $entity['status']) {
                    $this->Prodbases->co_update($entity['id'], 'status', ['status' => $entity['status']]);
                    $this->Prodoptions->save_by(['status' => $entity['status']], ['base_id' => $entity['id']]);
                    $this->Proditems->save_by(['status' => $entity['status']], ['base_id' => $entity['id']]);
                    $this->Prodcats->save_by(['status' => $entity['status']], ['base_id' => $entity['id']]);
                }
                $results['status'] = $this->Prodbases->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodbases->getTable(), 'update',  $entity['id']);
                    add_activity('Product Base', 'A product base updated');
                }
            } else {
                $results['status'] = $id = $this->Prodbases->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodbases->getTable(), 'create',  $id);
                    add_activity('Product Base', 'A new product base created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('saved_failed_msg'), lang('product') . ' ' . lang('base'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodbases->entity($this->put());
            if (array_key_exists('status', $entity)) {
                $this->Prodbases->co_update($id, 'status', ['status' => $entity['status']]);
                $this->Prodoptions->save_by(['status' => $entity['status']], ['base_id' => $id]);
                $this->Proditems->save_by(['status' => $entity['status']], ['base_id' => $id]);
                $this->Prodcats->save_by(['status' => $entity['status']], ['base_id' => $id]);
            }
            $results['status'] = $this->Prodbases->save($entity, $id);
            add_activity('Product Base', 'A product base updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('base'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodbases->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodbases->getTable(), 'update',  $id);
                add_activity('Product Base', 'A product base updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('base'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Prodbases->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodbases->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Prodbases->defaultBy()]) {
                $this->Prodbases->unset_default(NULL);
                $this->Prodbases->set_default(['id' => $obj['id']]);
                add_activity('Product Base', 'default product base changed');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('base'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Prodbases->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->Prodbases->co_update($id, 'delete');
                $this->Prodoptions->delete_by(['base_id' => $id]);
                $this->Proditems->delete_by(['base_id' => $id]);
                $this->Prodcats->delete_by(['base_id' => $id]);
                $this->Prodbases->delete($id);
                add_activity('Product Base', 'A product base deleted into bin');
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('base'));
                return $this->response($results);
            } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Prodbases->co_update($id, 'undo');
            $this->Prodoptions->save_by([$this->Prodoptions->deletedAt() => FALSE], ['base_id' => $id]);
            $this->Proditems->save_by([$this->Proditems->deletedAt() => FALSE], ['base_id' => $id]);
            $this->Prodcats->save_by([$this->Prodcats->deletedAt() => FALSE], ['base_id' => $id]);
            $this->Prodbases->save([$this->Prodbases->deletedAt() => FALSE], $id);
            add_activity('Product Base', 'A product base restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('update_failed_msg'), lang('product') . ' ' . lang('base'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Prodbases->delete($id, TRUE);
            add_activity('Product Base', 'A product base deleted permanently');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('product') . ' ' . lang('base')) : sprintf(lang('delete_failed_msg'), lang('product') . ' ' . lang('base'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Prodbases->get(NULL, FALSE, TRUE);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name);
            $entity['name_en'] = $name->en;
            $entity['name_bn'] = $name->bn;
            $this->Prodbases->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
