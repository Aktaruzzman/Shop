<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodcustomset extends REST_Controller
{

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('Prodcustsets');
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodtops');
        $this->load->model('Prodmods');
        $this->load->model('Cfgunits');
        $this->load->model('Stockdefines');
        $this->load->model('Stockentries');
        $this->load->model('Stockhubentries');
    }

    public function index_get($hook_item_id = null, $hook_option_id = null, $hub_id = null)
    {
        $data = [];
        $conditions['status'] = "active";
        if ($hook_item_id) {
            $conditions['hook_item_id'] = $hook_item_id;
            $item = $this->Proditems->get($hook_item_id, true, true);
            $item->name = $this->Proditems->decode($item->name);
            $item->photos['full'] = !empty($item->photo) ? site_url('/uploads/product/item/' . $item->photo) : './img/no-image.png';
            $item->photos['thumb'] = !empty($item->thumb) ? site_url('/uploads/product/item/thumbnail/' . $item->thumb) : './img/no-image.png';
            $data['hook'] = $item;
        }
        if ($hook_option_id) {
            $conditions['hook_option_id'] = $hook_option_id;
            $option = $this->Prodoptions->get($hook_option_id, true, true);
            $option->name = $this->Prodoptions->decode($option->name);
            $data['hook']->name->en = $option->name->en . ' ' . $data['hook']->name->en;
            $data['hook']->name->bn = $option->name->bn . ' ' . $data['hook']->name->bn;
            $data['hook']->photos['full'] = !empty($option->photo) ? site_url('/uploads/product/item/' . $option->photo) : $data['hook']->photos['full'];
            $data['hook']->photos['thumb'] = !empty($option->thumb) ? site_url('/uploads/product/item/' . $option->thumb) : $data['hook']->photos['thumb'];
        }
        if (empty($conditions)) {
            $conditions = null;
        }
        $sets = $this->Prodcustsets->get_by($conditions, false, false, null, null, null, null, 'level', 'ASC');
        $levels = [];
        foreach ($sets as $set) {
            $set->name = $this->Prodcustsets->decode($set->name);
            $set->options = $this->Prodcustsetoptions->get_by(['set_id' => $set->id, 'status' => 'active'], false, false);
            foreach ($set->options as $index => $option) {
                $set->options[$index] = $this->Prodcustsetoptions->build_entity($option, $hub_id);
            }
            if (array_key_exists($set->level, $levels)) {
                array_push($levels[$set->level], $set);
            } else {
                $levels[$set->level] = array($set);
            }
        }
        $data['levels'] = $levels;
        return $this->response($data, REST_Controller::HTTP_OK);
    }
}
