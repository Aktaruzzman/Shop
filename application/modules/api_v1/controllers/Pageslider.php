<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Pageslider extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Pagesliders');
            $this->load->model('Uploader');
        }
    }
    public function index_get($id = null, $edit = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Pagesliders->get($id, true);
            if ($results['entity']) {
                $results['entity']->title = $this->Pagesliders->decode($results['entity']->title);
                $results['entity']->photos['full'] = !empty($results['entity']->photo) ? site_url('/uploads/slider' . $results['entity']->photo) : './img/no-image.png';
                $results['entity']->photos['thumb'] = !empty($results['entity']->thumb) ? site_url('/uploads/slider/thumbnail/' . $results['entity']->thumb) : './img/no-image.png';
            }
        } else {
            $conditions = null;
            $results['list'] = $this->Pagesliders->get_by(null, false);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->title = $this->Pagesliders->decode($obj->title);
                $results['list'][$index]->photos['full'] = !empty($obj->photo) ? site_url('/uploads/slider/' . $obj->photo) : './img/no-image.png';
                $results['list'][$index]->photos['thumb'] = !empty($obj->thumb) ? site_url('/uploads/slider/thumbnail/' . $obj->thumb) : './img/no-image.png';
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
            }
        }
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->Pagesliders->entity($this->post());
        $entity['title'] = $this->Pagesliders->encode(['en' => $entity['title_en'], 'bn' => $entity['title_bn']]);
        $entity['subtitle'] = $this->Pagesliders->encode(['en' => $entity['subtitle_en'], 'bn' => $entity['subtitle_bn']]);
        $entity['message'] = $this->Pagesliders->encode(['en' => $entity['message_en'], 'bn' => $entity['message_bn']]);
        if (!empty($entity['id'])) {
            $results['status'] = $this->Pagesliders->save($entity, $entity['id']);
            if (!empty($results['status'])) add_activity('Slider Photo', 'A Slider page photo updated');
        } else {
            $results['status'] = $this->Pagesliders->save($entity);
            if (!empty($results['status'])) add_activity('Slider Photo', 'A new Slider page photo added');
        }
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status() && $results['status'];
        $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('saved_failed_msg'), lang('web') . ' ' . lang('page'));
        return $this->response($results);
    }

    public function photo_post()
    {
        if ($_FILES) {
            $config = array('upload_path' => APPPATH . '../uploads/slider', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'max_size' => 0, 'max_width' => 0, 'max_height' => 0, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no-image';
            if ($upload_status) {
                $saving['thumb'] = $saving['photo'];
                $this->Uploader->create_thumbnail(APPPATH . '../uploads/slider/' . $upload_data['file_name'], APPPATH . '../uploads/slider/thumbnail/' . $upload_data['file_name'], 175, 175);
                $this->Pagesliders->save($saving);
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
            $entity = $this->Pagesliders->entity($this->put());
            $results['status'] = $this->Pagesliders->save($entity, $id);
            if (!empty($results['status'])) add_activity('Slider Photo', 'A Slider photo updated');
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
            $results['status'] = $this->Pagesliders->save($this->patch(), $id);
            if (!empty($results['status'])) add_activity('Slider Photo', 'A Slider photo updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('update_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function trash_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $previous = $this->Pagesliders->get($id, true);
            $this->Uploader->unlink_image(APPPATH . "../uploads/slider/", $previous->photo);
            $this->Uploader->unlink_image(APPPATH . "../uploads/slider/thumbnail/", $previous->photo);
            $results['status'] = $this->Pagesliders->delete($id, true);
            if (!empty($results['status'])) add_activity('Slider Photo', 'A Slider page photo deleted peremanently');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('delete_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
