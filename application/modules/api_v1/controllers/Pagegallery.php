<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Pagegallery extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Pagegalleries');
            $this->load->model('Uploader');
        }
    }
    public function index_get($id = null, $edit = null, $year = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Pagegalleries->get($id, true);
            if ($results['entity']) {
                $results['entity']->title = $this->Pagegalleries->decode($results['entity']->title);
                $results['entity']->photos['full'] = !empty($results['entity']->photo) ? site_url('/uploads/page/gallery' . $results['entity']->photo) : './img/no-image.png';
                $results['entity']->photos['thumb'] = !empty($results['entity']->thumb) ? site_url('/uploads/page/gallery/thumbnail/' . $results['entity']->thumb) : './img/no-image.png';
            }
        } else {
            $conditions = null;
            if ($year && is_numeric($year)) $conditions['year'] = $year;
            $results['list'] = $this->Pagegalleries->get_by($conditions, false);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->title = $this->Pagegalleries->decode($obj->title);
                $results['list'][$index]->photos['full'] = !empty($obj->photo) ? site_url('/uploads/page/gallery/' . $obj->photo) : './img/no-image.png';
                $results['list'][$index]->photos['thumb'] = !empty($obj->thumb) ? site_url('/uploads/page/gallery/thumbnail/' . $obj->thumb) : './img/no-image.png';
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
            }
        }
        $year_range = range(2019, date('Y'));
        $results['years'] = array_combine($year_range, $year_range);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Pagegalleries->validate()) {
            $this->db->trans_start();
            $entity = $this->Pagegalleries->entity($this->post());
            if (array_key_exists('title_en', $entity) && array_key_exists('title_bn', $entity)) {
                $entity['title_en'] = strtolower($entity['title_en']);
                $entity['title'] = json_encode(array('en' => $entity['title_en'], 'bn' => $entity['title_bn']), JSON_UNESCAPED_UNICODE);
            }
            if (!empty($entity['id'])) {
                $results['status'] = $this->Pagegalleries->save($entity, $entity['id']);
                if (!empty($results['status'])) add_activity('Gallery Photo', 'A gallery page photo updated');
            } else {
                $entity['year'] = date('Y');
                $results['status'] = $this->Pagegalleries->save($entity);
                if (!empty($results['status'])) add_activity('Gallery Photo', 'A new gallery page photo added');
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
            $config = array('upload_path' => APPPATH . '../uploads/page/gallery', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'max_size' => 0, 'max_width' => 0, 'max_height' => 0, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no-image';
            if ($upload_status) {
                $saving['thumb'] = $saving['photo'];
                $this->Uploader->resize_image(APPPATH . '../uploads/page/gallery/' . $upload_data['file_name'], 1080, true);
                $this->Uploader->create_thumbnail(APPPATH . '../uploads/page/gallery/' . $upload_data['file_name'], APPPATH . '../uploads/page/gallery/thumbnail/' . $upload_data['file_name'], 175, 175);
                $previous = $this->Pagegalleries->get($id);
                $this->Pagegalleries->save($saving, $previous->id);
                $this->Uploader->unlink_image(APPPATH . "../uploads/page/gallery/", $previous->photo);
                $this->Uploader->unlink_image(APPPATH . "../uploads/page/gallery/thumbnail/", $previous->thumb);
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
            $entity = $this->Pagegalleries->entity($this->put());
            $results['status'] = $this->Pagegalleries->save($entity, $id);
            if (!empty($results['status'])) add_activity('Gallery Photo', 'A gallery photo updated');
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
            $results['status'] = $this->Pagegalleries->save($this->patch(), $id);
            if (!empty($results['status'])) add_activity('Gallery Photo', 'A gallery photo updated');
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
            $previous = $this->Pagegalleries->get($id, true);
            $this->Uploader->unlink_image(APPPATH . "../uploads/page/gallery/", $previous->photo);
            $this->Uploader->unlink_image(APPPATH . "../uploads/page/gallery/thumbnail/", $previous->photo);
            $results['status'] = $this->Pagegalleries->delete($id, true);
            if (!empty($results['status'])) add_activity('Gallery Photo', 'A gallery page photo deleted peremanently');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('web') . ' ' . lang('page')) : sprintf(lang('delete_failed_msg'), lang('web') . ' ' . lang('page'));
            return $this->response($results);
        } else {
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
    }
}
