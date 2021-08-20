<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgdevice extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgdevices');
        }
    }
    public function index_get($id = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        $results = [];
        if (!empty($id)) {
            $results['entity'] = $this->Cfgdevices->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Cfgdevices->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total']  = $this->Cfgdevices->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }
    public function index_post()
    {
        $this->db->trans_start();
        $entity['code'] = $this->Cfgdevices->get_code();
        $results['status'] = $id = $this->Cfgdevices->save($entity);
        if (!empty($results['status'])) {
            add_sync($this->Cfgdevices->getTable(), 'create',  $id);
            add_activity('Device code', 'A new device code generated');
        }
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status();
        $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('device')) : sprintf(lang('saved_failed_msg'), lang('device'));
        return $this->response($results);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdevices->entity($this->put());
            $results['status'] = $this->Cfgdevices->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdevices->getTable(), 'update',  $id);
                add_activity('Device code', 'A device code updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgdevices->entity($this->patch());
            $results['status'] = $this->Cfgdevices->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdevices->getTable(), 'update',  $id);
                add_activity('Device code', 'A device code updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function submit_patch($code = NULL)
    {
        if (!empty($code)) {
            $this->db->trans_start();
            $used = $this->Cfgdevices->get_by(['code' => $code], TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdevices->getTable(), 'update',  $used->id);
                add_activity('Device code', 'A device code updated');
            }
            if (!empty($used)) $this->Cfgdevices->save_by(['is_used' => TRUE, $this->Cfgdevices->deletedAt() => TRUE], ['code' => $code]);
            $this->db->trans_complete();
            $results['code'] = $code;
            $results['status'] = $this->db->trans_status() && !empty($used);
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('code')) : sprintf(lang('update_failed_msg'), lang('code'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgdevices->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdevices->getTable(), 'delete',  $id);
                add_activity('Device code', 'A device code deleted into bin');
            }
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
            $results['status'] = $this->Cfgdevices->save([$this->Cfgdevices->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdevices->getTable(), 'update',  $id);
                add_activity('Device code', 'A device code restored from bin');
            }
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
            $results['status'] = $this->Cfgdevices->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Cfgdevices->getTable(), 'delete',  $id);
                add_activity('Device code', 'A device code deleted from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
