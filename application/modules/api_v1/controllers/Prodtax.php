<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodtax extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Prodcats');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Prodcodes');
            $this->load->model('Cfgtaxes');
        }
    }

    public function index_get($config = NULL, $base = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($config)) {
            $results = $this->build_config($base);
            if (empty($base)) $base = $results['base']->id;
        }
        $results['list'] = !empty($base) ? $this->Prodcodes->get_by(['category_id' => $base], FALSE, $deleted, $limit, $offset, NULL, ['id', 'category_id', 'item_id', 'option_id']) : $this->Prodcodes->get_by(NULL, FALSE, $deleted, $limit, $offset, NULL, ['category_id', 'item_id', 'option_id']);
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index] = $this->build_entity($obj);
        }
        $results['total'] = !empty($base) ? $this->Prodcodes->count_rows(['category_id' => $base], $deleted) : $this->Prodcodes->count_rows(NULL, $deleted);
        return $this->response($results);
    }

    public function index_post()
    {
        $this->db->trans_start();
        $entity = $this->post();
        if (!empty($entity['bulk'])) {
            $results['status'] = $this->Prodoptions->save_by(['tax_id' => $entity['tax_id'], 'tax_by' => $entity['tax_by']], ['id >' => 0]);
            $results['status'] = $this->Proditems->save_by(['tax_id' => $entity['tax_id'], 'tax_by' => $entity['tax_by']], ['id >' => 0]);
        } else {
            if (!empty($entity['option_id'])) {
                $results['status'] = $this->Prodoptions->save(['tax_id' => $entity['tax_id'], 'tax_by' => $entity['tax_by']], $entity['option_id']);
            } else {
                if (!empty($entity['item_id']))   $results['status'] = $this->Proditems->save(['tax_id' => $entity['tax_id'], 'tax_by' => $entity['tax_by']], $entity['item_id']);
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
                $row = $this->Prodoptions->get($entity->option_id, TRUE, TRUE);
                $row->name = $this->name_entity($row->name);
                $item = $this->Proditems->get($entity->item_id, TRUE);
                $item->name = $this->name_entity($item->name);
                $entity->name  = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
                $entity->tax_id = $row->tax_id;
                $entity->tax_by = $row->tax_by;
            } else {
                $row = $this->Proditems->get($entity->item_id, TRUE, TRUE);
                $entity->name = json_decode($row->name);
                $entity->tax_id = $row->tax_id;
                $entity->tax_by = $row->tax_by;
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
        $results['taxes'] = $this->Cfgtaxes->get_by(['status' => 'active'], FALSE, FALSE, NULL, NULL, NULL, ['id', 'value', 'default']);
        return $results;
    }
}
