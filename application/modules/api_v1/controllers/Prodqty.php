<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodqty extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Prodcodes');
        }
    }

    public function index_get($config = null, $base = null, $deleted = null, $limit = null, $offset = null)
    {
        if (!empty($config)) {
            $results = $this->build_config($base);
            if (empty($base)) {
                $base = $results['base']->id;
            }
        }
        $results['list'] = !empty($base) ? $this->Prodcodes->get_by(['category_id' => $base], false, $deleted, $limit, $offset, null, ['id', 'category_id', 'item_id', 'option_id']) : $this->Prodcodes->get_by(null, false, $deleted, $limit, $offset, null, ['category_id', 'item_id', 'option_id']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total'] = !empty($base) ? $this->Prodcodes->count_rows(['category_id' => $base], $deleted) : $this->Prodcodes->count_rows(null, $deleted);
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->post();
        if (!empty($entity['option_id'])) {
            $results['status'] = $this->Prodoptions->save(['qty_lower' => $entity['qty_lower'], 'qty_step' => $entity['qty_step'], 'qty_upper' => $entity['qty_upper']], $entity['option_id']);
        } else {
            if (!empty($entity['item_id'])) {
                $results['status'] = $this->Proditems->save(['qty_lower' => $entity['qty_lower'], 'qty_step' => $entity['qty_step'], 'qty_upper' => $entity['qty_upper']], $entity['item_id']);
            }
        }
        if (!empty($results['status'])) {
            //add_sync($this->Prodcodes->getTable(), 'update',  $entity['id']);
            add_activity('Product vat', 'A product vat updated');
        }
        $this->db->trans_complete();
        $results['status'] = $this->db->trans_status() && $results['status'];
        $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('item') . ' ' . lang('vat')) : sprintf(lang('saved_failed_msg'), lang('item') . ' ' . lang('vat'));
        return $this->response($results);
    }

    private function build_entity($entity)
    {
        if (!empty($entity)) {
            if (!empty($entity->option_id)) {
                $row = $this->Prodoptions->get($entity->option_id, true, true);
                $row->name = $this->name_entity($row->name);
                $item = $this->Proditems->get($entity->item_id, true);
                $item->name = $this->name_entity($item->name);
                $entity->name = array('en' => $row->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
                $entity->qty_upper = $row->qty_upper;
                $entity->qty_lower = $row->qty_lower;
                $entity->qty_step = $row->qty_step;
                $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
                $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
            } else {
                $row = $this->Proditems->get($entity->item_id, true, true);
                $entity->name = json_decode($row->name);
                $entity->qty_upper = $row->qty_upper;
                $entity->qty_lower = $row->qty_lower;
                $entity->qty_step = $row->qty_step;
                $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
                $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
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
        $results['categories'] = $this->Prodcats->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'name']);
        foreach ($results['categories'] as $index => $obj) {
            $results['categories'][$index]->name = $this->name_entity($obj->name);
        }
        if (empty($category) && !empty($results['categories'])) {
            $category = $results['categories'][0]->id;
        }

        $results['base'] = $this->Prodcats->get($category, true);
        if ($results['base']) {
            $results['base']->name = $this->name_entity($results['base']->name);
        }

        return $results;
    }
}
