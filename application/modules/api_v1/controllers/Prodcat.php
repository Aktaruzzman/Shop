<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodcat extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodbases');
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Uploader');
            $this->load->model('Stockdefines');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $base = null, $deleted = null, $limit = null, $offset = null, $status = null, $show_in = null, $show_out = null, $category_sort_by = 'base')
    {
        if (!empty($id) && empty($edit)) {
            $entity = $this->Prodcats->get($id, true, $deleted);
            $results['entity'] = $this->Prodcats->build_entity($entity, $config);
        } else {
            $conditions = [];
            if (!empty($status)) $conditions['status'] = 'active';
            if (!empty($show_in)) $conditions['show_in'] = true;
            if (!empty($show_out)) $conditions['show_out'] = true;
            if (!empty($config))  $results = $this->Prodcats->build_config($base);

            if ($category_sort_by === 'base') {
                if (empty($config) && empty($base)) $base = $this->Prodbases->get_by(['default' => true], true)->id;
                if (!empty($base)) $conditions['base_id'] = $base;
            } else {
                if ($base == 'food' || $base == 'nonfood') $conditions['food_type'] = $base;
            }

            if (empty($conditions)) $conditions = null;

            $results['rows'] = $this->Prodcats->count_rows($conditions, $deleted);
            $results['list'] = $this->Prodcats->get_by($conditions, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Prodcats->build_entity($obj);
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
                $results['list'][$index]->items = $this->Proditems->count_rows(['category_id' => $obj->id]);
            }
        }
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Prodcats->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodcats->arrange_entity($this->Prodcats->entity($this->post()));
            if (!empty($entity['id'])) {
                $obj = $this->Prodcats->get($entity['id'], true, true);
                if ($obj->status !== $entity['status']) {
                    $this->Prodcats->co_update($entity['id'], 'status', ['status' => $entity['status']]);
                    $this->Prodoptions->save_by(['status' => $entity['status']], ['category_id' => $entity['id']]);
                    $this->Proditems->save_by(['status' => $entity['status']], ['category_id' => $entity['id']]);
                }
                if ($obj->base_id !== $entity['base_id']) {
                    $this->Proditems->save_by(['base_id' => $entity['base_id']], ['category_id' => $entity['id']]);
                    $this->Stockdefines->save_by(['base_id' => $entity['base_id']], ['category_id' => $entity['id']]);
                }
                $results['status'] = $this->Prodcats->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Prodbases->getTable(), 'update',  $entity['id']);
                    add_activity('Product category', 'A product category updated');
                }
            } else {
                $results['status'] = $id = $this->Prodcats->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Prodbases->getTable(), 'update',  $id);
                    add_activity('Product category', 'A product category updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('saved_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodcats->entity($this->put());
            if (array_key_exists('status', $entity)) {
                $this->Prodcats->co_update($id, 'status', ['status' => $entity['status']]);
                $this->Prodoptions->save_by(['status' => $entity['status']], ['category_id' => $id]);
                $this->Proditems->save_by(['status' => $entity['status']], ['category_id' => $id]);
            }
            $results['status'] = $this->Prodcats->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodbases->getTable(), 'update',  $id);
                add_activity('Product category', 'A product category updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodcats->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodbases->getTable(), 'update',  $id);
                add_activity('Product category', 'A product category updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('shop') . ' ' . lang('service')) : sprintf(lang('update_failed_msg'), lang('shop') . ' ' . lang('service'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Prodcats->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Prodcats->get($id, true);
            if (!empty($obj) && !$obj[$this->Prodcats->defaultBy()]) {
                $this->Prodcats->unset_default(['base_id' => $obj['base_id']]);
                $this->Prodcats->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Prodbases->getTable(), 'update',  $id);
                    add_activity('Product category', 'Default product category changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $obj = $this->Prodcats->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $this->Prodcats->co_update($id, 'delete');
                $this->Prodoptions->delete_by(['base_id' => $id]);
                $this->Proditems->delete_by(['base_id' => $id]);
                $results['status'] = $this->Prodcats->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Prodcats->getTable(), 'delete', $id);
                    add_activity('Product category', 'A product category deleted from bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
                return $this->response($results);
            } else {
                return $this->response(['status' => false, 'message' => 'invalid request']);
            }
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Prodcats->co_update($id, 'undo');
            $this->Prodoptions->save_by([$this->Prodoptions->deletedAt() => false], ['base_id' => $id]);
            $this->Proditems->save_by([$this->Proditems->deletedAt() => false], ['base_id' => $id]);
            $results['status'] = $this->Prodcats->save([$this->Prodcats->deletedAt() => false], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodbases->getTable(), 'update',  $id);
                add_activity('Product category', 'A product category restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('area')) : sprintf(lang('update_failed_msg'), lang('area'));

            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Prodcats->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Prodbases->getTable(), 'trash',  $id);
                add_activity('Product category', 'A product category deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
    public function photo_post($id)
    {
        if ($_FILES) {
            $config = array('upload_path' => APPPATH . '../uploads/product/category', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'max_size' => 0, 'max_width' => 0, 'max_height' => 0, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no-image';
            if ($upload_status) {
                $saving['thumb'] = $saving['photo'];
                $this->Uploader->resize_image(APPPATH . '../uploads/product/category/' . $upload_data['file_name'], 270, true, 250);
                $this->Uploader->create_thumbnail(APPPATH . '../uploads/product/category/' . $upload_data['file_name'], APPPATH . '../uploads/product/category/thumbnail/' . $upload_data['file_name'], 150, 130);
                $product = $this->Prodcats->get($id);
                $this->Prodcats->save($saving, $product->id);
                add_sync($this->Prodbases->getTable(), 'update',  $id);
                $this->Uploader->unlink_image(APPPATH . "../uploads/product/category/", $product->photo);
                $this->Uploader->unlink_image(APPPATH . "../uploads/product/category/thumbnail/", $product->photo);
            }
            $data['errors'] = $this->upload->display_errors();
            $data['file'] = $saving['photo'];
            return $this->response($data);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
