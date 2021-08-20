<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Page extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Pages');
            $this->load->model('Uploader');
        }
    }
    public function index_get($id = null, $edit = null, $deleted = null, $limit = null, $offset = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = is_numeric(($id)) ? $this->Pages->get($id, true, $deleted) : $this->Pages->get_by(['slug' => $id], true, $deleted);
            if ($results['entity']) {
                $results['entity']->title = $this->Pages->decode($results['entity']->title);
                $results['entity']->subtitle = $this->Pages->decode($results['entity']->subtitle);
                $results['entity']->description = $this->Pages->decode($results['entity']->description);
                $results['entity']->meta_keys = $this->Pages->decode($results['entity']->meta_keys);
                $results['entity']->meta_desc = $this->Pages->decode($results['entity']->meta_desc);
                $results['entity']->photos['full'] = !empty($results['entity']->photo) ? site_url('/uploads/page/' . $results['entity']->photo) : './img/no-image.png';
                $results['entity']->photos['thumb'] = !empty($results['entity']->thumb) ? site_url('/uploads/page/thumbnail/' . $results['entity']->thumb) : './img/no-image.png';
            }
        } else {
            $results['list'] = $this->Pages->get_by(null, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->title = $this->Pages->decode($obj->title);
                $results['list'][$index]->subtitle = $this->Pages->decode($obj->subtitle);
                $results['list'][$index]->description = $this->Pages->decode($obj->description);
                $results['list'][$index]->meta_keys = $this->Pages->decode($obj->meta_keys);
                $results['list'][$index]->meta_desc = $this->Pages->decode($obj->meta_desc);
                $results['list'][$index]->photos['full'] = !empty($obj->photo) ? site_url('/uploads/page/' . $obj->photo) : './img/no-image.png';
                $results['list'][$index]->photos['thumb'] = !empty($obj->thumb) ? site_url('/uploads/page/thumbnail/' . $obj->thumb) : './img/no-image.png';
                if ($id == $obj->id) {
                    $results['entity'] = $results['list'][$index];
                }
            }
            $results['total'] = $this->Pages->count_rows(null, $deleted);
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Pages->validate()) {
            $this->db->trans_start();
            $entity = $this->Pages->entity($this->post());


            
            if (array_key_exists('title_en', $entity) && array_key_exists('title_bn', $entity)) $entity['title'] = json_encode(array('en' => $entity['title_en'], 'bn' => $entity['title_bn']), JSON_UNESCAPED_UNICODE);
            if (array_key_exists('subtitle_en', $entity) && array_key_exists('subtitle_bn', $entity)) $entity['subtitle'] = json_encode(array('en' => $entity['subtitle_en'], 'bn' => $entity['subtitle_bn']), JSON_UNESCAPED_UNICODE);
            if (array_key_exists('description_en', $entity) && array_key_exists('description_bn', $entity)) $entity['description'] = json_encode(array('en' => $entity['description_en'], 'bn' => $entity['description_en']), JSON_UNESCAPED_UNICODE);
            if (array_key_exists('meta_keys_en', $entity) && array_key_exists('meta_keys_bn', $entity)) $entity['meta_keys'] = json_encode(array('en' => $entity['meta_keys_en'], 'bn' => $entity['meta_keys_bn']), JSON_UNESCAPED_UNICODE);
            if (array_key_exists('meta_desc_en', $entity) && array_key_exists('meta_desc_en', $entity)) $entity['meta_desc'] = json_encode(array('en' => $entity['meta_desc_en'], 'bn' => $entity['meta_desc_bn']), JSON_UNESCAPED_UNICODE);
            $order_by = $this->Pages->orderBy();
            if (!empty($entity['id'])) {
                if (!empty($entity[$order_by]) && $order_by === 'order') {
                    $old_order = $this->Pages->get($entity['id'], true)->order;
                    if (intval($old_order) !== $entity[$order_by]) {
                        $this->Pages->sort($entity['id'], $old_order, $entity[$order_by]);
                    }
                    unset($entity[$order_by]);
                }
                $results['status'] = $this->Pages->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('Page', 'A page updated');
            } else {
                if (!empty($order_by) && $order_by === 'order') $entity[$order_by] = $this->Pages->get_max($order_by) + 1;
                $results['status'] = $id = $this->Pages->save($entity);
                if (!empty($results['status'])) add_activity('Page', 'A new page created');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('saved_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }

    public function photo_post($id)
    {
        if ($_FILES) {
            $config = array('upload_path' => APPPATH . '../uploads/page', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'max_size' => 0, 'max_width' => 0, 'max_height' => 0, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no-image';
            if ($upload_status) {
                $saving['thumb'] = $saving['photo'];
                $this->Uploader->resize_image(APPPATH . '../uploads/page/' . $upload_data['file_name'], 1080, true);
                $this->Uploader->create_thumbnail(APPPATH . '../uploads/page/' . $upload_data['file_name'], APPPATH . '../uploads/page/thumbnail/' . $upload_data['file_name'], 150, 150);
                $previous = $this->Pages->get($id);
                $this->Pages->save($saving, $previous->id);
                $this->Uploader->unlink_image(APPPATH . "../uploads/page/", $previous->photo);
                $this->Uploader->unlink_image(APPPATH . "../uploads/page/thumbnail/", $previous->thumb);
            }
            $data['errors'] = $this->upload->display_errors();
            $data['file'] = $saving['photo'];
            return $this->response($data);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }

    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Pages->entity($this->put());
            $results['status'] = $this->Pages->save($entity, $id);
            if (!empty($results['status'])) {
                add_activity('Page', 'A page updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('update_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Pages->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Pages->getTable(), 'update', $id);
                add_activity('Page', 'A page updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('update_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Pages->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Pages->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Pages->defaultBy()]) {
                $this->Pages->unset_default(null);
                $results['status'] = $this->Pages->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_activity('Page', 'default page changed');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('update_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $obj = $this->Pages->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Pages->delete($id);
                if (!empty($results['status'])) {
                    add_activity('Page', 'A page deleted');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('delete_failed_msg'), lang('web') . ' ' . lang('page'));
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
            $results['status'] = $this->Pages->save([$this->Pages->deletedAt() => false, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) {
                add_activity('Page', 'A page restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('update_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Pages->delete($id, true);
            if (!empty($results['status'])) {
                add_activity('Page', 'A page deleted peremanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('delete_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
