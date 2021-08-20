<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Cfgconfig extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgconfigs');
            $this->load->model('Uploader');
            $this->load->model('Geoareas');
            $this->load->model('Prodbases');
        }
    }
    private function __get_configs()
    {
        $results['areas'] = $this->Geoareas->formatted();
        $results['config'] = $this->Cfgconfigs->get_all();
        $area = $this->Geoareas->formatted((int) $results['config']['store_area_id']);
        $results['config']['store_area'] = $area->formatted;
        $bases = $this->Prodbases->get_by(['status' => 'active'], false);
        foreach ($bases as $key => $v) {
            $results['config'][$v->value . '_ip'] = $v->ip;
        }
        return $results;
    }
    public function index_get($key = null)
    {
        if (!empty($key)) $results[$key] = $this->Cfgconfigs->get($key);
        else   $results = $this->Cfgconfigs->setup();
        $this->response($results);
    }
    public function index_post()
    {
        $this->db->trans_start();
        $this->Cfgconfigs->batch_save($this->post());
        add_sync($this->Cfgservices->getTable(), 'update', 0);
        add_activity('Configuration', 'System comfiguration updated');
        $this->db->trans_complete();

        $this->response($this->__get_configs());
    }

    public function logo_post()
    {
        if ($_FILES) {
            $config = array('upload_path' => APPPATH . '../uploads', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no_image';
            if ($upload_status) {
                $this->Uploader->resize_image(APPPATH . '../uploads/' . $upload_data['file_name'], 150, false, 70);
                $shop_logo = $this->Cfgconfigs->get('shop_logo');
                if (!empty($shop_logo)) $this->Uploader->unlink_image(APPPATH . "../uploads/", $shop_logo);
                $this->Cfgconfigs->save('shop_logo', $saving['photo']);
            }
            $data['errors'] = $this->upload->display_errors();
            $data['file'] = $saving['photo'];
            $this->response($data['status'] ? $this->__get_configs() : $data);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }

    public function bodybg_post()
    {
        if ($_FILES) {
            $config = array('upload_path' => APPPATH . '../uploads', 'allowed_types' => 'gif|jpg|png|jpeg', 'file_ext_tolower' => true, 'file_name' => date('YmdHms') . '-' . rand(1, 999999));
            $data['status'] = $upload_status = $this->Uploader->upload_image($config, 'photo');
            $upload_data = $this->upload->data();
            $saving['photo'] = $upload_status ? $upload_data['file_name'] : 'no_image';
            if ($upload_status) {
                $shop_logo = $this->Cfgconfigs->get('web_theme_bg_image');
                if (!empty($shop_logo)) $this->Uploader->unlink_image(APPPATH . "../uploads/", $shop_logo);
                $this->Cfgconfigs->save('web_theme_bg_image', $saving['photo']);
            }
            $data['errors'] = $this->upload->display_errors();
            $data['file'] = $saving['photo'];
            $this->response($data['status'] ? $this->__get_configs() : $data);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
