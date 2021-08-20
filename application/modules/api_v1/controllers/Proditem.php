<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Proditem extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Uploader');
        }
    }
    public function index_get($id = null, $config = null, $edit = null, $base = null, $deleted = null, $limit = null, $offset = null, $status = null, $show_in = null, $show_out = null, $supplier = null, $search = null, $hub_id = null)
    {
        if (!empty($config)) {
            $results = $this->Proditems->build_config($base);
            if (empty($results['base']) && !empty($results['categories'])) {
                $results['base'] = $results['categories'][0];
                $base = $results['categories'][0]->id;
            }
        }
        if (!empty($id) && empty($edit)) {
            $entity = $this->Proditems->get($id, true, $deleted);
            $entity->edit = false;
            $results['entity'] = $this->Proditems->build_entity($entity);
        } else {
            if (empty($results['base'])) {
                $results['base'] = $this->Prodcats->get($base, true, true, ['id', 'base_id', 'name', 'default', 'status', 'unit_id', 'color', 'background']);
                if (!empty($results['base'])) $results['base']->name = $this->Prodcats->decode($results['base']->name);
            }
            $conditions = [];
            if (!empty($base)) $conditions['category_id'] = $base;
            if (!empty($status)) $conditions['status'] = 'active';
            if (!empty($show_in)) $conditions['show_in'] = true;
            if (!empty($show_out)) $conditions['show_out'] = true;
            if (!empty($supplier) && intval($supplier) > 0) $conditions['supplier_id'] = $supplier;
            if (empty($conditions))                 $conditions = null;
            if (!empty($search)) {
                unset($conditions['category_id']);
                $search = str_replace(array('-', '_', ',', '+'), array(' ', ' ', ' ', ' '), filter_var($search, FILTER_SANITIZE_SPECIAL_CHARS));
                $results['list'] = $this->Proditems->get_by($conditions, false, $deleted, $limit, $offset, trim($search), ['name_en', 'name_bn', 'description_en', 'description_bn']);
                $results['rows'] = count($results['list']);
            } else {
                $results['rows'] = $this->Proditems->count_rows($conditions, $deleted);
                $results['list'] = $this->Proditems->get_by($conditions, false, $deleted, $limit, $offset);
            }
            foreach ($results['list'] as $index => $obj) {
                $obj->edit = !empty($edit) ||  !empty($config);
                $results['list'][$index] = $this->Proditems->build_entity($obj, $hub_id);
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
            }
        }
        if (empty($results['base'])) unset($results['base']);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Proditems->validate()) {
            $this->db->trans_start();
            $entity = $this->Proditems->arrange_entity($this->Proditems->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Proditems->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Proditems->getTable(), 'update', $entity['id']);
                    add_activity('Product Item', 'A product item updated');
                }
            } else {
                $results['status'] = $id = $this->Proditems->save($entity);
                $results['defines'] = $this->Proditems->define_stock($id, $entity);
                if (!empty($results['status'])) {
                    add_sync($this->Proditems->getTable(), 'create', $id);
                    add_activity('Product Item', 'A new product item created');
                }
                $results['id'] = $id;
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('item')) : sprintf(lang('saved_failed_msg'), lang('item'));
            if (!empty($results['defines']) && !empty($results['id']))
                $this->Proditems->save($results['defines'], $results['id']);
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }

    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Proditems->entity($this->put());
            if (array_key_exists('status', $entity)) {
                $this->Proditems->co_update($id, 'status', ['status' => $entity['status']]);
                $this->Prodoptions->save_by(['status' => $entity['status']], ['item_id' => $id]);
            }
            $results['status'] = $this->Proditems->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Proditems->getTable(), 'update', $id);
                add_activity('Product Item', 'A product item updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Proditems->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Proditems->getTable(), 'update', $id);
                add_activity('Product Item', 'A product item updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item')) : sprintf(lang('update_failed_msg'), lang('item'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Proditems->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Proditems->get($id, true);
            if (!empty($obj) && !$obj[$this->Proditems->defaultBy()]) {
                $this->Proditems->unset_default(['base_id' => $obj['base_id']]);
                $this->Proditems->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Proditems->getTable(), 'update', $id);
                    add_activity('Product Item', 'A default product item changed');
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
            $this->db->trans_start();
            $obj = $this->Proditems->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->Proditems->co_update($id, 'delete');
                $this->Prodoptions->delete_by(['item_id' => $id]);
                $this->Proditems->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Proditems->getTable(), 'delete', $id);
                    add_activity('Product Item', 'A product item deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status();
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
                return $this->response($results);
            }
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Proditems->co_update($id, 'undo');
            $this->Prodoptions->save_by([$this->Prodoptions->deletedAt() => false], ['item_id' => $id]);
            $this->Proditems->save([$this->Proditems->deletedAt() => false], $id);
            if (!empty($results['status'])) {
                add_sync($this->Proditems->getTable(), 'update', $id);
                add_activity('Product Item', 'A product item restored from bin');
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
            $this->Proditems->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Proditems->getTable(), 'trash', $id);
                add_activity('Product Item', 'A product item deleted permanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }

    public function photo_post()
    {
        if ($_FILES) {
            $item_id = $this->post('item_id');
            $option_id = $this->post('option_id');
            $data['product'] = $product = !empty($option_id) && (int) $option_id > 0 ? $this->Prodoptions->get($option_id, true) : $this->Proditems->get($item_id, true);
            if (!empty($product)) {
                $config = array('upload_path' => APPPATH . '../uploads/product/item', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
                $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
                $upload_data = $this->upload->data();
                $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no_image';
                if ($upload_status) {
                    $saving['thumb'] = $saving['photo'];
                    $this->Uploader->resize_image(APPPATH . '../uploads/product/item/' . $upload_data['file_name'], 270, true, 250);
                    $this->Uploader->create_thumbnail(APPPATH . '../uploads/product/item/' . $upload_data['file_name'], APPPATH . '../uploads/product/item/thumbnail/' . $upload_data['file_name'], 120, 120);
                    $data['status'] = !empty($option_id) && (int) $option_id > 0 ? $this->Prodoptions->save($saving, $product->id) : $this->Proditems->save($saving, $product->id);
                    $this->Uploader->unlink_image(APPPATH . "../uploads/product/item/", $product->photo);
                    $this->Uploader->unlink_image(APPPATH . "../uploads/product/item/thumbnail/", $product->photo);
                }
                $data['errors'] = $this->upload->display_errors();
                $data['file'] = $saving['photo'];
            }
            return $this->response($data);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
