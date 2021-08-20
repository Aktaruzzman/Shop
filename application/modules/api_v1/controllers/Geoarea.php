<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Geoarea extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Geodistricts');
            $this->load->model('Geoupazillas');
            $this->load->model('Geoareas');
            $this->load->model('Geodefaults');
            $this->load->model('Userhubs');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Geoareas->get($id, TRUE, $deleted);
            if ($results['entity']) $results['entity']->name = $this->Geoareas->decode($results['entity']->name);
        } else {
            if (!empty($config)) {
                $results['defaults'] = $this->Geodefaults->get_all();
                $results['base'] = !empty($results['defaults']['upazilla']) ? $results['defaults']['upazilla'] : NULL;
            } else {
                $results['base'] = $base ? $this->Geoupazillas->get($base, TRUE, FALSE) : NULL;
                if (!empty($results['base'])) $results['base']->name = $this->Geoareas->decode($results['base']->name);
            }
            $results['list'] = $this->Geoareas->get_areas(!empty($results['base']) ? $results['base']->id : null, null, null);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = $this->Geoareas->decode($obj->name);
                $upazilla = $this->Geoupazillas->get($obj->upazilla_id);
                $results['list'][$index]->upazilla =  $this->Geoareas->decode($upazilla->name);
                $results['list'][$index]->district = $this->Geoareas->decode($this->Geodistricts->get($upazilla->district_id)->name);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Geoareas->validate()) {
            $this->db->trans_start();
            $entity = $this->Geoareas->entity($this->post());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = ucwords($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Geoareas->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Geoareas->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) $this->Geoareas->sort($entity['id'], $old_order, $entity[$order_by]);
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Geoareas->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('area', 'An area data updated');
            } else {
                if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->Geoareas->get_max($order_by) + 1;
                $results['status'] = $id = $this->Geoareas->save($entity);
                if (!empty($results['status'])) add_activity('area', 'An area data added');
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
            $entity = $this->Geoareas->entity($this->put());
            $results['status'] = $this->Geoareas->save($entity, $id);
            if (!empty($results['status'])) add_activity('area', 'An area data updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Geoareas->entity($this->patch());
            $results['status'] = $this->Geoareas->save($entity, $id);
            if (!empty($results['status'])) add_activity('area', 'An area data updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Geoareas->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Geoareas->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Geoareas->defaultBy()]) {
                $this->Geoareas->unset_default(['upazilla_id' => $obj['upazilla_id']]);
                $results['status'] = $this->Geoareas->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) add_activity('area', 'default area changed');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Geoareas->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Geoareas->delete($id);
                if (!empty($results['status'])) add_activity('area', 'An area deleted into bin');
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
                return $this->response($results);
            } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Geoareas->save([$this->Geoareas->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) add_activity('area', 'An area restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Geoareas->delete($id, TRUE);
            if (!empty($results['status'])) add_activity('area', 'An area deleted permanently');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
