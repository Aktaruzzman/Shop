<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Subscriber extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Subscribers');
            $this->load->model('Broadcastmsgs');
        }
    }
    public function index_get($id = null, $edit = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Subscribers->get($id, true);
        } else {
            $results['list'] = $this->Subscribers->get_by(null, false);
            $results['entity'] = !empty($id) ? $this->Subscribers->get($id, true) : null;
        }
        $results['messages'] = $this->Broadcastmsgs->get_by(null, false, false, 10, 0, null, null, 'id', 'DESC');
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Subscribers->validate()) {
            $this->db->trans_start();
            $entity = $this->Subscribers->entity($this->post());
            if (!empty($entity['id'])) {
                $results['status'] = $this->Subscribers->save($entity, $entity['id']);
                if (!empty($results['status']))  add_activity('Subscriber', 'A subscriber updated');
            } else {
                $results['status'] = $id = $this->Subscribers->save($entity);
                if (!empty($results['status']))  add_activity('Subscriber', 'A new subscriber added');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'),  lang('subscriber')) : sprintf(lang('saved_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Subscribers->entity($this->put());
            $results['status'] = $this->Subscribers->save($entity, $id);
            if (!empty($results['status'])) add_activity('Subscriber', 'A subscriber updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'),  lang('subscriber')) : sprintf(lang('update_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Subscribers->save($this->patch(), $id);
            if (!empty($results['status'])) add_activity('Subscriber', 'A subscriber updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'),  lang('subscriber')) : sprintf(lang('update_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Subscribers->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Subscribers->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Subscribers->defaultBy()]) {
                $this->Subscribers->unset_default(null);
                $results['status'] = $this->Subscribers->set_default(['id' => $obj['id']]);
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'),  lang('subscriber')) : sprintf(lang('update_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Subscribers->delete($id);
            if (!empty($results['status'])) add_activity('Subscriber', 'A subscriber deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'),  lang('subscriber')) : sprintf(lang('delete_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Subscribers->save([$this->Subscribers->deletedAt() => false, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) add_activity('Subscriber', 'A subscriber restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('subscriber')) : sprintf(lang('update_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Subscribers->delete($id, true);
            if (!empty($results['status']))  add_activity('Subscriber', 'A subscriber deleted permanently from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'),  lang('subscriber')) : sprintf(lang('delete_failed_msg'),  lang('subscriber'));
            return $this->response($results);
        } else return $this->response(['status' => false, 'message' => 'invalid request']);
    }

    public function broadcast_post()
    {
        if (!empty($this->post('message'))) {
            $this->db->trans_start();
            $entity['subject'] = $this->post('subject');
            $entity['message'] = $this->post('message');
            $entity['recgroup'] = 'subscribers';
            if (!empty($this->post('sms'))) {
                $entity['sms'] = 1;
                $phones = $this->Subscribers->get(['status' => 'active', 'phone<>' => ''], false, false);
                if (!empty($phones)) $this->Broadcastmsgs->sms($phones, $entity['message']);
            }
            if (!empty($this->post('email'))) {
                $entity['email'] = 1;
                $emails = $this->Subscribers->get(['status' => 'active', 'email<>' => ''], false, false);
                if (!empty($emails)) $this->Broadcastmsgs->email($emails, $entity['subject'], $entity['message']);
            }
            $this->Broadcastmsgs->save($entity);
            $this->db->trans_complete();
            return $this->response(['status' => $this->db->trans_status()]);
        } else  return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
}
