<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Supplieragent extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Supplieragents');
            $this->load->model('Storesupplieragents');
        }
    }
    public function index_get($id = null, $edit = null, $deleted = null, $limit = null, $offset = null, $status = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Supplieragents->get($id, true, $deleted);
            if ($results['entity']) $results['entity']->name = json_decode($results['entity']->name);
        } else {
            if (config_item('store_type') === 'multiple') $results['list'] = $this->Supplieragents->get_supplieragents(getallheaders()['hub_id'], $deleted, $limit, $offset, $status);
            else $results['list'] = $this->Supplieragents->get_by($status ? ['status' => $status] : null, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = $this->Supplieragents->decode($obj->name);
                if ($id == $obj->id)  $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Supplieragents->entity($this->post());
        $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
        if (!empty($entity['id'])) {
            if (config_item('store_type') === 'multiple') {
                if ($this->post('hub_id') == getallheaders()['hub_id'])
                    $this->Supplieragents->save($entity, $entity['id']);
            } else $this->Supplieragents->save($entity, $entity['id']);
            add_activity('Supplier agent', 'A supplier agent updated');
        } else {
            $agent = $this->Supplieragents->get(['phone' => $entity['phone']], true, true);
            if (!empty($agent)) {
                $storeagent = $this->Storesupplieragents->get(['supplieragent_id' => $agent->id], true, true);
                if (empty($storeagent)) $this->Storesupplieragents->save(['supplieragent_id' => $agent->id, 'hub_id' => getallheaders()['hub_id']]);
            } else {
                $agent_id = $this->Supplieragents->save($entity);
                $this->Storesupplieragents->save(['supplieragent_id' => $agent_id, 'hub_id' => getallheaders()['hub_id']]);
            }
            add_activity('Supplier agent', 'A supplier agent added');
        }
        $this->db->trans_complete();
        $results['status']  = $this->db->trans_status();
        $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
        return $this->response($results);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Supplieragents->entity($this->put());
            $id = $this->Supplieragents->save($entity, $id);
            add_activity('User hub', 'A user hub updated');
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Supplieragents->save($this->patch(), $id);
            add_activity('User hub', 'A user hub updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Supplieragents->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Supplieragents->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Supplieragents->defaultBy()]) {
                $this->Supplieragents->unset_default(null);
                $this->Supplieragents->set_default(['id' => $obj['id']]);
                add_activity('User hub', 'default user hub changed');
            }
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $obj = $this->Supplieragents->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->Supplieragents->delete($id);
                add_activity('User hub', 'A user hub updated');
                $this->db->trans_complete();
                $results['status']  = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
                return $this->response($results);
            }
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Supplieragents->save([$this->Supplieragents->deletedAt() => false], $id);
            add_activity('User hub', 'A user hub updated');
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Supplieragents->delete($id, true);
            add_activity('User hub', 'A user hub deleted');
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
