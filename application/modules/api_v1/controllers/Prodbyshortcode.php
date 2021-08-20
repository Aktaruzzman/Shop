<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodbyshortcode extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
            $this->load->model('Prodcodes');
        }
    }
    public function index_get($shortcode = null, $supplier = null)
    {
        if (!empty($shortcode)) {
            $code = $this->Prodcodes->get_by(['shortcode' => $shortcode], true, false, ['item_id', 'option_id']);
            if (!empty($code)) {
                if (!empty($code->option_id)) {
                    $conditions = ['id' => $code->option_id, 'status' => 'active'];
                    if ($supplier) $conditions['supplier_id'] = $supplier;
                    $entity = $this->Prodoptions->get($conditions, true, false);
                    $entity->edit = false;
                    $results['entity'] = $this->Prodoptions->build_entity($entity);
                } else {
                    $conditions = ['id' => $code->item_id, 'status' => 'active'];
                    if ($supplier) $conditions['supplier_id'] = $supplier;
                    $entity = $this->Proditems->get($conditions, true, false);
                    $entity->edit = false;
                    $results['entity'] = $this->Proditems->build_entity($entity);
                }
                $results['status'] = !empty($results['entity']) ? true : false;
                return $this->response($results);
            }
            return $this->response(['status' => false, 'message' => 'invalid request']);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
}
