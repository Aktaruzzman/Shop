<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodfreeplan extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodfreeplans');
            $this->load->model('Cfgservices');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodfreeplans->get($id, TRUE, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = $this->Prodfreeplans->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
                if ($id == $obj->id)  $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Prodfreeplans->count_rows(NULL, $deleted);
        }
        if (!empty($config)) $results = $this->build_config($results);
        return $this->response($results);
    }
    private function build_entity($entity)
    {
        if (!empty($entity)) $entity->name = json_decode($entity->name);
        return $entity;
    }
    private function build_config($results)
    {
        $results['services'] = $this->Cfgservices->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'value', 'name', 'name_en', 'name_bn', 'default']);
        foreach ($results['services'] as $index => $obj) {
            $results['services'][$index]->name = json_decode($obj->name);
            if ($obj->value === 'table') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->table : 1;
            if ($obj->value === 'waiting') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->waiting : 0;
            if ($obj->value === 'collection') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->collection : 0;
            if ($obj->value === 'delivery') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->delivery : 0;
            if ($obj->value === 'bar') $results['services'][$index]->radio =  !empty($results['entity']) ?  $results['entity']->bar : 0;
        }
        return $results;
    }

    public function index_post()
    {
        if ($this->Prodfreeplans->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodfreeplans->entity($this->post());
            $entity['table'] = !empty($entity['table']) ? $entity['table'] : 0;
            $entity['waiting'] = !empty($entity['waiting']) ? $entity['waiting'] : 0;
            $entity['collection'] = !empty($entity['collection']) ? $entity['collection'] : 0;
            $entity['delivery'] = !empty($entity['delivery']) ? $entity['delivery'] : 0;
            $entity['bar'] = !empty($entity['bar']) ? $entity['bar'] : 0;
            $entity['Sun'] = !empty($entity['Sun']) ? $entity['Sun'] : 0;
            $entity['Mon'] = !empty($entity['Mon']) ? $entity['Mon'] : 0;
            $entity['Tue'] = !empty($entity['Tue']) ? $entity['Tue'] : 0;
            $entity['Wed'] = !empty($entity['Wed']) ? $entity['Wed'] : 0;
            $entity['Thu'] = !empty($entity['Thu']) ? $entity['Thu'] : 0;
            $entity['Fri'] = !empty($entity['Fri']) ? $entity['Fri'] : 0;
            $entity['Sat'] = !empty($entity['Sat']) ? $entity['Sat'] : 0;
            $entity['pos'] = !empty($entity['pos']) ? true : false;
            $entity['web'] = !empty($entity['web']) ? true : false;
            $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Prodfreeplans->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Prodfreeplans->get($entity['id'], TRUE)->order;
                    if (intval($old_order) !== $entity[$order_by]) $this->Prodfreeplans->sort($entity['id'], $old_order, $entity[$order_by]);
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Prodfreeplans->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('Free offer plan', 'A  free item offer plan updated');
            } else {
                if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->Prodfreeplans->get_max($order_by) + 1;
                $results['status'] = $id = $this->Prodfreeplans->save($entity);
                if (!empty($results['status'])) add_activity('Free offer plan', 'A new free item plan created');
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
            $entity = $this->Prodfreeplans->entity($this->put());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Prodfreeplans->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodfreeplans->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) $this->Prodfreeplans->sort($id, $old_order, $entity[$order_by]);
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodfreeplans->save($entity, $id);
            if (!empty($results['status'])) add_activity('Free offer plan', 'A free item plan updated');
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
            $entity = $this->Prodfreeplans->entity($this->patch());
            if (array_key_exists('name_en', $entity) && array_key_exists('name_bn', $entity)) {
                $entity['name_en'] = strtolower($entity['name_en']);
                $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            }
            $order_by = $this->Prodfreeplans->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodfreeplans->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) $this->Prodfreeplans->sort($id, $old_order, $entity[$order_by]);
                unset($entity[$order_by]);
            }
            $results['status'] = $this->Prodfreeplans->save($entity, $id);
            if (!empty($results['status'])) add_activity('Free offer plan', 'A free item plan updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Prodfreeplans->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodfreeplans->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Prodfreeplans->defaultBy()]) {
                $this->Prodfreeplans->unset_default(NULL);
                $results['status'] = $this->Prodfreeplans->set_default(['id' => $obj['id']]);
                add_activity('Free offer plan', 'A free item plan updated');
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
            $this->db->trans_start();
            $results['status'] = $this->Prodfreeplans->delete($id);
            if (!empty($results['status'])) add_activity('Free offer plan', 'A free item plan deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodfreeplans->save([$this->Prodfreeplans->deletedAt() => FALSE, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) add_activity('Free offer plan', 'A free item plan restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodfreeplans->delete($id, TRUE);
            if (!empty($results['status'])) add_activity('Free offer plan', 'A free item plan deleted permanently from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('delete_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
    }
}
