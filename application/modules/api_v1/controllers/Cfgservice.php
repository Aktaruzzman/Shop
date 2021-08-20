<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgservice extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgservices');
            $this->load->model('Sales');
            $this->load->model('Openings');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $pos = NULL)
    {
        $results = [];
        if (!empty($id) && empty($edit)) {
            $entity = $this->Cfgservices->get($id, TRUE, $deleted);
            $results['entity'] = $this->Cfgservices->build_entity($entity);
        } else {
            $results['list'] = !empty($pos) ? $this->Cfgservices->get_by(['value!=' => 'caller', 'status' => 'active'], FALSE, $deleted, $limit, $offset) : $this->Cfgservices->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Cfgservices->build_entity($obj);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        $results['tillaccount'] = $this->Sales->initial_till_cash(getallheaders());
        $openingTime = $this->Openings->get(['day' => date('D')], true);
        if (($openingTime->temp_closed && strtotime($openingTime->closed_till) > time()) || !$openingTime->opened) {
            $results['webstore_temp_closed'] = true;
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Cfgservices->validate()) {
            $this->db->trans_start();
            $entity = $this->Cfgservices->arrange_entity($this->Cfgservices->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Cfgservices->save($entity, $entity['id']);
                if ($results['status']) {
                    add_sync($this->Cfgservices->getTable(), 'update',  $entity['id']);
                    add_activity('System Service', 'A system service updated');
                }
            } else {
                $results['status'] = $id = $this->Cfgservices->save($entity);
                if ($results['status']) {
                    add_sync($this->Cfgservices->getTable(), 'create',  $id);
                    add_activity('System Service', 'A new system service added');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Cfgservices->entity($this->put());
            $results['status'] = $this->Cfgservices->save($entity, $id);
            if ($results['status']) {
                add_sync($this->Cfgservices->getTable(), 'update',  $id);
                add_activity('System Service', 'A system service updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgservices->save($this->patch(), $id);
            if ($results['status']) {
                add_sync($this->Cfgservices->getTable(), 'update',  $id);
                add_activity('System Service', 'A system service updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Cfgservices->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Cfgservices->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Cfgservices->defaultBy()]) {
                $this->Cfgservices->unset_default(NULL);
                $results['status'] = $this->Cfgservices->set_default(['id' => $obj['id']]);
                if ($results['status']) {
                    add_sync($this->Cfgservices->getTable(), 'update',  $id);
                    add_activity('System Service', 'Default system service changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Cfgservices->get($id, TRUE);
            if (empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Cfgservices->delete($id);
                if ($results['status']) {
                    add_sync($this->Cfgservices->getTable(), 'update',  $id);
                    add_activity('System Service', 'A system service deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
                return $this->response($results);
            } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Cfgservices->save([$this->Cfgservices->deletedAt() => FALSE], $id);
            if ($results['status']) {
                add_sync($this->Cfgservices->getTable(), 'update',  $id);
                add_activity('System Service', 'A system service restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Cfgservices->delete($id, TRUE);
            if ($results['status']) {
                add_sync($this->Cfgservices->getTable(), 'trash',  $id);
                add_activity('System Service', 'A system service deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
