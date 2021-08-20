<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Product extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgconfigs');
            $this->load->model('Stocksummaries');
            $this->load->model('Userhubs');
            $this->load->model('Stockshiftings');
            $this->load->model('Geoareas');
        }
    }
    public function index_get()
    {
        $results['config'] = $this->Cfgconfigs->get_all();
        return $this->response($results);
    }
    public function stocksummary_get($from = null, $to = null, $hub_id = NULL)
    {
        $results['hubs'] = $this->Userhubs->get(['status' => 'active'], NULL, NULL, ['id', 'name', 'area_id', 'house_en', 'house_bn']);
        foreach ($results['hubs'] as $k => $v) {
            $results['hubs'][$k]->name = $this->Userhubs->decode($v->name);
            $results['hubs'][$k]->area = $this->Geoareas->formatted($v->area_id)->formatted;
            if ($hub_id == $v->id) $results['hub'] = $results['hubs'][$k];
        }
        if (empty($from)) $from = !empty(getallheaders()['start']) ? getallheaders()['start'] : strtotime(date('Y-m-d') . ' 00:00:01') * 1000;
        if (empty($to)) $to = !empty(getallheaders()['end']) ? getallheaders()['end'] : strtotime(date('Y-m-d') . ' 23:59:59') * 1000;

        $results['items_in'] = $this->Stocksummaries->get_itemin_rows($from, $to, $hub_id);
        $results['items_out'] = $this->Stocksummaries->get_itemout_rows($from, $to, $hub_id);
        $results['items_low'] = $this->Stocksummaries->get_item_in_low_qty_rows($hub_id);
        $results['items_shifts'] = $this->Stockshiftings->get_rows($from, $to, $hub_id);
        return $this->response($results);
    }
}
