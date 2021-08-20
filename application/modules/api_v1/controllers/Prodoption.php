<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodoption extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodbases');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Cfgtaxes');
            $this->load->model('Cfgdiscounts');
            $this->load->model('Cfgunits');
            $this->load->model('Suppliers');
            $this->load->model('Prodcourses');
            $this->load->model('prodtimings');
            $this->load->model('Stockdefines');
            $this->load->model('Stockentries');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL, $status = NULL, $show_in = NULL, $show_out = NULL, $supplier = NULL, $search = NULL, $hub_id = NULL)
    {
        if (!empty($config)) {
            $results = $this->Prodoptions->build_config($base);
            $results['base'] = $base ? $this->Proditems->get($base, TRUE, TRUE) : NULL;
            if ($results['base']) $results['base']->name = $this->Prodoptions->decode($results['base']->name);
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodoptions->get($id, TRUE, $deleted);
            $entity->edit = false;
            $results['entity'] = $this->Prodoptions->build_entity($entity);
        } else {
            if (empty($results['base'])) {
                $results['base'] = $base ? $this->Proditems->get($base, TRUE, TRUE) : NULL;
                if (!empty($results['base'])) $results['base']->name = $this->Prodoptions->decode($results['base']->name);
            }
            $conditions = [];
            if (!empty($base)) $conditions['item_id'] = $base;
            if (!empty($status)) $conditions['status'] = 'active';
            if (!empty($show_in)) $conditions['show_in'] = TRUE;
            if (!empty($show_out)) $conditions['show_out'] = TRUE;
            if (!empty($supplier) && intval($supplier) > 0) $conditions['supplier_id'] = $supplier;
            if (empty($conditions)) $conditions = NULL;
            if (!empty($search)) {
                $search = str_replace(array('-', '_', ',', '+'), array(' ', ' ', ' ', ' '),  filter_var($search, FILTER_SANITIZE_SPECIAL_CHARS));
                $results['list'] = $this->Prodoptions->get_by($conditions, FALSE, $deleted, $limit, $offset, trim($search),  ['name_en', 'name_bn', 'description_en', 'description_bn']);
                $results['rows'] = count($results['list']);
            } else {
                $results['rows'] = $this->Prodoptions->count_rows($conditions, $deleted);
                $results['list'] = $this->Prodoptions->get_by($conditions, FALSE, $deleted, $limit, $offset);
            }
            foreach ($results['list'] as $index => $obj) {
                $obj->edit = !empty($edit) ||  !empty($config);
                $results['list'][$index] = $this->Prodoptions->build_entity($obj, $hub_id);
                if ($id == $obj->id) $results['entity'] =  $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Prodoptions->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodoptions->arrange_entity($this->Prodoptions->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Prodoptions->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodoptions->getTable(), 'update', $entity['id']);
                    add_activity('Item option', 'A product item option updated');
                }
            } else {
                $results['status'] = $id = $this->Prodoptions->save($entity);
                $results['defines'] = $this->Prodoptions->define_stock($id, $entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodoptions->getTable(), 'create', $id);
                    add_activity('Item option', 'A new product item option created');
                }
                $results['id'] = $id;
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            if (!empty($results['defines']) && !empty($results['id']))  $this->Prodoptions->save($results['defines'], $results['id']);
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodoptions->entity($this->put());
            if (array_key_exists('name_en', $entity) || array_key_exists('name_bn', $entity)) $entity['name'] = json_encode(array('en' => $entity['name_en'], 'bn' => $entity['name_bn']), JSON_UNESCAPED_UNICODE);
            if (array_key_exists('description_en', $entity) || array_key_exists('description_bn', $entity)) $entity['description'] = json_encode(array('en' => $entity['description_en'], 'bn' => $entity['description_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Prodoptions->orderBy();
            if (!empty($entity[$order_by]) && $order_by === "order") {
                $old_order = $this->Prodoptions->get($id, TRUE)->order;
                if (intval($old_order) !== $entity[$order_by]) {
                    $this->Prodoptions->sort($id, $old_order, $entity[$order_by]);
                }
                unset($entity[$order_by]);
            }
            if (array_key_exists('status', $entity)) $this->Prodoptions->co_update($id, 'status', ['status' => $entity['status']]);
            $results['status'] = $this->Prodoptions->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodoptions->getTable(), 'update', $id);
                add_activity('Item option', 'A product item option updated');
            }
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
            $results['status'] = $this->Prodoptions->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodoptions->getTable(), 'update', $id);
                add_activity('Item option', 'A product item option updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Prodoptions->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Prodoptions->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Prodoptions->defaultBy()]) {
                $this->Prodoptions->unset_default(['item_id' => $obj['item_id']]);
                $results['status'] = $this->Prodoptions->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Prodoptions->getTable(), 'update', $id);
                    add_activity('Item option', 'A product item option updated');
                }
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
            $obj = $this->Prodoptions->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $this->Prodoptions->co_update($id, 'delete');
                $results['status'] = $this->Prodoptions->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Prodoptions->getTable(), 'delete', $id);
                    add_activity('Item option', 'An item option deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
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
            $this->Prodoptions->co_update($id, 'undo');
            $results['status'] = $this->Prodoptions->save([$this->Prodoptions->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodoptions->getTable(), 'update', $id);
                add_activity('Item option', 'An item option restored from bin');
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
            $results['status'] = $this->Prodoptions->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Prodoptions->getTable(), 'trash', $id);
                add_activity('Item option', 'An item option deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }

    public function update_get()
    {
        $this->db->trans_start();
        $all = $this->Prodoptions->get(NULL, FALSE, TRUE);
        foreach ($all as $i => $a) {
            $name = json_decode($a->name, TRUE);
            $desription = json_decode($a->description, TRUE);
            //$entity['slug'] = $this->Prodoptions->slug($name['en']);
            $entity['name_en'] = $name['en'];
            $entity['name_bn'] = $name['bn'];
            $entity['description_en'] = $desription['en'];
            $entity['description_bn'] = $desription['bn'];
            $this->Prodoptions->save($entity, $a->id);
        }
        $this->db->trans_complete();
        return $this->response($this->db->trans_status());
    }
}
