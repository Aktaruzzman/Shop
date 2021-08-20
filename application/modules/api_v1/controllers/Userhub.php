<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Userhub extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Userhubs');
            $this->load->model('Geoareas');
        }
    }
    public function index_get($id = null, $edit = null, $config = null, $deleted = null, $limit = null, $offset = null, $status = null)
    {
        if (!empty($config)) $results['areas'] = $this->Geoareas->formatted();
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Userhubs->get($id, true, $deleted);
            if ($results['entity']) $results['entity'] = $this->Userhubs->build_entity($results['entity']);
        } else {
            $results['list'] = $this->Userhubs->get_by($status ? ['status' => $status] : null, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Userhubs->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }


    public function index_post()
    {
        if ($this->Userhubs->validate()) {
            $this->db->trans_start();
            $entity = $this->Userhubs->entity($this->post());
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            if (!empty($entity['house_en'])) {
                $entity['house'] = json_encode(array('en' => $entity['house_en'], 'bn' => !empty($entity['house_bn']) ? $entity['house_bn'] : $entity['house_en']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Userhubs->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && 'order' === $order_by) {
                    $old_order = $this->Userhubs->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Userhubs->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $this->Userhubs->save($entity, $entity['id']);
                add_activity('User hub', 'A user hub updated');
            } else {
                if (!empty($order_by) && 'order' === $order_by) {
                    $entity[$order_by] = $this->Userhubs->get_max($order_by) + 1;
                }
                $this->Userhubs->save($entity);
                add_activity('User hub', 'A new user hub created');
            }
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Userhubs->entity($this->put());
            $id     = $this->Userhubs->save($entity, $id);
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
            $results['status'] = $this->Userhubs->save($this->patch(), $id);
            add_activity('User hub', 'A user hub updated');
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Userhubs->defaultBy())) {
            $this->db->trans_start();
            $obj               = (array) $this->Userhubs->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Userhubs->defaultBy()]) {
                $this->Userhubs->unset_default(null);
                $this->Userhubs->set_default(['id' => $obj['id']]);
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
            $obj = $this->Userhubs->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->Userhubs->delete($id);
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
            $this->Userhubs->save([$this->Userhubs->deletedAt() => false], $id);
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
            $this->Userhubs->delete($id, true);
            add_activity('User hub', 'A user hub deleted');
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Userhubs->get(null, false, true);
        foreach ($all as $i => $a) {
            $name              = json_decode($a->name);
            $entity['name_en'] = $name->en;
            $entity['name_bn'] = $name->bn;
            $this->Userhubs->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
