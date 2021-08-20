<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgvoucher extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgvouchers');
            $this->load->model('Payments');
            $this->load->model('Users');
            $this->load->model('Userhubs');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($config)) {
            $results['payments'] = $this->Payments->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name', 'value', 'default']);
            foreach ($results['payments'] as $index => $obj) {
                $results['payments'][$index]->name = json_decode($obj->name);
            }
            $results['hubs'] = $this->Userhubs->get_by(NULL, FALSE);
            foreach ($results['hubs'] as $index => $obj) {
                $results['hubs'][$index]->name =  $this->Userhubs->decode($obj->name);
            }
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Cfgvouchers->get($id, TRUE, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $user = $this->Users->get(getallheaders()['user_id'], true, true);
            $condition = null;
            if ($user->role_value >= 3 && config_item('store_type') === 'multiple') $condition = ['hub_id' => $user->hub_id];
            $results['list'] = $this->Cfgvouchers->get_by($condition, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Cfgvouchers->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    private function build_entity($entity)
    {
        if (!empty($entity)) {
            $entity->status = ((time() * 1000) > $entity->end ||  $entity->balance <= 0) ?  'expired' : 'active';
            $entity->hub = $this->Cfgvouchers->decode($this->Userhubs->get($entity->hub_id, true, true)->name);
        }
        return $entity;
    }
    public function index_post()
    {
        if ($this->Cfgvouchers->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgvouchers->arrange_entity($this->Cfgvouchers->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgvouchers->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgvouchers->getTable(), 'update',  $entity['id']);
                    add_activity('Voucher', 'A voucher updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgvouchers->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgvouchers->getTable(), 'create',  $id);
                    add_activity('Voucher', 'A new voucher added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('voucher')) : sprintf(lang('saved_failed_msg'), lang('voucher'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgvouchers->entity($this->put());
            $results['status'] = $this->Cfgvouchers->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgvouchers->getTable(), 'update',  $id);
                add_activity('Vocuher', 'A Vocuher updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('voucher')) : sprintf(lang('update_failed_msg'), lang('voucher'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgvouchers->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgvouchers->getTable(), 'update',  $id);
                add_activity('Vocuher', 'A Vocuher updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('voucher')) : sprintf(lang('update_failed_msg'), lang('voucher'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Cfgvouchers->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Cfgvouchers->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Cfgvouchers->defaultBy()]) {
                $this->Cfgvouchers->unset_default(NULL);
                $results['status'] = $this->Cfgvouchers->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgvouchers->getTable(), 'update',  $id);
                    add_activity('Voucher', 'Default voucher changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('voucher')) : sprintf(lang('update_failed_msg'), lang('voucher'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgvouchers->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgvouchers->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Cfgvouchers->getTable(), 'delete',  $id);
                    add_activity('Voucher', 'A voucher deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('voucher')) : sprintf(lang('delete_failed_msg'), lang('voucher'));
                return $this->response($results);
            } else {
                return $this->response(['status' => FALSE, 'message' => 'invalid request']);
            }
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgvouchers->save([$this->Cfgvouchers->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgvouchers->getTable(), 'update',  $id);
                add_activity('Tax Option', 'A tax option restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('voucher')) : sprintf(lang('update_failed_msg'), lang('voucher'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgvouchers->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgvouchers->getTable(), 'delete',  $id);
                add_activity('Voucher', 'A Voucher deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('voucher')) : sprintf(lang('delete_failed_msg'), lang('voucher'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
