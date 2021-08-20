<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Blog extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Blogs');
            $this->load->model('Uploader');
        }
    }
    public function index_get($id = null, $edit = null, $deleted = null, $limit = null, $offset = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = is_numeric(($id)) ? $this->Blogs->get($id, true, $deleted) : $this->Blogs->get_by(['slug' => $id], true, $deleted);
            if ($results['entity']) $results['entity'] = $this->Blogs->build_entity($results['entity']);
        } else {
            $results['list'] = $this->Blogs->get_by(null, false, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Blogs->build_entity($obj);
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
            }
            $results['total'] = $this->Blogs->count_rows(null, $deleted);
        }
        return $this->response($results);
    }

    public function index_post()
    {

        if ($this->Blogs->validate()) {
            $this->db->trans_start();
            $entity = $this->Blogs->arrange_entity($this->Blogs->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Blogs->save($entity, $entity['id']);
                if (!empty($results['status'])) {
                    add_sync($this->Blogs->getTable(), 'update', $entity['id']);
                    add_activity('Website post', 'A web site post updated');
                }
            } else {
                $results['status'] = $id = $this->Blogs->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Blogs->getTable(), 'create', $id);
                    add_activity('Website post', 'A web site post created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('saved_failed_msg'), lang('blog') . ' ' . lang('post'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }



    public function photo_post($id)
    {
        if ($_FILES) {
            $config = array('upload_path' => APPPATH . '../uploads/blog', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'max_size' => 0, 'max_width' => 0, 'max_height' => 0, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no-image';
            if ($upload_status) {
                $saving['thumb'] = $saving['photo'];
                $this->Uploader->resize_image(APPPATH . '../uploads/blog/' . $upload_data['file_name'], 1080, true);
                $this->Uploader->create_thumbnail(APPPATH . '../uploads/blog/' . $upload_data['file_name'], APPPATH . '../uploads/blog/thumbnail/' . $upload_data['file_name'], 175, 150);
                $previous = $this->Blogs->get($id);
                $this->Blogs->save($saving, $previous->id);
                add_sync($this->Blogs->getTable(), 'update', $id);
                add_activity('Website post iamge', 'A web site post image changed');
                $this->Uploader->unlink_image(APPPATH . "../uploads/blog/", $previous->photo);
                $this->Uploader->unlink_image(APPPATH . "../uploads/blog/thumbnail/", $previous->thumb);
            }
            $data['errors'] = $this->upload->display_errors();
            $data['file'] = $saving['photo'];
            $data['message'] = empty(trim($data['errors'])) ? sprintf(lang('option_succeeded'), lang('image') . ' ' . lang('upload')) : sprintf(lang('option_failed'), lang('image') . ' ' . lang('upload'));
            return $this->response($data);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }

    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Blogs->entity($this->put());
            $results['status'] = $this->Blogs->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Blogs->getTable(), 'update', $id);
                add_activity('Website post', 'A website post updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('update_failed_msg'), lang('blog') . ' ' . lang('post'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Blogs->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Blogs->getTable(), 'update', $id);
                add_activity('Website post', 'A website post updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('update_failed_msg'), lang('blog') . ' ' . lang('post'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = null)
    {
        if (!empty($id) && !empty($this->Blogs->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Blogs->get($id, true);
            $results['status'] = true;
            if (!empty($obj) && !$obj[$this->Blogs->defaultBy()]) {
                $this->Blogs->unset_default(null);
                $results['status'] = $this->Blogs->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Blogs->getTable(), 'update', $id);
                    add_activity('Website post', 'A website post default setting updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('update_failed_msg'), lang('blog') . ' ' . lang('post'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $obj = $this->Blogs->get($id, true);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                $results['status'] = $this->Blogs->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Blogs->getTable(), 'delete', $id);
                    add_activity('Website post', 'A website post sent into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() && $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('delete_failed_msg'), lang('blog') . ' ' . lang('post'));
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
            $results['status'] = $this->Blogs->save([$this->Blogs->deletedAt() => false, 'status' => 'inactive'], $id);
            if (!empty($results['status'])) {
                add_sync($this->Blogs->getTable(), 'update', $id);
                add_activity('Website post', 'A website post broguht to live from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('update_failed_msg'), lang('blog') . ' ' . lang('post'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $results['status'] = $this->Blogs->delete($id, true);
            if (!empty($results['status'])) {
                add_sync($this->Blogs->getTable(), 'trash', $id);
                add_activity('Blog', 'A post deleted peremanently from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('blog') . ' ' . lang('post')) : sprintf(lang('delete_failed_msg'), lang('blog') . ' ' . lang('post'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
