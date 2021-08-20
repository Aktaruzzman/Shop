<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodcode extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Prodcodes');
        }
    }
    /**
     * List all codes with name
     *
     * @param [type] $id
     * @param string $type id/barcode/shortcode
     * @param [type] $base
     * @param [type] $deleted
     * @param [type] $limit
     * @param [type] $offset
     * @return void
     */
    public function index_get($id = NULL, $type = "id", $config = NULL, $edit = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($config)) {
            $results = $this->build_config($base);
            if (empty($base)) $base = $results['base']->id;
        }
        if (!empty($id) && empty($edit)) {
            if ($type == "id")   $entity = $this->Prodcodes->get($id, TRUE, $deleted);
            if ($type == "barcode")   $entity = $this->Prodcodes->get_by(['barcode' => $id], TRUE, $deleted);
            if ($type == "shortcode")   $entity = $this->Prodcodes->get_by(['shortcode' => $id], TRUE, $deleted);
            $results['entity'] = $this->build_entity($entity);
        } else {
            $results['list'] = !empty($base) ? $this->Prodcodes->get_by(['category_id' => $base], FALSE, $deleted, $limit, $offset) : $this->Prodcodes->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->build_entity($obj);
            }
            $results['total'] = !empty($base) ? $this->Prodcodes->count_rows(['category_id' => $base], $deleted) : $this->Prodcodes->count_rows(NULL, $deleted);
        }
        return $this->response($results);
    }

    private function build_entity($entity)
    {
        if (!empty($entity)) {
            if (!empty($entity->option_id)) {
                $row = $this->Prodoptions->get($entity->option_id, TRUE, TRUE);
                $row->name = $this->name_entity($row->name);
                $item = $this->Proditems->get($entity->item_id, TRUE);
                $item->name = $this->name_entity($item->name);
                $entity->name  = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
            } else {
                $row = $this->Proditems->get($entity->item_id, TRUE, TRUE);
                $entity->name = json_decode($row->name);
            }
        }
        return $entity;
    }
    private function name_entity($name)
    {
        return json_decode($name);
    }
    private function build_config($category)
    {
        $results['categories'] = $this->Prodcats->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'name']);
        foreach ($results['categories'] as $index => $obj) {
            $results['categories'][$index]->name = $this->name_entity($obj->name);
        }
        if (empty($category) && !empty($results['categories'])) $category = $results['categories'][0]->id;
        $results['base'] = $this->Prodcats->get($category, TRUE);
        if ($results['base']) $results['base']->name = $this->name_entity($results['base']->name);
        return $results;
    }

    public function search_post()
    {
        $results['criteria'] = $this->post();
        $base = !empty($this->post('base')) ? trim($this->post('base')) : NULL;
        $results['base'] = !empty($base) ? $this->Prodcats->get($base, TRUE) : NULL;
        if ($results['base']) $results['base']->name = json_decode($results['base']->name);
        $results['list'] = !empty($base) ? $this->Prodcodes->get_by(['category_id' => $base], FALSE, TRUE, NULL, NULL, trim($this->post('search')), ['name_en', 'name_bn', 'status']) : $this->Prodcodes->get_by(NULL, FALSE, TRUE, NULL, NULL, trim($this->post('search')),  ['name_en', 'name_bn', 'status']);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function filter_post()
    {
        $results['criteria'] = $criteria = $this->Prodcodes->entity($this->post());
        $results['list'] = $this->Prodcodes->get_by($criteria, FALSE, TRUE, NULL, NULL);
        $results['total']  = count($results['list']);
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Prodcodes->validate()) {
            $this->db->trans_start();
            $entity = $this->Prodcodes->entity($this->post());
            if (!empty($entity['shortcode'])) $entity['shortcode'] = str_pad($entity['shortcode'], 4, "0", STR_PAD_LEFT);
            $results['status'] = $this->Prodcodes->save($entity, $entity['id']);
            if (!empty($results['status'])) {
                add_sync($this->Prodcodes->getTable(), 'update',  $entity['id']);
                add_activity('Product shortcode', 'A product shortcode updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('item') . ' ' . lang('code')) : sprintf(lang('saved_failed_msg'), lang('item') . ' ' . lang('code'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodcodes->entity($this->put());
            $results['status'] = $this->Prodcodes->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcodes->getTable(), 'update',  $id);
                add_activity('Product shortcode', 'A product shortcode updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item') . ' ' . lang('code')) : sprintf(lang('update_failed_msg'), lang('item') . ' ' . lang('code'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Prodcodes->entity($this->patch());
            $results['status'] = $this->Prodcodes->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcodes->getTable(), 'update',  $id);
                add_activity('Product shortcode', 'A product shortcode updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('item') . ' ' . lang('code')) : sprintf(lang('update_failed_msg'), lang('item') . ' ' . lang('code'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Prodcodes->defaultBy())) {
            $this->db->trans_start();
            $obj = (array)$this->Prodcodes->get($id, TRUE);
            if (!empty($obj) && !$obj[$this->Prodcodes->defaultBy()]) {
                $this->Prodcodes->unset_default(['category_id' => $obj['category_id']]);
                $this->Prodcodes->set_default(['id' => $obj['id']]);
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
            $this->db->trans_start();
            $results['status'] = $this->Prodcodes->delete($id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcodes->getTable(), 'update',  $id);
                add_activity('Product shortcode', 'A product shortcode created');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Prodcodes->save([$this->Prodcodes->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Prodcodes->getTable(), 'update',  $id);
                add_activity('Product shortcode', 'A product shortcode restored from bin');
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
            $results['status'] = $this->Prodcodes->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Prodcodes->getTable(), 'trash',  $id);
                add_activity('Product shortcode', 'A product shortcode restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('area')) : sprintf(lang('delete_failed_msg'), lang('area'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
